<?php

class Wsu_Storeutilities_Helper_Utilities extends Mage_Core_Helper_Abstract {
	
	public function logInfo($String){
			echo $String."<br/>";
	}
	
	public function getUniqueCode($length = ""){
		$code = md5(uniqid(rand(), true));
		if ($length != "") return substr($code, 0, $length);
		else return $code;
	}
	public function csv_to_array($filename='', $delimiter=','){
		 if(!file_exists($filename) || !is_readable($filename))
			 return FALSE;
	
		 $header = NULL;
		 $data = array();
		 if (($handle = fopen($filename, 'r')) !== FALSE){
			 while (($row = fgetcsv($handle,1000, $delimiter)) !== FALSE){
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
	
	public function moveStoreProducts($website,$store,$rootcat,$children=null){
		if($children==null)$children = Mage::getModel('catalog/category')->getCategories($rootcat);
		foreach ($children as $category) {
			//echo $category->getName();
			$cat_id=$category->getId();
			$category = Mage::getModel('catalog/category')->load($cat_id);
			$collection = $category->getProductCollection();
			foreach ($collection as $product){
				$oldproductId = $product->getId();
				$_product=$product->load($productId);
				$sku = $_product->getSku();
				//echo "--------------------------------\n for ".$sku.' old webids:'.implode(',',$_product->getWebsiteIds()).' && storeid:'.$oldproductId." \n";
				try{
					$_product->setWebsiteIds(array($website)); //assigning website ID
					$_product->setStoreId($store);
					$_product->save();
				}catch (Exception $e) {
				   echo  'failed on sku:: ',$sku,"\n",$e->getMessage(),"\n";
				}
				//$newproductId = $_product->getId();
				//echo 'new webids:'.implode(',',$_product->getWebsiteIds()).' && storeid:'.$newproductId."\n";
			}
			$childrenCats = Mage::getModel('catalog/category')->getCategories($cat_id);
			if( count($childrenCats)>0){ moveStoreProducts($website,$site,$cat_id,$childrenCats); }
		}
	}
	
	public function make_store($categoryName,$site,$store,$view,$url="",$movingcat=-1){
		//#adding a root cat for the new store we will create
		// Create category object
		$category = Mage::getModel('catalog/category');
		$category->setStoreId(0); // No store is assigned to this category
		
		$rcat['name'] = $categoryName;
		$rcat['path'] = "1"; // this is the catgeory path - 1 for root category
		$rcat['display_mode'] = "PRODUCTS";
		$rcat['is_active'] = 1;
		
		$category->addData($rcat);
		$rcatId=0;
		try {
			$category->save();
			$rcatId = $category->getId();
		}
			catch (Exception $e){
			echo $e->getMessage();
		}
		if($rcatId>0){
			if($movingcat>0){
				$category = Mage::getModel( 'catalog/category' )->load($movingcat);
				Mage::unregister('category');
				Mage::unregister('current_category');
				Mage::register('category', $category);
				Mage::register('current_category', $category);
				$category->move($rcatId);
			}
	
		//#addWebsite
			/** @var $website Mage_Core_Model_Website */
			$website = Mage::getModel('core/website');
			$website->setCode($site['code'])
				->setName($site['name'])
				->save();
			$webid = $website->getId();
		//#addStoreGroup
			/** @var $storeGroup Mage_Core_Model_Store_Group */
			$storeGroup = Mage::getModel('core/store_group');
			$storeGroup->setWebsiteId($website->getId())
				->setName($store['name'])
				->setRootCategoryId($rcatId)
				->save();
			$cDat = new Mage_Core_Model_Config();
			$cDat->saveConfig('web/unsecure/base_url', "http://".$url.'/', 'websites', $webid);
			$cDat->saveConfig('web/secure/base_url', "https://".$url.'/', 'websites', $webid);
		//#addStore
			/** @var $store Mage_Core_Model_Store */
			$sotercode=$view['code'];
			$store = Mage::getModel('core/store');
			$store->setCode($sotercode)
				->setWebsiteId($storeGroup->getWebsiteId())
				->setGroupId($storeGroup->getId())
				->setName($view['name'])
				->setIsActive(1)
				->save();
			
			$storeid = $store->getId();
			moveStoreProducts($webid,$storeid,$rcatId);
			$cmsPageData = array(
				'title' => $site['name'],
				'root_template' => 'one_column',
				'meta_keywords' => 'meta,keywords',
				'meta_description' => 'meta description',
				'identifier' => 'home',
				'content_heading' => '',
				'is_active' => 1,
				'stores' => array($storeid),//available for all store views
				
				//this should be loaded
				'content' => '<div class="col-left side-col">
	<p class="home-callout"><a href="{{store direct_url="#"}"> <img src="{{storemedia url="/ph_callout_left_top.jpg"}}" alt="" border="0" /> </a></p>
	<p class="home-callout"><img src="{{storemedia url="/ph_callout_left_rebel.jpg"}}" alt="" border="0" /></p>
	{{block type="tag/popular" template="tag/popular.phtml"}}</div>
	<div class="home-spot">
	<p class="home-callout"><img src="{{storemedia url="/home_main_callout.jpg"}}" alt="" width="535" border="0" /></p>
	<p class="home-callout"><img src="{{storemedia url="/free_shipping_callout.jpg"}}" alt="" width="535" border="0" /></p>
	</div>
	<h1>Sites in the center</h1>
	<p>{{block type="catalog/product" stores_per="5" products_per="2" panles_per="3" template="custom_block/site_list.phtml"}}</p>'
			);
			
			Mage::getModel('cms/page')->setData($cmsPageData)->save();
		}
		return $rcatId;
	}
	public function createCmsPage(){
			$cmsPageData = array(
				'title' => $site['name'],
				'root_template' => 'one_column',
				'meta_keywords' => 'meta,keywords',
				'meta_description' => 'meta description',
				'identifier' => 'home',
				'content_heading' => '',
				'is_active' => 1,
				'stores' => array($storeid),//available for all store views
				
				//this should be loaded
				'content' => '<div class="col-left side-col">
	<p class="home-callout"><a href="{{store direct_url="#"}"> <img src="{{storemedia url="/ph_callout_left_top.jpg"}}" alt="" border="0" /> </a></p>
	<p class="home-callout"><img src="{{storemedia url="/ph_callout_left_rebel.jpg"}}" alt="" border="0" /></p>
	{{block type="tag/popular" template="tag/popular.phtml"}}</div>
	<div class="home-spot">
	<p class="home-callout"><img src="{{storemedia url="/home_main_callout.jpg"}}" alt="" width="535" border="0" /></p>
	<p class="home-callout"><img src="{{storemedia url="/free_shipping_callout.jpg"}}" alt="" width="535" border="0" /></p>
	</div>
	<h1>Sites in the center</h1>
	<p>{{block type="catalog/product" stores_per="5" products_per="2" panles_per="3" template="custom_block/site_list.phtml"}}</p>'
			);
			
			return Mage::getModel('cms/page')->setData($cmsPageData)->save();
	}
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
			echo " -> added cat ".$catsId."<br/>";
			if(isset($catInfo['children'])&& !empty($catInfo['children'])){
				echo " MAKING CHILDREN FOR -> added cat ".$catsId."<br/>";
				createCat($storeCodeId,$rootcatID.'/'.$catsId,$catInfo['children']);
			}
		}
	}	

    public function initFromSkeleton($skeletonId,$set,$stopGroup=null,$stopAttr=null) {
        $groups = Mage::getModel('eav/entity_attribute_group')
            ->getResourceCollection()
            ->setAttributeSetFilter($skeletonId)
            ->load();
    
        $newGroups = filterGroups($set,$groups,$stopGroup,$stopAttr);
        //$set->setGroups($newGroups);
        //return $set;   
        return $newGroups;
    }

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
                            //->setAttributeGroupId($newGroup->getId())
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
        return $newGroups;
        //$set->setGroups($newGroups);
        //return $set;       
    }


