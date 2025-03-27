<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Block\Adminhtml\Menu;

/**
 * CMS block edit form container
 */
class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Data\Form\FormKey $formKey,
		\Magetop\Menupro\Model\Cms $cmsCollection,
		\Magetop\Menupro\Model\Staticblock $staticBlockCollections,
		\Magetop\Menupro\Model\Categories $categories,
		\Magetop\Menupro\Model\Groupmenu $groupmenuModel,	
		\Magetop\Menupro\Model\Menu $menuModel,
		\Magetop\Menupro\Model\Menupro $menuproModel,
		\Magetop\Menupro\Model\Permission $permission,
		\Magetop\Menupro\Model\Sitemap $sitemapModel,
		\Magetop\Menupro\Helper\Data $menuproHelper,		
        array $data = []
    ) {
		$this->_registry = $registry;
		$this->_formKey = $formKey;
		$this->_cmsCollection = $cmsCollection;
		$this->_staticBlockCollections = $staticBlockCollections;
		$this->_categories = $categories;
		$this->_groupmenuModel = $groupmenuModel;
		$this->_menuModel = $menuModel;
		$this->_menuproModel = $menuproModel;
		$this->_permission = $permission;
		$this->_sitemapModel = $sitemapModel;
		$this->_menuproHelper = $menuproHelper;
        parent::__construct($context, $data);
    }

    public function getCmsCollectionsForGrid()
    {		
		return $this->_cmsCollection->getCmsCollectionsForGrid();
    }
	public function getStaticBlockCollectionsForGrid()
    {		
		return $this->_staticBlockCollections->getStaticBlockCollectionsForGrid();
    }
	public function getCategoryOptions()
    {		
		return $this->_categories->getCategoryOptions();
    }
	public function getAllGroupArray()
	{
		$groupData = array ();
		$group_collection = $this->_groupmenuModel->getCollection();
		foreach ( $group_collection as $group ) {
			$groupData [] = array (
					'value' => $group->getGroupmenuId (),
					'label' => $group->getTitle (),
					'menu_type' => $group->getMenuType () 
			);
		}
		return $groupData;
	}
	public function getChildMenuCollection ($parentId)
    {
    	$chilMenu= $this->_menuModel->getCollection()->setOrder("position","asc");
    	//$chilMenu->addFieldToFilter('status','1');
        $chilMenu->addFieldToFilter('parent_id',$parentId);
        return $chilMenu;
    }
	public function getMenus()
    {
    	$menus=$this->_menuModel->getCollection()->setOrder("groupmenu_id","asc")->setOrder("position","asc");
    	return $menus;
    }
	/*Get all parent menu fill to select box*/
	public function selectRecursive ($parentID)
	{
		$childCollection=$this->getChildMenuCollection($parentID);
		foreach($childCollection as $value){
			$menuId = $value->getMenuId();
			//Check this menu has child or not
			$this->optionData = $this->_menuproHelper->getMenuSpace($menuId);
			$this->parentoption[$menuId] = array('title' => '&nbsp;&nbsp;&nbsp;&nbsp;' . $this->optionData['blank_space'] . $value->getTitle(), 'group_id' => $value->getGroupmenuId(), 'level' => $this->optionData['level']);
			$hasChild = $this->getChildMenuCollection($menuId);
			if(count($hasChild)>0)
			{
				$this->selectRecursive($menuId);
			}
		}
	}
	public function getParentOptions()
	{
		$menus=$this->getMenus();		
		$this->parentoption[0]=array('title'=>"Root",'group_id'=>'','level' => 0);
		if($menus){
			foreach ($menus as $value) {
			if($value->getParentId() == 0)
			{
				$menuid=$value->getMenuId();
				$this->parentoption[$menuid] = array('title'=>'&nbsp;&nbsp;&nbsp;&nbsp;' . $value->getTitle(),'group_id'=>$value->getGroupmenuId(),'level' => 1);
				//Check has child menu or not
				$hasChild=$this->getChildMenuCollection($menuid);
				if(count($hasChild)>0)
				{
					$this->selectRecursive($menuid);
				}
			}
			}
		}
		return $this->parentoption;
	}
	public function storeSwitcherMulti()
    {		
		return $this->_menuproModel->storeSwitcherMulti();
    }
	public function getPermissionCollections()
    {		
		return $this->_permission->getPermissionCollections();
    }
	public function getFormKey()
    {		
		return $this->_formKey->getFormKey();
    }
	public function getBaseUrl()
    {		
		return $this->_menuproModel->getBaseUrl();
    }
	public function getSkinBaseurl()
    {		
		return $this->_menuproModel->getSkinBaseurl();
    }
	public function menuLists()
    {		
		return $this->_sitemapModel->menuLists();
    }
}
