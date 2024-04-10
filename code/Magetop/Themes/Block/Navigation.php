<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://Magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Themes\Block;

/**
 * Html page top menu block
 */
class Navigation extends \Magento\Theme\Block\Html\Topmenu
{
     /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $this->_menu, 'block' => $this]
        );

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);
		if($childrenWrapClass=='mbSearch'){
			$html = $this->_getSearchOption($this->_menu,$limit);
		}else{
			$html = $this->_getHtml($this->_menu, $childrenWrapClass, $limit);	
		}
        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_after',
            ['menu' => $this->_menu, 'transportObject' => $transportObject]
        );
        $html = $transportObject->getHtml();
        return $html;
    }
	 /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param \Magento\Framework\Data\Tree\Node $menuTree
     * @param string $childrenWrapClass
     * @param int $limit
     * @param array $colBrakes
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
    ) {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';
		$test=0;
        foreach ($children as $child) {
			if($childLevel == 0 and !$child->hasChildren() and $child->getIsActive()){
				$html .= '<ul class="mb-nav">'.$this->_getHtml1($this->_menu, $childrenWrapClass, $limit).'</ul>';
				break;	
			}
			if ($child->getHasActive() || $child->getIsActive() || $childLevel != 0) { $test++;
				$child->setLevel($childLevel);
				$child->setIsFirst($counter == 1);
				$child->setIsLast($counter == $childrenCount);
				$child->setPositionClass($itemPositionClassPrefix . $counter);								 			
				$outermostClassCode = '';	
				if (count($colBrakes) && $colBrakes[$counter]['colbrake']) {
					$html .= '</ul></li><li class="column"><ul>';
				}
				if($childLevel!=0){	
					$html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
					$html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '>';
					$html .= '<span>' . $this->escapeHtml(
						$child->getName()
					) . '</span></a>';
				}
				$html .= $this->_addSubMenu(
					$child,
					$childLevel,
					$childrenWrapClass,
					$limit
				);
				if($childLevel!=0){
					$html .= '</li>';
				}
				$itemPosition++;
				$counter++;
			}	
        }

        if (count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }
	protected function _getSearchOption(\Magento\Framework\Data\Tree\Node $menuTree, $curId) 
	{
        $html = '';
        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;
        $space='';
		foreach ($children as $child) {
			if ($childLevel < 3) {
				if($childLevel==1){ $space='--'; } 
				if($childLevel==2){ $space='----'; }
				$child->setLevel($childLevel);
				$catId = str_replace('category-node-', '', $child->getId());
				if($catId==$curId){
					$html .= '<option selected="selected" value="'.$catId.'">'.$space.$child->getName().'</option>';
				}else{
					$html .= '<option value="'.$catId.'">'.$space.$child->getName().'</option>';
				}				
				$html .= $this->_getSearchOption($child,$curId);
			}	
        }
        return $html;
    }

	protected function _getHtml1(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
    ) {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';
        foreach ($children as $child) {
				$child->setLevel($childLevel);
				$child->setIsFirst($counter == 1);
				$child->setIsLast($counter == $childrenCount);
				$child->setPositionClass($itemPositionClassPrefix . $counter);								 			
				$outermostClassCode = '';	
				if (count($colBrakes) && $colBrakes[$counter]['colbrake']) {
					$html .= '</ul></li><li class="column"><ul>';
				}
				$html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
				$html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '>';
				$html .= '<span>' . $this->escapeHtml(
					$child->getName()
				) . '</span></a>';
				$html .= $this->_addSubMenu(
					$child,
					$childLevel,
					$childrenWrapClass,
					$limit
				);
				if($childLevel!=0){
					$html .= '</li>';
				}
				$itemPosition++;
				$counter++;
        }

        if (count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }
    /**
     * Add identity
     *
     * @param array $identity
     * @return void
     */
    public function addIdentity($identity)
    {
        if (!in_array($identity, $this->identities)) {
            $this->identities[] = $identity;
        }
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->identities;
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        $keyInfo[] = $this->getUrl('*/*/*', ['_current' => true, '_query' => '']);
        return $keyInfo;
    }

    /**
     * Get tags array for saving cache
     *
     * @return array
     */
    protected function getCacheTags()
    {
        return array_merge(parent::getCacheTags(), $this->getIdentities());
    }

}