        /**
         * Create an atribute-set.
         *
         * For reference, see Mage_Adminhtml_Catalog_Product_SetController::saveAction().
         * @
		 * 
		 * 
		 * 
         * @return array|false
         */
        public function createAttributeSet($setName, $copyGroupsFromID = -1,$stopGroup=null,$stopAttr=null) {
     
            $setName = trim($setName);
     
            $this->logInfo("Creating attribute-set with name [$setName].");
     
            if($setName == '') {
               // $this->$this->logInfo("Could not create attribute set with an empty name.");
                return false;
            }
     
            //>>>> Create an incomplete version of the desired set.
            $model = Mage::getModel('eav/entity_attribute_set');
     
            // Set the entity type.
            $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();
            $this->logInfo("Using entity-type-ID ($entityTypeID).");
     
            $model->setEntityTypeId($entityTypeID);
     
            // We don't currently support groups, or more than one level. See
            // Mage_Adminhtml_Catalog_Product_SetController::saveAction().
     
           // $this->$this->logInfo("Creating vanilla attribute-set with name [$setName].");
     
            $model->setAttributeSetName($setName);
     
            // We suspect that this isn't really necessary since we're just
            // initializing new sets with a name and nothing else, but we do
            // this for the purpose of completeness, and of prevention if we
            // should expand in the future.
            $model->validate();
     
            // Create the record.
     
            try {
                $model->save();
            } catch(Exception $ex) {
               // $this->$this->logInfo("Initial attribute-set with name [$setName] could not be saved: " . $ex->getMessage());
                return false;
            }
     
            if(($id = $model->getId()) == false) {
                $this->logInfo("Could not get ID from new vanilla attribute-set with name [$setName].");
                return false;
            }
     
            $this->logInfo("Set ($id) created.");
     
            //<<<<
     
            //>>>> Load the new set with groups (mandatory).
     
            // Attach the same groups from the given set-ID to the new set.
            if($copyGroupsFromID === -1) {
                $this->logInfo("Cloning group configuration from existing set with ID ($copyGroupsFromID).");
               
               //$copyGroupsFromID = Mage::getModel(’catalog/product’)->getResource()->getEntityType()->getDefaultAttributeSetId(); 
                
                //$attributeSetName = "Default"; // put your own attribute set name
                //$attribute_set = Mage::getModel("eav/entity_attribute_set")->getCollection();
                //$attribute_set->addFieldToFilter("attribute_set_name", $attributeSetName)->getFirstItem();
                //$copyGroupsFromID = $attribute_set->getDefaultAttributeSetId();
            }
            $baseGroups = initFromSkeleton($copyGroupsFromID,$model,$stopGroup,$stopAttr);
            //$baseGroups =  $model->getGroups();
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
                $this->logInfo("Final attribute-set with name [$setName] could not be saved: " . $ex->getMessage());
                return false;
            }
            if(($groupID = $modelGroup->getId()) == false) {
                $this->logInfo("Could not get ID from new group [$groupName].");
                return false;
            }
     
