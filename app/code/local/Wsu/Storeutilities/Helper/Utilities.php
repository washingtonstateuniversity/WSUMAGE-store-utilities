<?php

class Wsu_Storeutilities_Helper_Utilities extends Mage_Core_Helper_Abstract {

	/**
	 * generate a md5 hash of a random number
	 *
	 * @deprecated 0.1.0
	 * @return string
	 */
	public function getUniqueCode($length = ""){
		$code = md5(uniqid(rand(), true));
		if ($length != ""){
			return substr($code, 0, $length);
		}else{
			return $code;
		}
	}
	/**
	 * take file and process into an array
	 *
	 * @return array
	 */
	public function csv_to_array( $filename='', $delimiter=',', $enclosure = '"', $escape = "\\" ){
		 if(!file_exists($filename) || !is_readable($filename)){
			 return FALSE;
		 }
		 $header = NULL;
		 $data = array();
		 if (($handle = fopen($filename, 'r')) !== FALSE){
			 while (($row = fgetcsv($handle,1000, $delimiter,$enclosure,$escape)) !== FALSE){
				 if(!$header){
					 $header = $row;
				 }else{
					 $data[] = array_combine($header, $row);
				 }
			 }
			 fclose($handle);
		 }
		 return $data;
	}
	
	/**
	 * sudo match  of jQuery's $.extend()
	 *
	 * @return array
	 */
	public function extend($old,$new){
		//add error check $old,$new
		$args = func_get_args();
		$data=array();
		$data = array_merge($data,$old);
		foreach($new as $key => $newValue) {
			$data[$key] = $newValue;
		}
		if(count($args)>2 && isset($args[2]) && !empty($args[2]) && $args[2]!=null){
			//keep applying the next array in order
			$allArg = array_shift(array_shift($args));//drop old and new
			$newArg = $allArg[0];
			$allArg = (count($allArg)>1)?array_shift($allArg):array();
			$data=call_user_func_array('extend', array_merge(array('old'=>$data,'new'=>$newArg),$allArg));
		}
		return $data;
	}
	
	//this should maybe be not based on the cats but the
	//store it's on, ie pull from Mage::getModel('catalog/product')?
	/**
	 * move all products of into a category base on args
	 *
	 * @since 0.1.0
	 *
	 *
	 * @param int $website
	 * @param int $store
	 * @param string $rootcat
	 * @param object $children Optional
	 */
	public function moveStoreProducts($website,$store,$rootcat,$children=null){
		if($children==null){
			$children = Mage::getModel('catalog/category')->getCategories($rootcat);
		}
		foreach ($children as $category) {
			echo $category->getName();
			$cat_id=$category->getId();
			$category = Mage::getModel('catalog/category')->load($cat_id);
			$collection = $category->getProductCollection();
			foreach ($collection as $product){
				$oldproductId = $product->getId();
				$_product=$product->load($productId);
				$sku = $_product->getSku();
				print("getting ready to move ".$sku);
				try{
					$_product->setWebsiteIds(array($website)); //assigning website ID
					$_product->setStoreId($store);
					$_product->save();
					print("moving ".$sku." to web::".$website." store::".$store);//to change to logs later
				}catch (Exception $e) {
				   Mage::log('failed on sku:: ',$sku,"\n",$e->getMessage(),"\n", Zend_Log::ERR);
				}
			}
			$childrenCats = Mage::getModel('catalog/category')->getCategories($cat_id);
			if( count($childrenCats)>0){ $this->moveStoreProducts($website,$site,$cat_id,$childrenCats); }
		}
	}
	
