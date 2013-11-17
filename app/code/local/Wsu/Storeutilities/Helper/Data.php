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
	
	
    public function getConfig($field, $default = null) {
        $value = Mage::getStoreConfig('localeselector/option/' . $field);
        if (!isset($value) or trim($value) == '') {
            return $default;
        } else {
            return $value;
        }
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
	 