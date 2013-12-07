<?php
class Wsu_Storeutilities_Block_Adminhtml_Categories extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface {
	protected $_template = 'wsu/storeutilities/choose_categories.phtml';
    public function render(Varien_Data_Form_Element_Abstract $element){
        return $this->toHtml();
    }
}