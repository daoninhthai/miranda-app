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

class Permission extends \Magento\Framework\Model\AbstractModel
{
	protected $groups=array();
	public function __construct(\Magento\Customer\Model\Group $customerGroup)
	{
		$this->_customerGroup = $customerGroup;
	}
	
	public function getPermissionCollections()
	{
		$this->groups[]=array('value'=>'-1','label'=>'Public');
		$this->groups[]=array('value'=>'-2','label'=>'Registered');
		$collection = $this->_customerGroup->getCollection();
		foreach($collection as $value)
		{
			$this->groups[] = array(
					'value'=>$value->getCustomerGroupId(),
					'label' => $value->getCustomerGroupCode()
			);
		}
		return $this->groups;
	}

}
