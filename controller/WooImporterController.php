<?php

class WooImporterController{

    private const PLUGIN_VIEW_PATH = WOO_IMPORTER_PLUGIN_PATH . "view/";
    


    public function __construct()
    {
             
    }


    public function getAdminTemplate(){

        require_once(self::PLUGIN_VIEW_PATH . "admin-template.php");

    }
}