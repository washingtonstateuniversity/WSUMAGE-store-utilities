<?php
require_once(Mage::getBaseDir('lib') . DS . 'lessphp' . DS . 'lessc.inc.php'); //fix this!!!!
class Wsu_Storeutilities_Helper_Data extends Mage_Core_Helper_Abstract {
	
	
	//note that we are doing the folder clearing due to the admin html
	//cache not being cleared when you flush the caches.  Need to look into this later
	#rememeber this is user byt sub-exts
	public function cleanConfigCache(){
		// Init without cache so we get a fresh version
		$cache=Mage::getBaseDir('cache');
		
		$files = glob($cache.'/*/*'); // get all file names
		foreach($files as $file){ // iterate files
		  if(is_file($file)){
			unlink($file); // delete file
		  }
		}
		Mage::getSingleton('core/session')->addSuccess('Saved config cache.');
	}	
	
	
    public function getConfig($path, $default = null) {
        $value = Mage::getStoreConfig($path);
        if ( empty($value) || !isset($value) || trim($value)=='' ) {
            return is_null($default)?'':$default;
        } else {
            return $value;
        }
    }
	/* get Cats that are from your store scope
		@todo redo this //maybe it should let you pick the thing to get? ie: array('name','level')
	*/
	public function get_mage_categories() {
		$category = Mage::getModel('catalog/category'); 
		$tree = $category->getTreeModel(); 
		$tree->load(); 
		$ids = $tree->getCollection()->getAllIds(); 
		$categories = array();
		//$storeId = //get by user
		if ($ids){ 
			foreach ($ids as $id){ 
				$cat = Mage::getModel('catalog/category'); 
				//$cat->setStoreId($storeId);
				$cat->load($id);
				$entity_id = $cat->getId(); 
				$name = $cat->getName(); 
				$level = $cat->getLevel();
				$categories[]=array("entity_id"=>$entity_id, 
							  "name"=>$name, 
							  "level"=>$level); 
			} 
		}
		return $categories;
	}
	
	
	
	public function hasExt($extname){
		return Mage::helper('core')->isModuleEnabled($extname)&&Mage::helper('core')->isModuleOutputEnabled($extname);
	}
	
	
	
	
	
    /**
     * Get or initialize an array of the default attributes for every product.
     * 
     * @return array The Require attribute codes for the produt's new attribute set.
     */
    public function _getRequiredAttributes($attrSetId) {
        $_requiredAttributes = array();
        $attributes          = Mage::getModel('catalog/product_attribute_api')->items($attrSetId);
        foreach ($attributes as $_attribute) {
            $_requiredAttributes[] = $_attribute['code'];
        }
		//these must be there so lets just make sure
		$_requiredAttributes[] = 'has_options';
		$_requiredAttributes[] = 'required_options';
		$_requiredAttributes[] = 'sku';
        return $_requiredAttributes;
    }
	protected $_allAttributes = array();
    public function _getAllAttributes() {
        if (empty($this->_allAttributes)) {
            $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
            foreach ($attributes as $_attribute) {
                $attr['code']           = $_attribute['attribute_code'];
                $attribute              = Mage::getModel('eav/entity_attribute')->load($_attribute['attribute_id']);
                $attr['table']          = $attribute->getBackendTable();
                $attr['id']             = $_attribute['attribute_id'];
                $this->_allAttributes[] = $attr;
            }
        }
        return $this->_allAttributes;
    }
	
	
	
	
	
    /**
     * Get file extension in lower case
     *
     * @param $file
     * @return string
     */
    public function getFileExtension($file) {
        return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }
    /**
     * Compile less file and return full path to created css
     *
     * @param $file
     * @return string
     */
    public function compile($file) {
        if (!$file) return '';
        try {
            $targetFilename = Mage::getBaseDir('media') . DS . 'lesscss' . DS . md5($file) . '.css';
            $cacheKey       = 'less_' . $file;
            $cacheModel     = $cache = Mage::getSingleton('core/cache');
            $cache          = $cacheModel->load($cacheKey);
            if ($cache) {
                $cache = @unserialize($cache);
            }
            if (!file_exists($targetFilename)) {
                $cache = false;
            }
            $lastUpdated = (isset($cache['updated'])) ? $cache['updated'] : 0;
            $cache       = lessc::cexecute(($cache) ? $cache : $file);
            if ($cache['updated'] > $lastUpdated) {
                if (!file_exists(dirname($targetFilename))) {
                    mkdir(dirname($targetFilename), 0777, true);
                }
                file_put_contents($targetFilename, $cache['compiled']);
                $cacheModel->save(serialize($cache), $cacheKey);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $targetFilename = '';
        }
        return $targetFilename;
    }
}
	 