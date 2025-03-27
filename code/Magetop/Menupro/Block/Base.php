<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Block;

class Base extends \Magento\Framework\View\Element\Template
{
	protected $_plus = '<span class="mcp-icon fa-angle-plus-square expand"></span>';
	protected $_data_hover = "data-hover='mcpdropdown'";
	protected $_dropdown_toggle = "class='mcpdropdown-toggle'";
	protected $_rightIcon = '<i class="mcp-icon fa-chevron-right"></i>';
	protected $_urlValue;
	protected $_liClasses;
	protected $_aHref;
	protected $_aImage;
	protected $_aText;
    protected $_aTitle;
	protected $_aIcon;
	protected $_aTarget;
	protected $_parent = false;
	protected $_block;
	// protected $_type;
	protected $_menuLink;
	// protected $_itemUrlValue;
	/**
	* If li has sub item, then we need to add some class such as: parent,
	* has-sub, etc... Need a space before or after each class
	*/
	// protected $_liHasSubClasses = 'mcpdropdown parent ';
	protected $_liHasSubClasses = ' mcpdropdown parent ';
	// A class of li that has a link being actived.
	protected $_liActiveClass = ' active ';
	// Column layout classes: sub_one, sub_two...
	protected $_columnLayout = array (
			1 => 'one',
			2 => 'two',
			3 => 'three',
			4 => 'four',
			5 => 'five',
			6 => 'six',
			100 => 'full' 
	);
	//---Auto Show Sub--
	protected $_tree = array();
	public $categoryObject;
	/**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,		
		\Magento\Catalog\Model\Product $modelProduct,
		\Magetop\Menupro\Model\Categories $modelCategories,
		\Magetop\Menupro\Model\Groupmenu $groupmenuModel,	
		\Magetop\Menupro\Model\Cms $cmsModel,	
		\Magetop\Menupro\Model\Menu $menuModel,
		\Magetop\Menupro\Model\Menupro $menuproModel,
		\Magetop\Menupro\Helper\Data $menuproHelper,
		\Magetop\Menupro\Model\Groupmenu\Source\Options $groupmenuOptions,
		\Magetop\Menupro\Model\Groupmenu\Source $groupmenuSource,
        array $data = []
    ) {
		$this->_registry = $registry;
		$this->_modelCategories = $modelCategories;
		$this->_modelProduct = $modelProduct;
		$this->_groupmenuModel = $groupmenuModel;
		$this->_cmsModel = $cmsModel;
		$this->_menuModel = $menuModel;
		$this->_menuproModel = $menuproModel;
		$this->_menuproHelper = $menuproHelper;
		$this->_groupmenuOptions = $groupmenuOptions;
		$this->_groupmenuSource = $groupmenuSource;
        parent::__construct($context, $data);
    }
	/*
	public function menuDesignDir() {
		$baseDir = $this->_menuproModel->getFrontendpath();
		$path = $appDir ."menupro/static/";
		if(!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		return $path;
	}
	*/
	public function menuDesignDir() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$dir = $objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');
		$appDir = $dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::APP);
		$path = $appDir ."/code/Magetop/Menupro/view/frontend/templates/menupro/static/";
		if(!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		return $path;
	}
	public function getMenuFilename($groupId, $type = "") {
		$storeId = $this->_menuproModel->getStoreId();
		$extraPath = array($storeId, $groupId);
		$extraPathName =  join('_', $extraPath);
		$name = $type . "menupro_" . $extraPathName . "" . ".phtml";
		return $name;
	}
	public function isGroupResponsive($style) {
		$allResponsiveStyle = $this->_groupmenuOptions->getMobileResponsiveStyles();
		if (in_array($style, $allResponsiveStyle)) {
			return true;
		}
		return false;
	}
	public function getMenuCollection($groupId, $permission) {
		$isEnabled = $this->_groupmenuModel->load($groupId)->getIsActive();
		if ($isEnabled == 1) {
			return $this->_menuproModel->getMenuByGroupId ( $groupId, $permission);
		}
		return;
	}
	public function getArrow($position) {
		$data = array(
			'menu-creator-pro' => '<span class="mcp-icon fa-angle-down"></span>',
			'menu-creator-pro menu-creator-pro-top-fixed' => '<span class="mcp-icon fa-angle-down"></span>',
			'menu-creator-pro menu-creator-pro-left' => '<span class="mcp-icon fa-angle-right"></span>',
			'menu-creator-pro menu-creator-pro-left-fixed' => '<span class="mcp-icon fa-angle-right"></span>',
			'menu-creator-pro menu-creator-pro-right' => '<span class="mcp-icon fa-angle-left"></span>',
			'menu-creator-pro menu-creator-pro-right-fixed' => '<span class="mcp-icon fa-angle-left"></span>',
			'menu-creator-pro menu-creator-pro-bottom' => '<span class="mcp-icon fa-angle-up"></span>',
			'menu-creator-pro menu-creator-pro-bottom-fixed' => '<span class="mcp-icon fa-angle-up"></span>',
			'menu-creator-pro menu-creator-pro-accordion' => '<span class="mcp-icon fa-angle-down"></span>',
		);
		return $data[$position];
	}
	
	public function getSubArrow ($position) {
		return $this->getArrow($position);
	}
	public function getMenuLink($itemUrlValue, $type) {
		$store_id = $this->_menuproModel->getStoreId ();
		$defaultStoreId = $this->_menuproModel->getDefaultStoreId ();
		// Default store id = 11;
		// If current store is default, then remove store code from menu url
		switch ($type) {
			case 1 :
				if ($itemUrlValue == 'home') {
					if ($store_id == $defaultStoreId) {
						$this->_urlValue = $this->_menuproModel->getSkinBaseurl();
					} else {
						$this->_urlValue = $this->_menuproModel->_getBaseUrl();
					}
				} else {
					
					$this->_urlValue = $this->_cmsModel->getCmsUrl($itemUrlValue);
				}
				break;
			
			case 3 :
				if (strpos ( $itemUrlValue, 'http' ) === false) {
					if ($store_id == $defaultStoreId) {
						$this->_urlValue = $this->_menuproModel->getSkinBaseurl() . $itemUrlValue;
					} else {
						$this->_urlValue = $this->_menuproModel->_getBaseUrl() . $itemUrlValue;
					}
				} else {
					$this->_urlValue = $itemUrlValue;
				}
				break;
			
			case 4 :
				$rootId = $this->_menuproModel->getRootCategoryId();
				if ($itemUrlValue == $rootId) {
					$this->_urlValue = "#";
				} else {
					$this->_urlValue = $this->_modelCategories->getCatUrl($itemUrlValue);
				}
				break;
			
			case 5 :
				$_product = $this->_modelProduct->load($itemUrlValue);
				if ($store_id == $defaultStoreId) {
					$baseUrl = $this->_menuproModel->getSkinBaseurl();
				} else {
					$baseUrl = $this->_menuproModel->_getBaseUrl();
				}
				if($_product->getUrl() != "") {
					$this->_urlValue = $baseUrl . $_product->getUrl();
				} else {
					$this->_urlValue = $_product->getProductUrl(true);
				}
				break;			
			case 6 :
				$this->_urlValue = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId ( $itemUrlValue );
				break;
			
			case 7 :
				$this->_urlValue = '#';
				break;
			
			default :
				$mostUsedUrl = $this->_menuproHelper->getMostUsedUrl();
				if (array_key_exists($itemUrlValue, $mostUsedUrl)) {
					$this->_urlValue = $this->_menuproModel->getBaseUrl() . $mostUsedUrl[$itemUrlValue];
				} else {
					$this->_urlValue = "";
				}
				break;
		}
		if ($type != 6) {
			$validUrl = $this->_menuproHelper->getValidUrl($this->_urlValue);
			$this->_urlValue = $validUrl;
		}	
		return $this->_urlValue;
	}
	public function resetMenuItemVar() {
		$this->_liClasses = '';
		$this->_aHref = '';
		$this->_aImage = '';
		$this->_aText = '';
        $this->_aTitle = '';
		$this->_aTarget = '';
		$this->_block = '';
		$this->_aIcon = '';
		$this->_parent = false;
	}
	public function getChildMenu($groupId, $menuId, $permission) {
		return $this->_menuproModel->getChildMenu ( $groupId, $menuId, $permission );
	}
	public function autoSub($categoryId,$autoShowSub, $subArrow, $showMenuInLevel2 = false)
	{
		$html = "";
		if ($autoShowSub == null) {
			return false;
		}
		if (!is_numeric($categoryId)) {
			return false;
		}
		$arrowSymbol = $subArrow;//'<span class="mcp-arrow-down"></span>';
		$helper = $this->_menuproHelper;
		$isDevelopMode = $helper->isDevelopMode();
		//Check current url is secure or not
		$baseUrl = $this->_menuproModel->getBaseUrl();
		
		$childs = $this->_modelCategories->getChildCategoryCollection($categoryId);
		if ($childs == "") {
			return;
		}		
		$html .= "<ul>";		
		foreach ($childs as $child) {
			//-----------------Level 1-----------------------
			if ($child->getUrl()) {
				$newUrlPath = $child->getUrl();
				$urlPath = str_replace($baseUrl, '', $newUrlPath);
			}
			$childs = $this->_modelCategories->getChildCategoryCollection($child->getEntityId());
			$liClass = "autosub-item ";
			$ulClass = $dataHover = $arrow = '';
			if ( count($childs) > 0) {
				$liClass .= "parent mcpdropdown has-submenu";
				$dataHover .= 'data-hover="mcpdropdown"';
				$ulClass .= 'mcpdropdown-menu';
				$arrow = $arrowSymbol;
			}							
				$html .= "<li class='" . $liClass . "'>";
				
				if ($isDevelopMode) {
					$html .= '<a title="'. $child->getName() . '" href="' . $baseUrl . $urlPath  . '">' . $child->getName() . '</a>' . $arrow;
				} else {
					$categoryName = htmlentities($child->getName(), ENT_QUOTES, "UTF-8");
					$html .= '<a title="<?php echo __("'. $categoryName .'") ?>" href="<?php echo $this->_menuproModel->getMCPUrl(); ?>' . $urlPath .'"><?php echo __("'. $categoryName .'") ?></a>' . $arrow;
				}
				
				if ( count($childs) > 0) {
					//--------Level 2----------
					$html .= "<div class='autosub'>";
					$html .= "<ul class='" . $ulClass . "'>";
					foreach($childs as $child) {
						if ($child->getUrl()) {
							$newUrlPath = $child->getUrl();
							$urlPath = str_replace($baseUrl, '', $newUrlPath);
						}
						$childs = $this->_modelCategories->getChildCategoryCollection($child->getEntityId());
						$liClass = "autosub-item ";
						$ulClass = $dataHover = $arrow = '';
						if ( count($childs) > 0) {
							$liClass .= "parent mcpdropdown has-submenu";
							$dataHover .= 'data-hover="mcpdropdown"';
							$ulClass .= 'mcpdropdown-menu';
							$arrow = $arrowSymbol;
						}						
							$html .= "<li class='level3 " . $liClass . "'>";
							
                            if ($isDevelopMode) {
								$html .= '<a title="'. $child->getName() . '" href="' . $baseUrl . $urlPath  . '">' . $child->getName() . '</a>' . $arrow;
							} else {
								$categoryName = htmlentities($child->getName(), ENT_QUOTES, "UTF-8");
								$html .= '<a title="<?php echo __("'. $categoryName .'") ?>" href="<?php echo $this->_menuproModel->getMCPUrl(); ?>' . $urlPath .'"><?php echo __("'. $categoryName .'") ?></a>' . $arrow;
							}							
							// ------------- Level 3---------------
							if ( count($childs) > 0) {
								$html .= "<div class='autosub'>";
								$html .= "<ul class='" . $ulClass . "'>";
								foreach($childs as $child) {
									if ($child->getUrl()) {
										$newUrlPath = $child->getUrl();
										$urlPath = str_replace($baseUrl, '', $newUrlPath);
									}
									$childs = $this->_modelCategories->getChildCategoryCollection($child->getEntityId());
									$liClass = "autosub-item ";
									$ulClass = $dataHover = $arrow = '';
									if ( count($childs) > 0) {
										$liClass .= "parent mcpdropdown has-submenu";
										$dataHover .= 'data-hover="mcpdropdown"';
										$ulClass .= 'mcpdropdown-menu';
										$arrow = $arrowSymbol;
									}
										$html .= "<li class='" . $liClass . "'>";
                                        if ($isDevelopMode) {
											$html .= '<a title="'. $child->getName() . '" href="' . $baseUrl . $urlPath  . '">' . $child->getName() . '</a>' . $arrow;
										} else {
											$categoryName = htmlentities($child->getName(), ENT_QUOTES, "UTF-8");
											$html .= '<a title="<?php echo __("'. $categoryName .'") ?>" href="<?php echo $this->_menuproModel->getMCPUrl(); ?>' . $urlPath .'"><?php echo __("'. $categoryName .'") ?></a>' . $arrow;
										}
										// ------------- Level 4---------------
										if ( count($childs) > 0) {
											$html .= "<div class='autosub'>";
											$html .= "<ul class='" . $ulClass . "'>";
											foreach($childs as $child) {
												if ($child->getUrl()) {
													$newUrlPath = $child->getUrl();
													$urlPath = str_replace($baseUrl, '', $newUrlPath);
												}
												$liClass = "autosub-item ";
												$ulClass = $dataHover = $arrow = '';
													$html .= "<li class='" . $liClass . "'>";
                                                    if ($isDevelopMode) {
														$html .= '<a title="'. $child->getName() . '" href="' . $baseUrl . $urlPath  . '">' . $child->getName() . '</a>' . $arrow;
													} else {
														$categoryName = htmlentities($child->getName(), ENT_QUOTES, "UTF-8");
														$html .= '<a title="<?php echo __("'. $categoryName .'") ?>" href="<?php echo $this->_menuproModel->getMCPUrl(); ?>' . $urlPath .'"><?php echo __("'. $categoryName .'") ?></a>' . $arrow;
													}
													$html .= "</li>";
											}
											$html .= "</ul>";
											$html .= "</div>";
										}
										// ------------- Level 4---------------
										$html .= "</li>";
								}
								$html .= "</ul>";
								$html .= "</div>";
								// ------------- Level 3---------------
							}							
							$html .= "</li>";
					}					
					$html .= "</ul>";
					$html .= "</div>";
					//--------Level 2----------
				}				
				$html .= "</li>";
			//-----------------End Level 1-------------------
		}
		
		$html .= "</ul>";
		
		return $html;
	}
	public function exportMenupro($menuproHtml, $groupId, $type = "") {
		$temp1 = str_replace("</div>", "\n</div>\n", $menuproHtml);
		$temp2 = str_replace("</ul>", "</ul>\n", $temp1);
		$temp3 = str_replace("</li>", "\n</li>", $temp2);
		try {
			ob_start();
			echo $temp3;
			$content = ob_get_contents();
			//ob_end_flush();
			ob_end_clean();// Will not display the content
			$storeId = $this->_menuproModel->getStoreId();
			$path = $this->menuDesignDir();
			$filename = $this->getMenuFilename($groupId, $type);
			file_put_contents($path . $filename, $content);
		} catch (Exception $e) {
			//die($e);
			//Mage::log($e, null, 'menupro.log');
		}
		$response = array();
		if (file_exists($path . $filename)) {
			$response['success'] = true;
			$response['filename'] = $filename;
		} else {
			$response['error'] = true;
			$response['message'] = "Can not export menu file! Something went wrong ...";
		}
		return $response;
	}
	public function detectDevice() {
		$tablet_browser = 0;
		$mobile_browser = 0;
		 
		if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			$tablet_browser++;
		}
		 
		if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			$mobile_browser++;
		}
		 
		if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
			$mobile_browser++;
		}
		 
		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
		$mobile_agents = array(
			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			'newt','noki','palm','pana','pant','phil','play','port','prox',
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			'wapr','webc','winw','winw','xda ','xda-');
		 
		if (in_array($mobile_ua,$mobile_agents)) {
			$mobile_browser++;
		}
		 
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'opera mini') > 0) {
			$mobile_browser++;
			//Check for tablets on opera mini alternative headers
			$stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
			if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
			  $tablet_browser++;
			}
		}
		if ($tablet_browser > 0) {
		   return 'tablet';
		}
		else if ($mobile_browser > 0) {
		   return  'mobile';
		}
		else {
		   return 'desktop';
		}   
	}
}