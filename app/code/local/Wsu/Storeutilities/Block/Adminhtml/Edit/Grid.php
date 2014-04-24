<?php
class Wsu_Storeutilities_Block_Adminhtml_Edit_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('wsu_storeutilities');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }
    protected function _prepareCollection() {
        $collection = Mage::getModel('core/website')->getCollection();
        $this->setCollection($collection);
		
		if(Mage::helper('core')->isModuleEnabled('Wsu_Storepartitions')){
			$role = Mage::getSingleton('storepartitions/role');
			if ($role->isPermissionsEnabled()) {
				$collection->addIdFilter($role->getAllowedWebsiteIds());
			}
		}

        parent::_prepareCollection();
        return $this;
    }
    protected function _prepareColumns() {
        $this->addColumn('website_title', array(
            'header' => Mage::helper('core')->__('Website Name'),
            'align' => 'left',
            'index' => 'name',
            //            'filter_index'  => 'main_table.name',
            //            'renderer'      => 'wsu_storeutilities/adminhtml_system_store_grid_render_website'
            'renderer' => 'wsu_storeutilities/adminhtml_edit_renderer_website'
        ));
        return parent::_prepareColumns();
    }
}