	/**
	 * add a category by name with attr if provided
	 *
	 * @since 0.1.0
	 *
	 *
	 * @param string $categoryName
	 * @param array $attr Optional. Description.
	 */
	public function make_category($categoryName,$attr=array()){
		$category = Mage::getModel('catalog/category');
		$category->setStoreId(0); // No store is assigned to this category
		
		//set up so defaults
		$defaults = array(
			'name'=>$categoryName,
			'path'=>"1", // this is the catgeory path - 1 for root category. 
			'description'=>"Category Description",
			'meta_title'=>"",
			'meta_keywords'=>"",
			'meta_description'=>"",
			'display_mode'=>"PRODUCTS",
			'is_active'=>1,
			'is_anchor'=>1
		);
		$rcat = array_merge($defaults,$attr);
		
		$category->addData($rcat);
		$rcatId=0;
		try {
			$category->save();
			$rcatId = $category->getId();
		}
			catch (Exception $e){
			echo $e->getMessage();
		}
		return $rcatId;
	}
	
	/**
	 * add a website by name with attr if provided
	 *
	 * @since 0.1.0
	 *
	 *
	 * @param string $site
	 * @return Boolean|int false on fail, id on pass
	 */
	public function make_website($site){
		$website = Mage::getModel('core/website');
		$website->load($site['code']);
		if( empty($website) || !($website->getId()>0) ){
			$website->setCode($site['code'])
				->setName($site['name'])
				->save()
				->load($site['code']);
		}
		if (empty($website)) {
			Mage::log("Tried to create website '{$site['code']}' but failed: ", Zend_Log::ERR);
			return false;
		}
		$webid = $website->getId();
		if($webid>0){
			Mage::app()->getConfig()->reinit();
			return $webid;
		}else{
			Mage::log("Tried to create website '{$site['code']}' but failed (thought it was there): ", Zend_Log::ERR);
			return false;
		}
		
	}
	
	/**
	 * make_storeGroup
	 */
	public function make_storeGroup($store,$url,$websiteId,$rootCategory){
		$storeGroup = Mage::getModel('core/store_group');
		$storeGroupName = $store['name'];
		
		$website = Mage::app()->getWebsite($websiteId);
		$groups = $website->getGroups();

		foreach($groups as $group){
			$gname = $group->getName();
			if($gname = $storeGroupName){
				$storeGroup->load($group->getGroupId());
				break;
			}
		}
		
		if(empty($storeGroup) || !($storeGroup->getId()>0) ){
			$storeGroup->setData(
				array(
					'root_category_id' => $rootCategory,
					'website_id' => $websiteId,
					'name' => $storeGroupName,
				)
			);		
			$storeGroup->save();
			$storeGroup->load($storeGroup->getId());
			$cDat = new Mage_Core_Model_Config();
			$cDat->saveConfig('web/unsecure/base_url', "http://".$url.'/', 'websites', $websiteId);
			$cDat->saveConfig('web/secure/base_url', "https://".$url.'/', 'websites', $websiteId);
		}

		if(!empty($storeGroup) && ($storeGroup->getId()>0) ){
			Mage::app()->getConfig()->reinit();
			return $storeGroup->getId();
		}else{
			Mage::log("Tried to create Store Group '{$storeGroupCode}' but failed: ", Zend_Log::ERR);
			return false;
		}
	}
	
	/**
	 * reparentCategory
	 */
	public function reparentCategory($rootCategory,$movingcat){
		$rcatId=$rootCategory;
		if($rcatId>0){
			if($movingcat>0){
				$category = Mage::getModel( 'catalog/category' )->load($movingcat);
				$targetcategory = Mage::getModel( 'catalog/category' )->load($rcatId);
				$_category = Mage::registry('current_category');
				if(!empty($category) && !empty($targetcategory)){
					Mage::unregister('category');
					Mage::unregister('current_category');
					Mage::register('category', $category);
					Mage::register('current_category', $category);
					$_category = Mage::registry('current_category');
					if(!empty($_category) && $_category->getId()>0){
						$category->move($rcatId);
					}
				}
			}
		}
	}

