<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2013-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected static $egridImgDir = null;
    protected static $egridImgURL = null;
    protected static $egridImgThumb = null;
    protected static $egridImgThumbWidth = null;
    protected $_allowedExtensions = Array();
	protected static $separatorLine = '--------------------';
	/**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager 
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\Category $categoryModel,
		\Magento\Customer\Model\Session $customerSession,
        \Magetop\Menupro\Model\Menu $menuModel
        ) {
        parent::__construct($context);
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->_categoryModel = $categoryModel;
		$this->_customerSession = $customerSession;
        $this->_menuModel = $menuModel;
    }
	public function getMenuTypes()
	{
		return array(
			'1' => 'CMS Page',
			'4' => 'Category Page',
			'6' => 'Static Block',
			'5' => 'Product Page',
			'3' => 'Custom Url',
			'7' => 'Alias [href=#]',
			'8'	=> 'Separator Line'
		);
	}
	public function getMostUsedLinks()
	{
		return array(
			'account' 	=> 'My Account',
			'cart' 		=> 'My Cart',
			'wishlist' 	=> 'My Wishlist',
			'checkout' 	=> 'Checkout',
			'login' 	=> 'Login',
			'logout' 	=> 'Logout',
			'register' 	=> 'Register',
			'contact' 	=> 'Contact Us'
		);
	}
	public function getSeparatorLine()
	{
		return self::$separatorLine;
	}
	public function getParentIds($menu_id)
	{
		$menu = $this->_menuModel;
		$p_id=$menu->load($menu_id)->getParentId();
		$p_ids=$p_id;
		//Stop this function when it parent is root node
		if($p_id!=0)
		{
			$p_ids=$p_ids."-".$this->getParentIds($p_id);
		}
		return $p_ids;
	}
	public function getMenuSpace($menu_id)
	{
		$space="";
		$parentIds=explode("-", $this->getParentIds($menu_id));
		for($i=1; $i<count($parentIds);$i++)
		{
			$space = $space."&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		return array(
			'blank_space' 	=> $space,
			'level'			=> count($parentIds)
		);
		
	}
	public function getValidUrl ($url, $isSecure=null) {
		$isSecure = $this->_storeManager->getStore()->isCurrentlySecure();
		if ($isSecure) {
			if (!strpos($url, 'https://')) {
				$validUrl = str_replace('http://', 'https://', $url);
				$url = $validUrl;
			}
		} else {
			if (!strpos($url, 'http://')) {
				$validUrl = str_replace('https://', 'http://', $url);
				$url = $validUrl;
			}
		}
		return $url;
	}
	public function getCategoryTitle ($categoryId) {
		$categoryInfo = $this->_categoryModel->load($categoryId)->getData();
		return $categoryInfo['name'];
	}
	public function isDevelopMode() {		
		$developMode = $this->scopeConfig->getValue('menupro/performance/develop_mode',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($developMode) {
			return true;
		}
		return false;
	}
	public function isDisableFrontEnd() {		
		$isDisableFrontEnd = $this->scopeConfig->getValue('menupro/setting/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($isDisableFrontEnd) {
			return false;
		}
		return true;
	}
	/**
	* Check current user permission
	* @return array permission id
	*/
	public function authenticate()
	{
		$permission = array(); 
		$permission [] = -1; // Pulbic as default. For all user
		$customerGroup = null;
		if ($this->_customerSession->isLoggedIn()) {
			$customerGroup = $this->_customerSession->getCustomerGroupId();
			/**
			* User who logged in can see their group permission and registered group as well.
			*/
			$permission [] = -2; //Registered
			$permission [] = $customerGroup; //Registered
		} else {
			$permission [] = $this->_customerSession->getCustomerGroupId();
		}
		return $permission;
	}
	public function getMostUsedUrl()
	{
		return array(
			'account' 	=> 'customer/account/',
			'cart' 		=> 'checkout/cart/',
			'wishlist' 	=> 'wishlist/',
			'checkout' 	=> 'checkout/',
			'login' 	=> 'customer/account/login/',
			'logout' 	=> 'customer/account/logout/',
			'register' 	=> 'customer/account/create/',
			'contact' 	=> 'contact/'
		);
	}	
	public function getConfig($cfg=null)
    {
        if($cfg) return $this->scopeConfig->getValue( $cfg, \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        return $this->scopeConfig;
    }
}