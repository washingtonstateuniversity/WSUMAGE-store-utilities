<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Page
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
Maybe this needs to be extending/includes from the Wsu_Storeutilities_Block_Html_Head
 */
class  Wsu_Storeutilities_Block_Adminhtml_Page_Head extends Mage_Adminhtml_Block_Page_Head
{
    /**
     * Initialize template
     *
     */
    protected function _construct()
    {
        $this->setTemplate('page/html/head.phtml');
    }
	
    /**
     * Add HEAD Item
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return Mage_Page_Block_Html_Head
     */
    public function addItem($type, $name, $params=null, $if=null, $cond=null,$window_obj="",$local_path="")
    {
        if (($type==='skin_css'||$type==='cdn_css') && empty($params)) {
            $params = 'media="all"';
        }
        $this->_data['items'][$type.'/'.$name] = array(
            'type'   => $type,
            'name'   => $name,
            'params' => $params,
            'if'     => $if,
            'cond'   => $cond,
			'window_obj'   => $window_obj,
			'local_path'   => $local_path,
       );
        return $this;
    }
	
	
    /**
     * Add JavaScript file to HEAD entity
     *
     * @param string $name
     * @param string $params
     * @return Mage_Page_Block_Html_Head
     */
    public function addCdnJs($name, $params=null, $if=null, $cond=null,$window_obj="",$local_path=""){
        $this->addItem('cdn_js', $name, $params, $if=null, $cond=null,$window_obj="",$local_path="");
        return $this;
    }

    /**
     * Add CSS file to HEAD entity
     *
     * @param string $name
     * @param string $params
     * @return Mage_Page_Block_Html_Head
     */
    public function addCdn_css($name, $params=null, $if=null, $cond=null,$window_obj="",$local_path=""){
        $this->addItem('cdn_css', $name, $params, $if=null, $cond=null,$window_obj="",$local_path="");
        return $this;
    }




    /**
     * Add HEAD External Item
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @return Mage_Page_Block_Html_Head
     */
    public function addExternalItem($type, $name, $params=null, $if=null, $cond=null){
    	parent::addItem($type, $name, $params, $if, $cond);
    }

    /**
     * Remove External Item from HEAD entity
     *
     * @param string $type
     * @param string $name
     * @return Mage_Page_Block_Html_Head
     */
    public function removeExternalItem($type, $name)
    {
    	parent::removeItem($type, $name);
    }
    
	
	
   /**
     * Get HEAD HTML with CSS/JS/RSS definitions
     * (actually it also renders other elements, TODO: fix it up or rename this method)
     *
     * @return string
     */
    public function getCssJsHtml()
    {
        // separate items by types
        $lines  = array();
        foreach ($this->_data['items'] as $item) {
            /*if (!is_null($item['cond']) && !$this->getData($item['cond']) || !isset($item['name'])) {
                continue;
            }*/
            $if     = !empty($item['if']) ? $item['if'] : '';
            $params = !empty($item['params']) ? $item['params'] : '';
			$window_obj = !empty($item['window_obj']) ? $item['window_obj'] : '';
			$local_path = !empty($item['local_path']) ? $item['local_path'] : '';
			
            switch ($item['type']) {
                case 'js':        // js/*.js
                case 'skin_js':   // skin/*/*.js
                case 'js_css':    // js/*.css
                case 'skin_css':  // skin/*/*.css
                    $lines[$if][$item['type']][$params][$item['name']] = $item['name'];
                    break;
                default:
                    $this->_separateOtherHtmlHeadElements($lines, $if, $item['type'], $params, $item['name'], $item, $window_obj, $local_path);
                    break;
            }
        }

        // prepare HTML
        $shouldMergeJs = Mage::getStoreConfigFlag('dev/js/merge_files');
        $shouldMergeCss = Mage::getStoreConfigFlag('dev/css/merge_css_files');
        $html   = '';
        foreach ($lines as $if => $items) {
            if (empty($items)) {
                continue;
            }
            if (!empty($if)) {
                $html .= '<!--[if '.$if.']>'."\n";
            }
			// cdn CSS first
            if (!empty($items['cdn_css'])) {
                $html .= $this->_prepareOtherHtmlHeadElements($items['cdn_css']) . "\n";
            }
            // static and skin css
            $html .= $this->_prepareStaticAndSkinElements('<link rel="stylesheet" type="text/css" href="%s"%s />' . "\n",
                empty($items['js_css']) ? array() : $items['js_css'],
                empty($items['skin_css']) ? array() : $items['skin_css'],
                $shouldMergeCss ? array(Mage::getDesign(), 'getMergedCssUrl') : null
            );
            // cdn JS first
            if (!empty($items['cdn_js'])) {
                $html .= $this->_prepareOtherHtmlHeadElements($items['cdn_js']) . "\n";
            }

            // static and skin javascripts
            $html .= $this->_prepareStaticAndSkinElements('<script type="text/javascript" src="%s"%s></script>' . "\n",
                empty($items['js']) ? array() : $items['js'],
                empty($items['skin_js']) ? array() : $items['skin_js'],
                $shouldMergeJs ? array(Mage::getDesign(), 'getMergedJsUrl') : null
            );

            // other stuff
            if (!empty($items['other'])) {
                $html .= $this->_prepareOtherHtmlHeadElements($items['other']) . "\n";
            }

            if (!empty($if)) {
                $html .= '<![endif]-->'."\n";
            }
        }
        return $html;
    }


	
	
	
	
	
	
	
	
	
	
	
	
	
    /**
     * Classify HTML head item and queue it into "lines" array
     *
     * @see self::getCssJsHtml()
     * @param array &$lines
     * @param string $itemIf
     * @param string $itemType
     * @param string $itemParams
     * @param string $itemName
     * @param array $itemThe
     */
    protected function _separateOtherHtmlHeadElements(&$lines, $itemIf, $itemType, $itemParams, $itemName, $itemThe, $window_obj= "", $local_path= "")
    {
        $params = $itemParams ? ' ' . $itemParams : '';
        $href   = $itemName;
        switch ($itemType) {
            case 'rss':
                $lines[$itemIf]['other'][] = sprintf('<link href="%s"%s rel="alternate" type="application/rss+xml" />',
                    $href, $params
                );
                break;
            case 'link_rel':
                $lines[$itemIf]['other'][] = sprintf('<link%s href="%s" />', $params, $href);
                break;
           	case 'external_js':
                $lines[$itemIf]['other'][] = sprintf('<script type="text/javascript" src="%s" %s></script>', $href, $params);
                break;               
          	case 'external_css':
                $lines[$itemIf]['other'][] = sprintf('<link rel="stylesheet" type="text/css" href="%s" %s/>', $href, $params);
                break;
          	case 'cdn_css':
                $lines[$itemIf]['other'][] = sprintf('<link rel="stylesheet" type="text/css" href="%s" %s/>', $href, $params);
                break;
            case 'cdn_js':
				$local = "";

				if($local_path !="" && $local_path != "")
				$local = "\n<script type=\"text/javascript\">if (!window.".$window_obj.") {document.write(unescape(\"%3Cscript src='".$local_path."' type='text/javascript'%3E%3C/script%3E\"));}</script>";

                $lines[$itemIf]['cdn_js'][] = sprintf("<script type=\"text/javascript\" src=\"%s\"%s></script>%s", $href, $params,$local);
                break;
        }
    }

}