	/**
	 * make_store
	 * 
	 * @todo this needs to be abstracted more
	 */
	public function make_store($webSiteId,$storeGroupId,$view){
		$storecode=$view['code'];
		$store = Mage::getModel('core/store');
		
		$group = Mage::getModel('core/store_group')->load($storeGroupId);
        $stores = $group->getStoreCollection();
		foreach($stores as $store){
			$sname = $store->getCode();
			if($sname = $storecode){
				$store->load($store->getStoreId());
				break;
			}
		}
		if( empty($store) || !($store->getId()>0) ){
			$store->setData(
					array(
						'website_id' => $webSiteId,
						'name' => $view['name'],
						'code' => $storecode,
						'group_id' => $storeGroupId,
						'is_active' => 1,
					)
			);
			$store->save();
			$store->load($store->getId());
		}
		$storeid = $store->getId();	
		if($storeid>0){
			Mage::app()->getConfig()->reinit();
			return $storeid;
		}else{
			Mage::log("Tried to create store view '{$sotercode}' but failed: ", Zend_Log::ERR);
			return false;
		}
	}
	
	/**
	 * createCmsPage
	 */
	public function createCmsPage($storeids,$params=array()){
		$default = array(
			'title' => 'Store title',
			'root_template' => 'one_column',
			'meta_keywords' => 'meta,keywords',
			'meta_description' => 'meta description',
			'identifier' => 'home',
			'content_heading' => '',
			'is_active' => 1,
			'stores' => is_array($storeids)?$storeids:array($storeids),//available for all store views
			'content' => '<p>Welcome to this store\'s page.</p>'
		);
		$cmsPageData = $this->extend($default,$params);
		Mage::getModel('cms/page')->setData($cmsPageData)->save();
		return true;
	}
	
	/**
	 * createCat
	 */
	public function createCat($storeCodeId,$rootcatID,$cats=array()){
		foreach($cats as $url=>$catInfo){
			$category = Mage::getModel('catalog/category');
			$category->setStoreId($storeCodeId);
				//this should be more pliable
				$cat['name'] =$catInfo['name'];
				$cat['path'] = "1/".$rootcatID;
				$cat['description'] = $catInfo['description'];
				$cat['is_active'] = $catInfo['is_active'];
				$cat['is_anchor'] = $catInfo['is_anchor'];
				$cat['page_layout'] = $catInfo['is_anchor'];
				$cat['url_key'] = $url;
				$cat['image'] = $catInfo['image'];
			$category->addData($cat);
			$category->save();
			$catsId=$category->getId();
			if(isset($catInfo['children'])&& !empty($catInfo['children'])){
				$this->createCat($storeCodeId,$rootcatID.'/'.$catsId,$catInfo['children']);
			}
		}
	}	
	
	/**
	 * initFromSkeleton
	 */
    public function initFromSkeleton($skeletonId,$set,$stopGroup=null,$stopAttr=null) {
        $groups = Mage::getModel('eav/entity_attribute_group')
            ->getResourceCollection()
            ->setAttributeSetFilter($skeletonId)
            ->load();
    
        $newGroups = $this->filterGroups($set,$groups,$stopGroup,$stopAttr);
        return $newGroups;
    }
	
	/**
	 * filterGroups
	 */
    public function filterGroups($set,$groups,$stopGroup=null,$stopAttr=null){
        $newGroups = array();
        foreach ($groups as $group) {
            if(!in_array($group->getAttributeGroupName(),$stopGroup)){
				
                $newGroup = clone $group;
                $newGroup->setId(null)
                    ->setAttributeSetId($set->getId())
                    ->setDefaultId($group->getDefaultId());
            
                $groupAttributesCollection = Mage::getModel('eav/entity_attribute')
                    ->getResourceCollection()
                    ->setAttributeGroupFilter($group->getId())
                    ->load();
            
                $newAttributes = array();
                foreach ($groupAttributesCollection as $attribute) {
                    if(!in_array($attribute->getName(),$stopAttr)){
                        $newAttribute = Mage::getModel('eav/entity_attribute')
                            ->setId($attribute->getId())
                            ->setAttributeSetId($set->getId())
                            ->setEntityTypeId($set->getEntityTypeId())
                            ->setSortOrder($attribute->getSortOrder());
                        $newAttributes[] = $newAttribute;
                    }
                }
                $newGroup->setAttributes($newAttributes);
                $newGroups[] = $newGroup;
            }
        }
		//var_dump($newGroups);
        return $newGroups; 
    }
		
