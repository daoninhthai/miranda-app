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

class Staticblock extends \Magento\Framework\Model\AbstractModel
{
	public function __construct(\Magento\Cms\Model\Block $cmsBlock)
	{
		$this->_cmsBlock = $cmsBlock;
	}
	public function getStaticBlockCollectionsForGrid()
	{
		$staticBlockOption=array();
		$collection = $this->_cmsBlock->getCollection();
		foreach($collection as $value)
		{
			if($value->getIsActive()==true){
				$staticBlockOption[$value->getIdentifier()]=$value->getTitle();
			}
		}

		return $staticBlockOption;
	}
}