            $this->logInfo("Created attribute-set with ID ($id) and default-group with ID ($groupID).");
     
            return array(
                            'SetID'     => $id,
                            'GroupID'   => $groupID,
                        );
        }
     
        /**
         * Create an attribute.
         *
         * For reference, see Mage_Adminhtml_Catalog_Product_AttributeController::saveAction().
         * @lableText : string -
		 * @attributeCode : string -
		 * @values : string|-1 -
		 * @productTypes : string|-1 - A CSV like "simple, grouped, configurable, virtual, bundle, downloadable, giftcard"
		 * @setInfo : array|-1 -
		 * 
         * @return int|false
         */
        public function createAttribute($labelText, $attributeCode, $values = -1, $productTypes = -1, $setInfo = -1) {
     
            $labelText = trim($labelText);
            $attributeCode = trim($attributeCode);
     
            if($labelText == '' || $attributeCode == '') {
                $this->logInfo("Can't import the attribute with an empty label or code.  LABEL= [$labelText]  CODE= [$attributeCode]");
                return false;
            }
     
            if($values === -1) {
                $values = array();
            }
     
            if($productTypes === -1) {
                $productTypes = array();
            }
     
            if($setInfo !== -1 && (isset($setInfo['SetID']) == false || isset($setInfo['GroupID']) == false)) {
                $this->logInfo("Please provide both the set-ID and the group-ID of the attribute-set if you'd like to subscribe to one.");
                return false;
            }
     
            $this->logInfo("Creating attribute [$labelText] with code [$attributeCode].");
     
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
            foreach($values as $key => $newValue) {
                if(isset($data[$key]) == false) {
                    $this->logInfo("Attribute feature [$key] is not valid.");
                    return false;
                } else {
                    $data[$key] = $newValue;
                }
            }
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
     
            if($setInfo !== -1) {
                $model->setAttributeSetId($setInfo['SetID']);
                $model->setAttributeGroupId($setInfo['GroupID']);
            }
     
            $entityTypeID = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
            $model->setEntityTypeId($entityTypeID);
     
            $model->setIsUserDefined(1);

            try {
                $model->save();
            }
            catch(Exception $ex) {
                $this->logInfo("Attribute [$labelText] could not be saved: " . $ex->getMessage());
                return false;
            }
     
            $id = $model->getId();
     
            $this->logInfo("Attribute [$labelText] has been saved as ID ($id).");
     
            return $id;
        }




	
}

