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


class Sitemap extends \Magento\Framework\Model\AbstractModel
{
	protected $_grid_nav = "";
	protected $_groupstatus = 0;
	public function __construct(
		\Magento\Store\Model\Store $modelStore,
		\Magetop\Menupro\Model\Groupmenu $groupmenuModel,
		\Magetop\Menupro\Model\Menu $menuModel,
		\Magetop\Menupro\Model\Menupro $menuproModel,
		\Magetop\Menupro\Helper\Data $menuproHelper,
		\Magento\Backend\Helper\Data $backendHelper
	)
	{
		$this->_modelStore = $modelStore;
		$this->_groupmenuModel = $groupmenuModel;
		$this->_menuModel = $menuModel;
		$this->_menuproModel = $menuproModel;
		$this->_menuproHelper = $menuproHelper;
		$this->_backendHelper = $backendHelper;
	}
	public function getChildMenuCollection ($parentId)
    {
    	$chilMenu = $this->_menuModel->getCollection()->setOrder("position","asc");
        $chilMenu->addFieldToFilter('parent_id',$parentId);
        return $chilMenu;
    }
	public function getAllMenu()
    {
    	$menus = $this->_menuModel->getCollection()->setOrder("groupmenu_id","asc")->setOrder("position","asc");
    	return $menus;
    }
	public function recursiveMenu ($parentid){
    	$chils = $this->getChildMenuCollection($parentid);
    	foreach($chils as $value) {
    		$haschild=$this->getChildMenuCollection($value->getMenuId());
    		$this->_grid_nav .= "<li id='m-" . $value->getMenuId() . "' store='" . $value->getStoreids() . "' class='group-"
				. $value->getGroupmenuId()
				. ((count($haschild) > 0) ? ' sm_liOpen' : '') 
				. (($value->getStatus() != 1) ? ' disabled' : '') 
				. (($value->getType() == 8) ? " separator-line" : "") . "'>";
    			//Is current url is secure or not
    			$edit_link = $this->_backendHelper->getUrl("menupro/menu/edit/",array("id"=>$value->getMenuId()));
    			$validUrl = $this->_menuproHelper->getValidUrl($edit_link, $this->_modelStore->isCurrentlySecure());
    			$edit_link = $validUrl;
    			$this->_grid_nav .= '<dl class="sm_s_published">';
    			$this->_grid_nav .= '<a href="#"class="sm_expander">&nbsp;</a>';
    			//Use category title as default
				$menuTitle = $value->getTitle();
				if ($value->getType() == 4 && $value->getUseCategoryTitle() == 2) {
					$menuTitle = $this->_menuproHelper->getCategoryTitle($value->getUrlValue());
				}
    			$this->_grid_nav .= '<dt><a class="sm_title" href="#" onclick="MCP.editMenu(\'' . $edit_link.'\')">' . $menuTitle . '</a></dt>';
    			$this->_grid_nav .= '<dd class="sm_actions">';
    			$this->_grid_nav .= ' <span class="sm_move" title="Move">Move</span>';
    			$this->_grid_nav .= ' <span class="sm_delete" title="Delete">Delete</span>';
    			$this->_grid_nav .= ' <a href="#" class="sm_addChild" title="Add Child">Add Child</a>';
    			$this->_grid_nav .= '</dd>';
    			$this->_grid_nav .= '<dd class="sm_status"><span class="' . (($value->getStatus() == 1) ? 'sm_pub' : 'sm_unpub') . '" ></span></dd>';
    			$this->_grid_nav .= '</dl>';
    			if(count($haschild)>0){
    				$this->_grid_nav.="<ul>";
    					$this->recursiveMenu($value->getMenuId());
    				$this->_grid_nav.="</ul>";
    			}
    		$this->_grid_nav.="</li>";
    	}
    }
	public function menuLists()
    {
    	$_menu = $this->getAllMenu();
		if(count($_menu)>0){
			$this->_grid_nav .= "<ul id='sitemap'>";
				//Store switcher dropdown
				$this->_grid_nav .= "<div class='menupro-switcher'>";
				$this->_grid_nav .= "<span class='store-view'>Choose Store View:</span>";
				$store_switcher = $this->_menuproModel->storeSwitcher();
				$this->_grid_nav .= $store_switcher;
				$this->_grid_nav .= "</div>";
				$_i=0;
				foreach ($_menu as $value){$_i++;
					if ($value->getParentId() == 0) {
						$groupid = $value->getGroupmenuId();
						$haschild = $this->getChildMenuCollection($value->getMenuId());
						//Group title of each group
						if($this->_groupstatus != $groupid){
							if($this->_groupstatus != 0) {
								$this->_grid_nav .= "<div class='bottom-button'><button type='button' class='btn btn-green' onclick='MCP.updateGroupTree(".$this->_groupstatus.");'>Save</button></div>";
							}							
							$group = $this->_groupmenuModel->load($groupid);
							$this->_grid_nav .= "<div id='group-".$value->getGroupmenuId()."' class='group-title group-menu'><h4>" . strtoupper($group->getTitle()) ."</h4><div class='group-action'><button type='button' onclick='viewGroup(".$value->getGroupmenuId().", \"" . $group->getTitle() . "\")' class='btn btn-blue'>Preview</button><button type='button' onclick='MCP.expandAll(".$value->getGroupmenuId().");' class='btn btn-expand'><i class='fa fa-caret-up'></i><i class='fa fa-caret-down'></i></button></div></div>";							
							$this->_groupstatus = $groupid;
						}
 						$this->_grid_nav .= "<li id='m-" . $value->getMenuId() . "' store='" . $value->getStoreids() . "' class='group-".$value->getGroupmenuId()
							. ((count($haschild) > 0) ? ' sm_liOpen' : '') 
							. (($value->getStatus() != 1) ? ' disabled' : '') 
							. (($value->getType() == 8) ? " separator-line" : "") . "'>";
							
 							//Is current url is secure or not
			    			$edit_link = $this->_backendHelper->getUrl("menupro/menu/edit/",array("id"=>$value->getMenuId()));
			    			$validUrl = $this->_menuproHelper->getValidUrl($edit_link, $this->_modelStore->isCurrentlySecure());
			    			$edit_link = $validUrl;
							$this->_grid_nav .= '<dl class="sm_s_published">';
							$this->_grid_nav .= '<a href="#"class="sm_expander">&nbsp;</a>';
								//Use category title as default
								$menuTitle = $value->getTitle();
								if ($value->getType() == 4 && $value->getUseCategoryTitle() == 2) {
									$menuTitle = $this->_menuproHelper->getCategoryTitle($value->getUrlValue());
								}
								$this->_grid_nav .= '<dt><a class="sm_title" href="#" onclick="MCP.editMenu(\'' . $edit_link.'\')">' . $menuTitle . '</a></dt>';
								$this->_grid_nav .= '<dd class="sm_actions">';
								$this->_grid_nav .= ' <span class="sm_move" title="Move">Move</span>';
								$this->_grid_nav .= ' <span class="sm_delete" title="Delete">Delete</span>';
								$this->_grid_nav .= ' <a href="#" class="sm_addChild" title="Add Child">Add Child</a>';
								$this->_grid_nav .= '</dd>';
								$this->_grid_nav .= '<dd class="sm_status"><span class="' . (($value->getStatus() == 1) ? 'sm_pub' : 'sm_unpub') . '"></span></dd>';
								$this->_grid_nav .= '</dl>';
							if (count($haschild) > 0) {
								$this->_grid_nav .= "<ul>";
									$this->recursiveMenu($value->getMenuId());
								$this->_grid_nav .= "</ul>";
							}
							
						$this->_grid_nav .= "</li>";
					}
					if($_i==count($_menu)){
						$this->_grid_nav .= "<div class='bottom-button'><button type='button' class='btn btn-green' onclick='MCP.updateGroupTree(".$this->_groupstatus.");'>Save</button></div>";
					}
				}
			$this->_grid_nav .= "</ul>";
		}else{
			$this->_grid_nav .= "<span class='no-menu' style='margin-left: 75px;'><em>There is no menu on this group <br/></em></span>";
		} 
    	return $this->_grid_nav;
    }
}
