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

class Menu extends \Magento\Framework\Model\AbstractModel
{
    /**
     * CMS cache tag
     */
    const CACHE_TAG = 'menu';

    /**
     * @var string
     */
    protected $_cacheTag = 'menu';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'menu';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magetop\Menupro\Model\ResourceModel\Menu');
    }
}
