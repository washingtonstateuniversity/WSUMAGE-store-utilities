<?php
class Wsu_Storeutilities_Model_Design_Package extends Mage_Core_Model_Design_Package {
    /**
     * Get skin file url
     *
     * @param null $file
     * @param array $params
     * @return string
     */
    public function getSkinUrl($file = null, array $params = array()) {
        if (empty($params['_type'])) {
            $params['_type'] = 'skin';
        }
        $helper = Mage::helper('storeutilities');
        if ($helper->getFileExtension($file) == 'less') {
            $file = $this->getFilename($file, $params);
            if ($file) {
                $file = str_replace(Mage::getBaseDir('media') . DS, '', $file);
                $file = str_replace('\\', '/', $file);
                $file = Mage::getBaseUrl('media', isset($params['_secure']) ? (bool) $params['_secure'] : null) . $file;
            }
        } else {
            $file = parent::getSkinUrl($file, $params);
        }
        return $file;
    }
    /**
     * Compile less file and return css file name
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getFilename($file, array $params) {
        $helper = Mage::helper('storeutilities');
        $file   = parent::getFilename($file, $params);
        if ($helper->getFileExtension($file) == 'less') {
            $file = $helper->compile($file);
        }
        return $file;
    }
}
