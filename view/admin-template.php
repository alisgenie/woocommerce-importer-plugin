<div class="woo-importer-wrapper">
    <h1><?php echo get_admin_page_title() ?></h1>

    <pre>
    <?php

    $intestazioneCsv = "cod,nome,prezzo,prezzo scontato,quantita";
    $intestazioneCsv = explode(",", $intestazioneCsv);

    $rowWidth = sizeof($intestazioneCsv);

    $arRet = [];
    // echo '<pre>';

    $product = wc_get_product(wc_get_product_id_by_sku('maglia_uno'));
    // print_r($intestazioneCsv);
    // var_dump($product);
    new WooImporterService();

    //var_dump($product->get_sku('edit'));
    $a = '3.5';
    $correct = is_numeric($a) && is_int($a);
    //$correct = is_int($a);
    function createProdVar()
    {
        // Creating a variable product
        $product = new WC_Product_Variable();

        // Name and image would be enough
        $product->set_name('Wizard Hat');
        //$product->set_image_id(90);

        // one available for variation attribute
        $attribute = new WC_Product_Attribute();
        $attribute->set_name('Magical');
        $attribute->set_options(array('Yes', 'No'));
        $attribute->set_position(0);
        $attribute->set_visible(true);
        $attribute->set_variation(true); // here it is

        $product->set_attributes(array($attribute));

        // save the changes and go on
        $product->save();

        // now we need two variations for Magical and Non-magical Wizard hat
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($product->get_id());
        $variation->set_attributes(array('magical' => 'Yes'));
        $variation->set_regular_price(1000000); // yep, magic hat is quite expensive
        $variation->save();

        $variation = new WC_Product_Variation();
        $variation->set_parent_id($product->get_id());
        $variation->set_attributes(array('magical' => 'No'));
        $variation->set_regular_price(500);
        $variation->save();
    }




    ?>
  <form action='#' method='POST' enctype='multipart/form-data'>
    <label for="myfile">Select a file:</label>

    <input type="file" id="<?php echo WooImportersERVICE::INPUT_NAME; ?>" name="<?php echo WooImportersERVICE::INPUT_NAME ?>" accept='csv'><br><br>
    <button type="submit" value='1' name='upload'>Carica File</button>
    <button type="submit" value='1' name='aggiorna_prodotti'>Aggiorna Prodotti</button>
  </form>

</div>