	/**
	 * checkForWebsite
	 */
	public function checkForWebsite($code=NULL){
		if($code!=NULL){
			$website = Mage::getConfig()->getNode('websites/'.$code);
			if (!empty($website)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * getInstaller
	 */
	public function getInstaller() {
		return new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
	}

	/**
	 * getEntityTypeId
	 */
	public function getEntityTypeId() {
		return $entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
	}	

	/**
	 * getAttributeSetId
	 */
	public function getAttributeSetId($attribute_set_name) {
		$entityTypeId = $this->getEntityTypeId();
		$installer = $this->getInstaller();
		$attributeSetObject = new Varien_Object($installer->getAttributeSet($entityTypeId , $attribute_set_name));
		$attributeSetId = $attributeSetObject->getAttributeSetId();
		return $attributeSetId;
	}
	
	/**
	 * getAttributeGroupId
	 */
	public function getAttributeGroupId($attribute_set_name, $attribute_group_name) {
		$attribute_set_id = $this->getAttributeSetId($attribute_set_name);
		$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
		return $setup->getAttributeGroupId('catalog_product', $attribute_set_id, $attribute_group_name);
		
		
		$entityTypeId = $this->getEntityTypeId();
		$attributeSetId = $this->getAttributeSetId($attribute_set_name);
		$installer = $this->getInstaller();//new Mage_Eav_Model_Entity_Setup('core_setup');
		$attributeGroupObject = new Varien_Object($installer->getAttributeGroup($entityTypeId ,$attributeSetId,$attribute_group_name));
		return $attributeGroupId = $attributeGroupObject->getAttributeGroupId();
	}
	
	/**
	 * createAttributeGroup
	 */
	public function createAttributeGroup($attribute_group_name , $attribute_set_name) {
		$attributeGroupId = $this->getAttributeGroupId($attribute_group_name,$attribute_set_name);
		if(isset($attributeGroupId) && !empty($attributeGroupId)) {
			return $attributeGroupId;
		}
		$entityTypeId = $this->getEntityTypeId();
		$installer = $this->getInstaller();//new Mage_Eav_Model_Entity_Setup('core_setup');
		$attributeSetId = getAttributeSetId($attribute_set_name);
		$installer->addAttributeGroup($entityTypeId , $attributeSetId , $attribute_group_name);
		return $attributeGroupId = $this->getAttributeGroupId($attribute_set_name,$attribute_group_name);
	}
	
	/**
	 * deleteAttributeSet
	 */
	public function deleteAttributeSet($attribute_set_name) {
		$attributeSetId = $this->getAttributeSetId($attribute_set_name);
		if($attributeSetId) {
			Mage::getModel('eav/entity_attribute_set')->setId($attributeSetId)->delete();
			return array('Name'=>$attribute_set_name,'Id'=>$attributeSetId,'Msg'=>'Deleted successfully');
		}
		return array('Name'=>$attribute_set_name,'Id'=>$attributeSetId,'Msg'=>'Attribute Set not found');
	}

	/**
	 * Create an atribute-set.
	 *
	 * For reference, see Mage_Adminhtml_Catalog_Product_SetController::saveAction().
	 * @param string $setName
	 * @param string|int $copyGroupsFromID 
	 * @param string|int $stopGroup
	 * @param array|int $stopAttr
	 * 
	 * 
	 * @return array|false
	 */
	public function createAttributeSet($setName, $copyGroupsFromID = -1,$stopGroup=null,$stopAttr=null) {
 
		$setName = trim($setName);
 
		//$this->logInfo("Creating attribute-set with name [$setName].");
 
		if($setName == '') {
		   // $this->$this->logInfo("Could not create attribute set with an empty name.");
			Mage::log("Tried to create attribute set with an empty name.", Zend_Log::ERR);
			return false;
		}

		$model = Mage::getModel('eav/entity_attribute_set');
		$entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();

		$model->setEntityTypeId($entityTypeID);
		$model->setAttributeSetName($setName);
		//just in case, may not be needed
		$model->validate();
		try {
			$model->save();
		} catch(Exception $ex) {
			Mage::log("Initial attribute-set with name [$setName] could not be saved: " . $ex->getMessage(), Zend_Log::ERR);
			return false;
		}
 
		if(($id = $model->getId()) == false) {
			Mage::log("Could not get ID from new vanilla attribute-set with name [$setName].", Zend_Log::ERR);
			return false;
		}
		Mage::log("Set ($id) created.", Zend_Log::INFO);

		$baseGroups = $this->initFromSkeleton($copyGroupsFromID,$model,$stopGroup,$stopAttr);

		$modelGroup = Mage::getModel('eav/entity_attribute_group');
		$modelGroup->setAttributeGroupName("Event Details");
		$modelGroup->setAttributeSetId($model->getId());
		$modelGroup->setSortOrder(1);
	
		$modelGroup->setId(null)
			->setAttributeSetId($model->getId())
			->setDefaultId($modelGroup->getDefaultId())
			->setSortOrder(1)
			->setAttributes(array());
		$newGroups[] = $modelGroup;
		
		
		$model->setGroups( array_merge($baseGroups,$newGroups) );
		//$model->initFromSkeleton($copyGroupsFromID);
/*            var_dump($model);
die();  
$baseGroups =  $model->getGroups();

var_dump($baseGroups);
die();            */
		//<<<<
 
		// Save the final version of our set.
		try {
			$model->save();
		} catch(Exception $ex) {
			Mage::log("Final attribute-set with name [$setName] could not be saved: " . $ex->getMessage(), Zend_Log::ERR);
			return false;
		}
		if(($groupID = $modelGroup->getId()) == false) {
			Mage::log("Could not get ID from new group [$groupName].", Zend_Log::ERR);
			return false;
		}
		Mage::log("Created attribute-set with ID ($id) and default-group with ID ($groupID).", Zend_Log::INFO);
		return array(
						'SetID'     => $id,
						'GroupID'   => $groupID,
					);
	}
 
	/**
	 * Create an attribute.
	 *
	 * For reference, see Mage_Adminhtml_Catalog_Product_AttributeController::saveAction().
	 * @param string $lableText
	 * @param string $attributeCode
	 * @param string|int $values 
	 * @param string|int $productTypesA CSV like "simple, grouped, configurable, virtual, bundle, downloadable, giftcard"
	 * @param array|int $setInfo
	 * 
	 * @return int|false
	 */
	public function createAttribute($labelText, $attributeCode, $values = -1, $productTypes = -1, $setInfo = -1) {
 
		$labelText = trim($labelText);
		$attributeCode = trim($attributeCode);
 
		if($labelText == '' || $attributeCode == '') {
			Mage::log("Can't import the attribute with an empty label or code.  LABEL= [$labelText]  CODE= [$attributeCode]", Zend_Log::ERR);
			return false;
		}
 
		if($values === -1) {
			$values = array();
		}
 
		if($productTypes === -1) {
			$productTypes = array();
		}
 		//var_dump($setInfo);
		if($setInfo === -1 || empty($setInfo)){//(isset($setInfo['SetID']) == false || isset($setInfo['GroupID']) == false)) {
			//die('tried to retrun false');
			Mage::log("Failed provide both the set-ID and the group-ID of the attribute-set", Zend_Log::ERR);
			
			return false;
		}//die('made it');

		//>>>> Build the data structure that will define the attribute. See
		//     Mage_Adminhtml_Catalog_Product_AttributeController::saveAction().
 
		$data = array(
						'is_global'                     => '0',
						'frontend_input'                => 'text',
						'default_value_text'            => '',
						'default_value_yesno'           => '0',
						'default_value_date'            => '',
						'default_value_textarea'        => '',
						'is_unique'                     => '0',
						'is_required'                   => '0',
						'frontend_class'                => '',
						'is_searchable'                 => '1',
						'is_visible_in_advanced_search' => '1',
						'is_comparable'                 => '1',
						'is_used_for_promo_rules'       => '0',
						'is_html_allowed_on_front'      => '1',
						'is_visible_on_front'           => '0',
						'used_in_product_listing'       => '0',
						'used_for_sort_by'              => '0',
						'is_configurable'               => '0',
						'is_filterable'                 => '0',
						'is_filterable_in_search'       => '0',
						'backend_type'                  => 'varchar',
						'default_value'                 => '',
					);
 
		// Now, overlay the incoming values on to the defaults.
		/*foreach($values as $key => $newValue) {
			if(isset($data[$key]) == false) {
				Mage::log("Attribute feature [$key] is not valid while creating Attr set.", Zend_Log::ERR);
				return false;
			} else {
				$data[$key] = $newValue;
			}
		}*/
		
		$data = $this->extend($data,$values);
		
		// Valid product types: simple, grouped, configurable, virtual, bundle, downloadable, giftcard
		$data['apply_to']       = $productTypes;
		$data['attribute_code'] = $attributeCode;
		$data['frontend_label'] = array(
											0 => $labelText,
											1 => '',
											3 => '',
											2 => '',
											4 => '',
										);

		$model = Mage::getModel('catalog/resource_eav_attribute');
 
		$model->addData($data);

		if(isset($setInfo['SetID'])){
			$model->setAttributeSetId($setInfo['SetID']);
			$model->setAttributeGroupId($setInfo['GroupID']);
		}else{
			foreach($setInfo as $set){
				$model->setAttributeSetId($set['SetID']);
				$model->setAttributeGroupId($set['GroupID']);
			}
		}

		$entityTypeID = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
		$model->setEntityTypeId($entityTypeID);
 
		$model->setIsUserDefined(1);

		try {
			$model->save();
		}
		catch(Exception $ex) {
			Mage::log("Attribute [$labelText] could not be saved: " . $ex->getMessage(), Zend_Log::ERR);
			return false;
		}
 
		$id = $model->getId();

		Mage::log("Attribute [$labelText] has been saved as ID ($id).", Zend_Log::INFO);
		return $id;
	}
	
	/**
	 * Outputs the html checked attribute.
	 *
	 * Compares the first two arguments and if identical marks as checked
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $checked One of the values to compare
	 * @param mixed $current (true) The other value to compare if not just true
	 * @param bool $echo Whether to echo or just return the string
	 * @return string html attribute or empty string
	 */
	public function checked( $checked, $current = true, $echo = true ) {
		return __checked_selected_helper( $checked, $current, $echo, 'checked' );
	}
	
	/**
	 * Outputs the html selected attribute.
	 *
	 * Compares the first two arguments and if identical marks as selected
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $selected One of the values to compare
	 * @param mixed $current (true) The other value to compare if not just true
	 * @param bool $echo Whether to echo or just return the string
	 * @return string html attribute or empty string
	 */
	public function selected( $selected, $current = true, $echo = true ) {
		return __checked_selected_helper( $selected, $current, $echo, 'selected' );
	}
	
	/**
	 * Outputs the html disabled attribute.
	 *
	 * Compares the first two arguments and if identical marks as disabled
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $disabled One of the values to compare
	 * @param mixed $current (true) The other value to compare if not just true
	 * @param bool $echo Whether to echo or just return the string
	 * @return string html attribute or empty string
	 */
	public function disabled( $disabled, $current = true, $echo = true ) {
		return __checked_selected_helper( $disabled, $current, $echo, 'disabled' );
	}
	
	/**
	 * Private helper function for checked, selected, and disabled.
	 *
	 * Compares the first two arguments and if identical marks as $type
	 *
	 * @since 0.1.0
	 * @access private
	 *
	 * @param mixed $helper One of the values to compare
	 * @param mixed $current (true) The other value to compare if not just true
	 * @param bool $echo Whether to echo or just return the string
	 * @param string $type The type of checked|selected|disabled we are doing
	 * @return string html attribute or empty string
	 */
	private function __checked_selected_helper( $helper, $current, $echo, $type ) {
		if ( (string) $helper === (string) $current ){
			$result = " $type='$type'";
		}else{
			$result = '';
		}
		if ( $echo ){
			echo $result;
		}
		return $result;
	}
}


