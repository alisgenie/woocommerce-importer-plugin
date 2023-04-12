<?php

/*
Plugin Name: Woo Importer
Description: This plugin can import product details from a csv to Woocommerce db
*/

//reference: https://rudrastyh.com/woocommerce/create-product-programmatically.html
//https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-product-functions.html

define( 'WOO_IMPORTER_PLUGIN_PATH', plugin_dir_path(__FILE__));   
define( 'WOO_IMPORTER_PLUGIN_URI', plugin_dir_url(__FILE__));
define( 'WOO_IMPORTER_PLUGIN_FILE', plugin_dir_path(__FILE__) . "woo-importer.php");


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WOO_IMPORTER_PATH', ABSPATH . 'wp-content/uploads/shop/imports/');



class WooImporter
{

    
    

    public function __construct()
    {
        $pluginName = 'WooImporter';
        
        $bootstrapServiceFile = WOO_IMPORTER_PLUGIN_PATH . 'service/BootstrapService.php';
        $ControllerFile = WOO_IMPORTER_PLUGIN_PATH . 'controller/' . $pluginName . 'Controller.php';
        $ServiceFile = WOO_IMPORTER_PLUGIN_PATH . 'service/' . $pluginName . 'Service.php';

        
        require_once($bootstrapServiceFile);
        require_once($ControllerFile);
        require_once($ServiceFile);
        
        
        register_activation_hook( WOO_IMPORTER_PLUGIN_FILE, [new BootstrapService(), 'bootstrap']);
        $this->add_actions();
    }


    private function add_actions(){

        add_action('admin_menu', [$this, 'wooImporterMenu']);
    }

    public function wooImporterMenu(){
        add_menu_page(
            'Woo Importer - Import your products',
            'Woo Importer',
            'manage_options',
            'woo_importer',
            [new WooImporterController(), 'getAdminTemplate'],
            'dashicons-store',
            1
        );

    }
}


new WooImporter();