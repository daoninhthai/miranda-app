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
class Categories extends \Magento\Framework\Model\AbstractModel
{
	protected $_modelCategories;
	public function __construct(\Magento\Catalog\Model\Category $modelCategories)
	{
		$this->_modelCategories = $modelCategories;
	}
	public function getChildCategoryCollection($parentId)
    {
		$categories=$this->getCategories();
		$categories->addFieldToFilter("parent_id",$parentId);
    	return $categories;
    }
	public function getCategories()
    {
    	$categories = $this->_modelCategories
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addIsActiveFilter();
		$categories->addFieldToFilter ( 'include_in_menu', 1 );
		$categories->addFieldToFilter ( 'is_active', 1 );
    	return $categories;
    }
	public function getCategoryOptions()
	{
		$categories=$this->getCategories();
		foreach ($categories as $value) {
			if($value->getParentId()==1){
				$categoryid=$value->getEntityId();
				$this->category_option[$categoryid]=$value->getName();
				//Check has child menu or not
				$hasChild=$this->getChildCategoryCollection($categoryid);
				if(count($hasChild)>0)
				{
					$this->selectRecursiveCategories($categoryid);
				}
			}
		}
		return $this->category_option;
	}
	public function getCategorySpace($categoryid)
	{
		$path = $this->_modelCategories->load($categoryid)->getPath();
		$space="";
		$num=explode("/", $path);
		for($i=1; $i<count($num);$i++)
		{
			$space=$space."&nbsp;&nbsp;&nbsp;";
		}
		return $space;
	}
	/*Get all parent menu fill to select box*/
	public function selectRecursiveCategories($parentID)
	{
		$childCollection=$this->getChildCategoryCollection($parentID);
		foreach($childCollection as $value){
			$categoryId=$value->getEntityId();
			//Check this menu has child or not
			$this->optionsymbol=$this->getCategorySpace($categoryId);
			$this->category_option[$categoryId]=$this->optionsymbol.$value->getName();
			$hasChild=$this->getChildCategoryCollection($categoryId);
			if(count($hasChild)>0)
			{
				$this->selectRecursiveCategories($categoryId);
			}
		}
	}
	public function getCatUrl($categoryId) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		return $objectManager->create('Magento\Catalog\Model\Category')->load($categoryId)->getUrl();
    } 
}
