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

class Pushnew  extends \Magetop\Menupro\Block\Menu
{
	public function getPushnewMenuHtml($groupId) {
		$backLink = '<a class="mp-back" href="#">'. __('back') .'</a>';
		$hasChildIcon = 'mb-icon mb-icon-arrow-left';
		if ($this->_menuproHelper->isDisableFrontEnd()) {
			return;
		}
		/* Get Group Permission Id of current login */
		$permission = $this->_menuproHelper->authenticate ();
		$menuCollection = $this->getMenuCollection ( $groupId, $permission);
		$DHTML = "";
		if ($menuCollection != NULL) {
			$DHTML .= "<ul>";
			foreach ( $menuCollection as $menu ) {
				if ($menu->getParentId () == 0) {
					$titleHide = false;
					$menuData = $this->getMenuData ( $menu, $permission);
					if ($menu->getType() == 6) {
						$DHTML .= "<li><div class='static-block ". $menuData['liClasses'] ."'>" . $menuData ['block'] . "</div></li>";
						continue;
					};
					//MCP Level 0
					if ($menuData ['hide_sub_header'] != 1) {
						$titleHide = true;
						if (count($menuData['childcollection']) || $menuData['isAutoShowSub']) {
							$DHTML .= '<li class="'.$hasChildIcon.'">';
						} else {
							$DHTML .= '<li>';
						}
						$DHTML .= self::getLink($menuData, $menu->getIconClass());
					}	
					if (count($menuData['childcollection'])) {
						if ($titleHide) {
							$DHTML .= '<div class="mp-level">';
							$DHTML .= '<h2 class="'.$menu->getIconClass().'">'.$menuData['aTitle'].'</h2>';
							$DHTML .= $backLink;
							$DHTML .= '<ul>';
						}
							foreach ($menuData['childcollection'] as $menu) {
								$titleHide1 = false;
								$menuData = $this->getMenuData($menu, $permission);
								if ($menu->getType() == 6) {
									$DHTML .= "<li><div class='static-block ". $menuData['liClasses'] ."'>" . $menuData ['block'] . "</div></li>";
									continue;
								};
								//MCP Level 1
								if ($menuData ['hide_sub_header'] != 1) {
									$titleHide1 = true;
									if (count($menuData['childcollection']) || $menuData['isAutoShowSub']) {
										$DHTML .= '<li class="'.$hasChildIcon.'">';
									} else {
										$DHTML .= '<li>';
									}
									$DHTML .= self::getLink($menuData, $menu->getIconClass());
								}
								if (count($menuData['childcollection'])) {
									if ($titleHide1) {
										$DHTML .= '<div class="mp-level">';
										$DHTML .= '<h2 class="'.$menu->getIconClass().'">'.$menuData['aTitle'].'</h2>';
										$DHTML .= $backLink;
										$DHTML .= '<ul>';
									}	
										foreach ($menuData['childcollection'] as $menu) {
											$titleHide2 = false;
											$menuData = $this->getMenuData($menu, $permission);
											if ($menu->getType() == 6) {
												$DHTML .= "<li><div class='static-block ". $menuData['liClasses'] ."'>" . $menuData ['block'] . "</div></li>";
												continue;
											};
											//MCP Level 2
											if ($menuData ['hide_sub_header'] != 1) {
												$titleHide2 = true;
												if (count($menuData['childcollection']) || $menuData['isAutoShowSub']) {
													$DHTML .= '<li class="'.$hasChildIcon.'">';
												} else {
													$DHTML .= '<li>';
												}
												$DHTML .= self::getLink($menuData, $menu->getIconClass());
											}	
											if (count($menuData['childcollection'])) {
												if($titleHide2){
													$DHTML .= '<div class="mp-level">';
													$DHTML .= '<h2 class="'.$menu->getIconClass().'">'.$menuData['aTitle'].'</h2>';
													$DHTML .= $backLink;
													$DHTML .= '<ul>';
												}
													foreach ($menuData['childcollection'] as $menu) {
														$titleHide3 = false;
														$menuData = $this->getMenuData($menu, $permission);
														if ($menu->getType() == 6) {
															$DHTML .= "<li><div class='static-block ". $menuData['liClasses'] ."'>" . $menuData ['block'] . "</div></li>";
															continue;
														};
														//MCP Level 3
														if ($menuData ['hide_sub_header'] != 1) {
															$titleHide3 = true;
															if (count($menuData['childcollection']) || $menuData['isAutoShowSub']) {
																$DHTML .= '<li class="'.$hasChildIcon.'">';
															} else {
																$DHTML .= '<li>';
															}
															$DHTML .= self::getLink($menuData, $menu->getIconClass());
														}
														if (count($menuData['childcollection'])) {
															if($titleHide3){
																$DHTML .= '<div class="mp-level">';
																$DHTML .= '<h2 class="'.$menu->getIconClass().'">'.$menuData['aTitle'].'</h2>';
																$DHTML .= $backLink;
																$DHTML .= '<ul>';
															}
																foreach ($menuData['childcollection'] as $menu) {
																	$menuData = $this->getMenuData($menu, $permission);
																	if ($menu->getType() == 6) {
																		$DHTML .= "<li><div class='static-block ". $menuData['liClasses'] ."'>" . $menuData ['block'] . "</div></li>";
																		continue;
																	};
																	//MCP Level 4
																	$DHTML .= '<li>';
																	$DHTML .= self::getLink($menuData, $menu->getIconClass());
																	$DHTML .= "</li>";
																	//MCP Level 4
																}
															if($titleHide3){
																$DHTML .= '</ul>';
																$DHTML .= '</div>';
															}
														} else {
															if ($menuData['isAutoShowSub']) {
																$autoSubMenu = $this->getPushnewAutoSub ( $menu->getUrlValue (), $menu->getAutosub ());
																if ($autoSubMenu != "") {
																	$DHTML .= '<div class="mp-level">';
																	$DHTML .= '<h2 class="'.$menu->getIconClass().'">'.$menuData['aTitle'].'</h2>';
																	$DHTML .= $backLink;
																	$DHTML .= $autoSubMenu;
																	$DHTML .= '</div>';
																}
															}
														}
														$DHTML .= "</li>";
														//MCP Level 3
													}
												if($titleHide2){
													$DHTML .= '</ul>';
													$DHTML .= '</div>';
												}	
											} else {
												if ($menuData['isAutoShowSub']) {
													$autoSubMenu = $this->getPushnewAutoSub ( $menu->getUrlValue (), $menu->getAutosub ());
													if ($autoSubMenu != "") {
														$DHTML .= '<div class="mp-level">';
														$DHTML .= '<h2 class="'.$menu->getIconClass().'">'.$menuData['aTitle'].'</h2>';
														$DHTML .= $backLink;
														$DHTML .= $autoSubMenu;
														$DHTML .= '</div>';
													}
												}
											}
											$DHTML .= "</li>";
											//MCP Level 2
										}
									if($titleHide1){
										$DHTML .= '</ul>';
										$DHTML .= '</div>';
									}	
								} else {
									if ($menuData['isAutoShowSub']) {
										$autoSubMenu = $this->getPushnewAutoSub ( $menu->getUrlValue (), $menu->getAutosub ());
										if ($autoSubMenu != "") {
											$DHTML .= '<div class="mp-level">';
											$DHTML .= '<h2 class="'.$menu->getIconClass().'">'.$menuData['aTitle'].'</h2>';
											$DHTML .= $backLink;
											$DHTML .= $autoSubMenu;
											$DHTML .= '</div>';
										}
									}
								}
								$DHTML .= "</li>";
								//MCP Level 1
							}
						if($titleHide){
							$DHTML .= '</ul>';
							$DHTML .= '</div>';
						}
					} else {
						if ($menuData['isAutoShowSub']) {
							$autoSubMenu = $this->getPushnewAutoSub ( $menu->getUrlValue (), $menu->getAutosub ());
							if ($autoSubMenu != "") {
								$DHTML .= '<div class="mp-level">';
								$DHTML .= '<h2 class="'.$menu->getIconClass().'">'.$menuData['aTitle'].'</h2>';
								$DHTML .= $backLink;
								$DHTML .= $autoSubMenu;
								$DHTML .= '</div>';
							}
						}
					}
					$DHTML .= "</li>";
					//End Level 0
				}
			}
			$DHTML .= "</ul>";
		}
		return $DHTML;
	}
	public static function  getLink($menuData, $iconClass) {
		$link = '<a class="' . $iconClass . '" title="' . $menuData['aTitle'] . '" target="' . $menuData['target'] . '" href="'.$menuData['aHref'].'">' . $menuData['aText'] . '</a>';
		return $link;
	}
	public function getPushnewAutoSub ($categoryId, $autoShowSub = null)
	{
		if ($autoShowSub == null) {
			return false;
		}
		if (!is_numeric($categoryId)) {
			return false;
		}
		$html = "";
		
		$backLink = '<a class="mp-back" href="#">'. __('back') .'</a>';
		$hasChildIcon = 'mb-icon mb-icon-arrow-left';
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
			//Update url_path in enterprise version
			if ($child->getUrl()) {
				$newUrlPath = $child->getUrl();
				$urlPath = str_replace($baseUrl, '', $newUrlPath);
			} 
			$childs = $this->_modelCategories->getChildCategoryCollection($child->getEntityId());
			$liClass = (count($childs)) ? "autosub-item " . $hasChildIcon . " " : "autosub-item ";
				$html .= "<li class='" . $liClass . "'>";
				if ($isDevelopMode) {
					$html .= '<a title="'. $child->getName() . '" href="' . $baseUrl . $urlPath  . '">' . $child->getName() . '</a>';
				} else {
					$categoryName = htmlentities($child->getName(), ENT_QUOTES, "UTF-8");
					$html .= '<a title="<?php echo __("'. $categoryName .'") ?>" href="<?php echo $this->_menuproModel->getMCPUrl(); ?>' . $urlPath .'"><?php echo __(("'. $categoryName .'") ?></a>';
				}
				if ( count($childs) > 0) {
					//--------Level 2----------
					$html .= "<div class='mp-level'>";
					$html .= "<h2>".$child->getName()."</h2>";
					$html .= $backLink;
					$html .= "<ul>";
					foreach($childs as $child) {
						if ($child->getUrl()) {
							$newUrlPath = $child->getUrl();
							$urlPath = str_replace($baseUrl, '', $newUrlPath);
						}
						$childs = $this->_modelCategories->getChildCategoryCollection($child->getEntityId());
						$liClass = (count($childs)) ? "autosub-item " . $hasChildIcon . " " : "autosub-item ";
							$html .= "<li class='level3 " . $liClass . "'>";
                            if ($isDevelopMode) {
								$html .= '<a title="'. $child->getName() . '" href="' . $baseUrl . $urlPath  . '">' . $child->getName() . '</a>';
							} else {
								$categoryName = htmlentities($child->getName(), ENT_QUOTES, "UTF-8");
								$html .= '<a title="<?php echo __("'. $categoryName .'") ?>" href="<?php echo $this->_menuproModel->getMCPUrl(); ?>' . $urlPath .'"><?php echo __(("'. $categoryName .'") ?></a>';
							}
							// ------------- Level 3---------------
							if ( count($childs) > 0) {
								$html .= "<div class='mp-level'>";
								$html .= "<h2>".$child->getName()."</h2>";
								$html .= $backLink;
								$html .= "<ul>";
								foreach($childs as $child) {
									if ($child->getUrl()) {
										$newUrlPath = $child->getUrl();
										$urlPath = str_replace($baseUrl, '', $newUrlPath);
									}
									$childs = $this->_modelCategories->getChildCategoryCollection($child->getEntityId());
									$liClass = (count($childs)) ? "autosub-item " . $hasChildIcon . " " : "autosub-item ";
									$ulClass = $dataHover = $arrow = '';
										$html .= "<li class='" . $liClass . "'>";
                                        if ($isDevelopMode) {
											$html .= '<a title="'. $child->getName() . '" href="' . $baseUrl . $urlPath  . '">' . $child->getName() . '</a>';
										} else {
											$categoryName = htmlentities($child->getName(), ENT_QUOTES, "UTF-8");
											$html .= '<a title="<?php echo __("'. $categoryName .'") ?>" href="<?php echo $this->_menuproModel->getMCPUrl(); ?>' . $urlPath .'"><?php echo __(("'. $categoryName .'") ?></a>';
										}
										// ------------- Level 4---------------
										if ( count($childs) > 0) {
											$html .= "<div class='mp-level'>";
											$html .= "<h2>".$child->getName()."</h2>";
											$html .= $backLink;
											$html .= "<ul>";
											foreach($childs as $child) {
												if ($child->getUrl()) {
													$newUrlPath = $child->getUrl();
													$urlPath = str_replace($baseUrl, '', $newUrlPath);
												}
												$liClass = "autosub-item ";
													$html .= "<li class='" . $liClass . "'>";
                                                    if ($isDevelopMode) {
														$html .= '<a title="'. $child->getName() . '" href="' . $baseUrl . $urlPath  . '">' . $child->getName() . '</a>';
													} else {
														$categoryName = htmlentities($child->getName(), ENT_QUOTES, "UTF-8");
														$html .= '<a title="<?php echo __("'. $categoryName .'") ?>" href="<?php echo $this->_menuproModel->getMCPUrl(); ?>' . $urlPath .'"><?php echo __(("'. $categoryName .'") ?></a>';
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
	public function getStaticHtml($groupId) {
		$filename = $this->getMenuFilename($groupId, "pushnew_");
		$path = $this->menuDesignDir();
		if (file_exists($path . $filename)) {
			$block = $this->getLayout()->createBlock('Magetop\Menupro\Block\Menu')->setTemplate("Magetop_Menupro::menupro/static/" . $filename)->toHtml();
		} else {
			//Create new static html file
			$menuHtml = $this->getPushnewMenuHtml($groupId);
			$response = $this->exportMenupro($menuHtml, $groupId, "pushnew_");
			if ($response['success']) {
				$block = $this->getLayout()->createBlock('Magetop\Menupro\Block\Menu')->setTemplate("Magetop_Menupro::menupro/static/" . $response['filename'])->toHtml();
			}
		}
		return $block;
	}
}