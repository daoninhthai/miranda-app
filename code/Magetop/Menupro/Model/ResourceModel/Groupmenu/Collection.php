<?php 
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Model\ResourceModel\Groupmenu;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected $_idFieldName = 'groupmenu_id';

    protected function _construct()
    {
        $this->_init('Magetop\Menupro\Model\Groupmenu', 'Magetop\Menupro\Model\ResourceModel\Groupmenu');
    }

}
