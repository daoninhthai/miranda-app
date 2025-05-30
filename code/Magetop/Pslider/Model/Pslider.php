<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Pslider\Model;


use Magetop\Pslider\Api\Data\PsliderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magetop\Pslider\Model\ResourceModel\Pslider as ResourceModel;
use Magento\Catalog\Model\Category;

class Pslider extends AbstractModel implements PsliderInterface,IdentityInterface{

    const ENABLE = 1;
    const DISABLE = 0;
    const CACHE_TAG = 'pslider';

    protected $_cacheTag = 'pslider';
    protected $_eventPrefix = 'pslider';
    protected function _construct ()
    {
        $this->_init('\Magetop\Pslider\Model\ResourceModel\Pslider');
    }
    /**
     * Prepare item's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::ENABLE => __('Enabled'), self::DISABLE => __('Disabled')];
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    public function beforeSave ()
    {
        return parent::beforeSave (); // TODO: Change the autogenerated stub
    }
    ///////////////////////////
    /// GETTERS IMPLEMENTS ///
    /////////////////////////

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->getData (self::ID);
    }

    /**
     * @return string|null
     */
    public function getTitle ()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @return string|null
     */
    public function getDesc()
    {
        return $this->getData(self::DESC);
    }

    /**
     * @return int|null
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * @return int|null
     */
    public function getTypeId()
    {
        return $this->getData(self::TYPE_ID);
    }

    /**
     * @return int|null
     */
    public function getCatsId()
    {
        return $this->getData(self::CATS_ID);
    }

    /**
     * @return int|null
     */
    public function getPos()
    {
        return $this->getData(self::POS);
    }
    /**
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    /**
     * @return string|null
     */
    public function getCreateTime()
    {
        return $this->getData(self::CREATE_TIME);
    }

    /**
     * @return string|null
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /////////////////////////
    // SETTERS IMPLEMENTS //
    ////////////////////////

    /**
     * @param int $id
     * @return \Magetop\Pslider\Api\Data\PsliderInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
    /**
     * @param string $title
     * @return \Magetop\Pslider\Api\Data\PsliderInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @param $desc string
     * @return \Magetop\Pslider\Api\Data\PsliderInterface
     */
    public function setDesc($desc)
    {
        return $this->setData(self::DESC, $desc);
    }

    /**
     * @param $category_id int
     * @return \Magetop\Pslider\Api\Data\PsliderInterface
     */
    public function setCategoryId($category_id)
    {
        return $this->setData(self::CATEGORY_ID, $category_id);
    }

    /**
     * @param $type_id int
     * @return \Magetop\Pslider\Api\Data\PsliderInterface
     */
    public function setTypeId($type_id)
    {
        return $this->setData(self::TYPE_ID, $type_id);
    }

    /**
     * @param $cats_id int
     * @return \Magetop\Pslider\Api\Data\PsliderInterface
     */
    public function setCatsId($cats_id)
    {
        return $this->setData(self::CATS_ID, $cats_id);
    }

    /**
     * @param $pos int
     * @return \Magetop\Pslider\Api\Data\PsliderInterface
     */
    public function setPos($pos)
    {
        return $this->setData(self::POS, $pos);
    }

    /**
     * @param $status int
     * @return \Magetop\Pslider\Api\Data\PsliderInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @param $create_time string
     * @return \Magetop\Pslider\Api\Data\PsliderInterface
     */
    public function setCreateTime($create_time)
    {
        return $this->setData(self::CREATE_TIME, $create_time);
    }

    /**
     * @param $update_time string
     * @return \Magetop\Pslider\Api\Data\PsliderInterface
     */
    public function setUpdateTime($update_time)
    {
        return $this->setData(self::UPDATE_TIME, $update_time);
    }
}