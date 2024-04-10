<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Model;

class Menupro extends \Magento\Framework\Model\AbstractModel
{
    public function __construct(
		\Magento\Store\Model\System\Store $systemStore,
		\Magento\Store\Model\Store $modelStore,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magetop\Menupro\Model\Menu $menuModel,
		\Magetop\Menupro\Helper\Data $menuproHelper
	)
	{
		$this->_systemStore = $systemStore;
		$this->_modelStore = $modelStore;
		$this->_storeManager = $storeManager;
		$this->_menuModel = $menuModel;
		$this->_menuproHelper = $menuproHelper;
	}
	/*Store switcher dropdown*/
    public function storeSwitcher()
    {
    	$store_info=$this->_systemStore->getStoreValuesForForm(false, true);
		$store_switcher="";
		$store_switcher.="<select id='store_switcher' onChange='MCP.storeFilter(this.value)'>";
			foreach($store_info as $value){
				
				if($value['value']==0){
					$store_switcher.="<option value='0'>".$value['label']."</option>";
				}else{
					$store_switcher.="<optgroup label='".$value['label']."'></optgroup>";
					if(!empty($value['value'])){
						foreach ($value['value'] as $option){
							$store_switcher.="<option value='".$option['value']."'>&nbsp;&nbsp;&nbsp;&nbsp;".$option['label']."</option>";
						}
					}
				}
			}
		$store_switcher.="</select>";
		return $store_switcher;
    }
	/*Store switcher filter dropdown*/
	public function storeSwitcherMulti()
    {
    	$store_info=$this->_systemStore->getStoreValuesForForm(false, true);
		$store_switcher="";
		$store_switcher.="<select id='storeids' class='required-entry span3' multiple='multiple' name='storeids[]'>";
			foreach($store_info as $value){
				
				if($value['value']==0){
					$store_switcher.="<option selected='selected' value='0'>" . $value['label'] . "</option>";
				}else{
					$store_switcher.="<optgroup label='".$value['label']."'></optgroup>";
					if(!empty($value['value'])){
						foreach ($value['value'] as $option){
							$store_switcher.="<option value='".$option['value']."'>&nbsp;&nbsp;&nbsp;&nbsp;".$option['label']."</option>";
						}
					}
				}
			}
		$store_switcher.="</select>";
		return $store_switcher;
    } 
	public function _getBaseUrl()
    {		
		return $this->_modelStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
    }
	public function getBaseUrl()
    {		
		$baseUrl = $this->_getBaseUrl();
		$validUrl = $this->_menuproHelper->getValidUrl($baseUrl);
		$baseUrl = $validUrl;
		return $baseUrl;
    }
	public function getSkinBaseurl()
    {		
		return $this->_modelStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
	public function getMCPUrl()
    {		
		$url = $this->getSkinBaseurl();
		$validUrl = $this->_menuproHelper->getValidUrl($url);
		return $validUrl;
    }
	public function getMenuCollectionByStore() {
    	$collection = $this->_menuModel->getCollection()->setOrder('position', 'asc');
    	//Get all menu_id in this store
    	$validMenuIds = array();
    	foreach ($collection as $menuItem) {
    		$tempStore = $menuItem->getStoreids();
    		$tempStoreArr = explode(',', $tempStore);
    		if (in_array($this->getStoreId(), $tempStoreArr) || in_array('0', $tempStoreArr)) {
    			$validMenuIds[] = $menuItem->getMenuId();
    		} 
    	}
    	//Add Filter to collection
		$newCollection = $this->_menuModel->getCollection();
    	$newCollection->addFieldToFilter('menu_id', array ("in" => $validMenuIds));
		$newCollection->addFieldToFilter('is_active', '1');
		$newCollection->setOrder('position', 'asc');
    	return $newCollection;
    }
	function getMenuByGroupId ($group_id, $permission) 
	{
		$menu = $this->getMenuCollectionByStore();
		$menu->addFieldToFilter('groupmenu_id', $group_id);
		$menu->addFieldToFilter('permission', array ("in" => $permission));
		
		return $menu;
	}
	function getStoreId () 
	{
		$storeId= $this->_storeManager->getStore()->getId();		
		return $storeId;
	}
	function getDefaultStoreId () 
	{
		$storeId= $this->_storeManager->getWebsite()->getDefaultGroup()->getDefaultStoreId();		
		return $storeId;
	}
	function getRootCategoryId () 
	{
		$rootId= $this->_storeManager->getStore()->getRootCategoryId();		
		return $rootId;
	}
	/**
	* Get a collection, get all items have status = 1
	* @param parentId, groupId, permission, storeid
	* @return collection
	*/
 	public function getChildMenu ($group_id, $parent_id, $permission) 
	{
    	$childMenu = $this->getMenuByGroupId($group_id, $permission);
    	$childMenu->addFieldToFilter('groupmenu_id', $group_id);
		$childMenu->addFieldToFilter('parent_id', $parent_id);
		$childMenu->addFieldToFilter('permission', array ("in" => $permission));
    	return $childMenu;
    }
	public function getCurrentUrl() {
		return $this->_storeManager->getStore()->getCurrentUrl();
	}
	public function getMediapath() {
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}
	public function getFrontendpath() {
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::SESSION_NAMESPACE);
	}
}