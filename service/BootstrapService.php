<?php


class BootstrapService
{

    public function bootstrap()
    {
        $this->setImporter();
    }

    private  function setImporter()
    {
        $dir = WOO_IMPORTER_PATH;

        function importerLog($riga = '', $dir='./')
        {
            if (!empty($riga)) {
                $riga = " [ " . date("Y-m-d H:i:s") . " ] Attività eseguita: $riga";
                file_put_contents($dir . "importer-log.txt", $riga, FILE_APPEND);
            }
        }

        if (!file_exists($dir)) {
            //recursive
            mkdir($dir, 0777,true);
            importerLog('la directory ' . $dir . ' è stata creata.', $dir);
        }
    }


}
