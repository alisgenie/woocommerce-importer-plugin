<div class="woo-importer-wrapper">
    <h1><?php echo get_admin_page_title() ?></h1>
  <div>
    <p>
  Puoi inserire importare  file csv con le seguenti intestazioni:<br>
   nel caso di prodotti semplici:  "sku,nome,prezzo,prezzo scontato,quantita"<br>
   nel caso di prodotti variabili:  "id,id_padre,sku,nome,prezzo,prezzo scontato,quantita,taglia"
    </p>
  </div>

  <form action='#' method='POST' enctype='multipart/form-data'>
    <label for="myfile">Select a file:</label>

    <input type="file" id="<?php echo WooImportersERVICE::INPUT_NAME; ?>" name="<?php echo WooImportersERVICE::INPUT_NAME ?>" accept='csv'><br><br>
    <button type="submit" value='1' name='upload'>Carica File</button>
    <button type="submit" value='1' name='aggiorna_prodotti'>Aggiorna Prodotti</button>
  </form>

</div>
