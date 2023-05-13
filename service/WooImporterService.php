<?php

class WooImporterService
{

    public const FILE_NAME = 'woo-imports.csv';
    public const INPUT_NAME = 'woo-importer-file';

    public function __construct()
    {
        date_default_timezone_set('Europe/Rome');
        $this->import();
    }

    private function import()
    {

        if (isset($_POST['upload']) && $_POST['upload']) {
            $this->upload();
        }
        if (isset($_POST['aggiorna_prodotti']) && $_POST['aggiorna_prodotti']) {

            $updateFlag = false;
            $updateFlag = $this->updateProducts(WOO_IMPORTER_PATH . self::FILE_NAME);

            if ($updateFlag) {

                self::importerLog("file " . self::FILE_NAME . " importato con successo");
                $copyFileName = date("Y-m-d--H-i-s--") . self::FILE_NAME;

                if (!copy(WOO_IMPORTER_PATH . self::FILE_NAME, WOO_IMPORTER_PATH . $copyFileName)) {
                    self::importerLog("problemi a copiare " . self::FILE_NAME . " come '$copyFileName'", 1);
                } else {

                    self::importerLog("File di backup creato per " . self::FILE_NAME . " come '$copyFileName'");
                }
            }
        }
    }

    //info: funzione non scalabile consider&&o la funzione che deve chiamare importerLog()
    /**
     * Useful when a file is uploded through html form of <input type="file"...
     * Save the uploaded file from temporary directory of the server system, to a file in a directory of your choice
     */
    private function upload()
    {



        if (isset($_FILES[self::INPUT_NAME]) && !empty($_FILES[self::INPUT_NAME]["name"])) {

            $uguali = false;

            if (file_exists(WOO_IMPORTER_PATH . self::FILE_NAME)) {
                $uguali = filesize(WOO_IMPORTER_PATH . self::FILE_NAME) == filesize($_FILES[self::INPUT_NAME]['tmp_name'])
                    &&
                    md5_file(WOO_IMPORTER_PATH . self::FILE_NAME) == md5_file($_FILES[self::INPUT_NAME]['tmp_name']);
            }
            if ($uguali) {
                self::importerLog('file caricato ignorato, uguale al precedente.');
                return false;
            } else {
                $salvato = false;
                $salvato = move_uploaded_file($_FILES[self::INPUT_NAME]['tmp_name'], WOO_IMPORTER_PATH . self::FILE_NAME);
                if ($salvato) {

                    self::importerLog(sprintf("un nuovo file chiamato '%s' è stato caricato come '%s", $_FILES[self::INPUT_NAME]["name"], self::FILE_NAME));
                } else {
                    self::importerLog('nessun file caricato, problemi a spostare il file temporaneo', 1);
                }
            }
        } else {
            self::importerLog('nessun file caricato, non hai selezionato un file');
        }
    }
    private function updateProducts() : bool
    {

        $intestazioneCsvSemplici = explode(",", "sku,nome,prezzo,prezzo scontato,quantita");
        $file = WOO_IMPORTER_PATH . self::FILE_NAME;
        $csv = [];
        $csv = file($file);


        $fileColumns = str_getcsv(array_shift($csv));


        if (

            sizeof($fileColumns) === sizeof($intestazioneCsvSemplici) && sizeof(array_intersect($fileColumns, $intestazioneCsvSemplici))
        ) {
           return $this->updateSimpleProducts($csv, sizeof($intestazioneCsvSemplici));
        } else{
            return false;
        }
    }

    private function updateVariableProducts($csv)
    {
 //funzione in corso di implementazione nel branch variableProducts
    }
    
