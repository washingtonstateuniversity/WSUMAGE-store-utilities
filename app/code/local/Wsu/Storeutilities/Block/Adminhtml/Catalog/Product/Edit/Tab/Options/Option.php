<?php

class Wsu_Storeutilities_Block_Adminhtml_Catalog_Product_Edit_Tab_Options_Option extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Option {
    /**
     * Class constructor
     */
    public function __construct(){
        parent::__construct();
        $this->setTemplate('wsu/storeutilities/product/edit/options/option.phtml');
    }
}