    private function updateSimpleProducts($csv, $width)
    {

        $rowWidth = $width;

        self::importerLog("inizio aggiornamento prodotti");
        //stores the Ids of products actually present on the csv
        $actualProducts = [];

        foreach ($csv as $row) {


            $row = str_getcsv($row);

            //info: controllo che le righe del csv contengano il numero di campi giusto
            if ($rowWidth === sizeof($row)) {

                $product = false;
                $product = wc_get_product(wc_get_product_id_by_sku($row[0]));

                // for($i = 0; $i<sizeof($row); $i++){
                //     $row[$i] = trim($row[$i]);
                // }

                if (empty($product)) {
                    $product = new WC_Product_Simple;
                    $product->set_sku($row[0]);
                    $product->set_name($row[1]);
                    $product->set_regular_price($row[2]);
                    $product->set_sale_price($row[3]);
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity($row[4]);
                    $product->save();
                    $riga = implode(",", $row);
                    self::importerLog("la riga '$riga' è stata inserita come nuovo prodotto");
                } else {

                    $name = $product->get_name();

                    if ($name != $row[1]) {

                        $product->set_name($row[1]);
                        self::importerLog("nel prodotto con sku  '$row[0]' è stato aggiornato '$name' con '$row[1]' ");
                    }
                    $regular_price = $product->get_regular_price();
                    $correct = is_numeric($row[2]) && $row[2] > 0;

                    if ($regular_price != $row[2] && $correct) {

                        $product->set_regular_price($row[2]);
                        self::importerLog("nel prodotto con sku  '$row[0]' è stato aggiornato '$regular_price' con '$row[2]' ");
                    } elseif (!$correct) {
                        self::importerLog("$row[2] non è un prezzo corretto");
                    }

                    $sale_price = $product->get_sale_price();
                    $correct = is_numeric($row[3]) && $row[3] > 0 && $row < 0;

                    if ($sale_price != $row[3] && $correct) {

                        $product->set_sale_price($row[3]);
                        self::importerLog("nel prodotto con sku  '$row[0]' è stato aggiornato '$sale_price' con '$row[3]' ");
                    } elseif (!$correct) {
                        self::importerLog("$row[3] non è un prezzo corretto");
                    }

                    $stock_quantity = $product->get_stock_quantity();
                    $correct = is_numeric($row[4]) && $row[4] >= 0 && ($row[4] - intval($row[4])) == 0;
                    if ($stock_quantity != $row[4] && $correct) {

                        $product->set_stock_quantity($row[4]);
                        self::importerLog("nel prodotto con sku  '$row[0]' è stato aggiornato '$stock_quantity' con '$row[4]' ");
                    } elseif (!$correct) {
                        self::importerLog("'$row[4]' non è un formato di quantità corretto");
                    }

                    if (!$correct) {
                        $product->save();
                    }
                }
                array_push($actualProducts, $product->get_id());
            } else {
                self::importerLog('file importato ignorato, le righe del file csv non sono nel formato corretto');
                return false;
            }
        }



        $productsToDelete = wc_get_products(array(
            'type' => 'simple',
            'exclude' => $actualProducts,
        ));



        foreach ($productsToDelete as $id) {
            $product = wc_get_product($id);
            $eliminato = $product->get_sku();
            $product->delete();
            self::importerLog("è stato spostato nel cestino il prodotto con sku '$eliminato'");
        }
        return true;
    }






    /** 
     *    Funzione per salvare righe precedute da data ed ora nel file ABSPATH/wp-content/uploads/ali/importerLog.txt. 
     *    E' possibile salavare la riga in modalità debug stamp&&o la riga e il file dove è stata chiamata la funzione.
     * 
     * @param string $riga stringa di log da salvare
     * @param int $debug Opzionale: se Assente salva in modalità normale, se uguale a 0 non salva nulla, se uguale ad 1 salva in modalità debug.
     */
    public static function importerLog($riga = '', $debug = -1)
    {
        if (!$debug == 0) {
            if (!empty($riga)) {

                $dir = WOO_IMPORTER_PATH;

                //date_default_timezone_set('Europe/Rome');

                if ($debug > 0) {
                    $d = debug_backtrace();
                    $d = $d[0];

                    $riga = "\n [ " . date("Y-m-d H:i:s") . " ] Info di debug " . $d['file'] . " alla linea " . $d['line'] . ": $riga";
                    file_put_contents($dir . "importer-log.txt", $riga, FILE_APPEND);
                } else {
                    $riga = "\n [ " . date("Y-m-d H:i:s") . " ]  $riga";
                    file_put_contents($dir . "importer-log.txt", $riga, FILE_APPEND);
                }
            }
        }
    }
}
