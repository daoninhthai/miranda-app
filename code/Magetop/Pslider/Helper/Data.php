<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Pslider\Helper;


use Magento\Backend\Helper\Data as BackendHelper;
use Magetop\Pslider\Model\PsliderCatsFactory;
use Magetop\Pslider\Model\PsliderFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Category;


class Data extends AbstractHelper{
    const ENABLE = 1;
    const DISABLE = 0;
    const BESTSELL = 1;
    const TOPRATE = 2;
    const MOSTVIEW = 3;
    const LASTET = 4;
    const NEWPRODUCT = 5;
    const SALEPRODUCT = 6;
    const TOPDOWN = 7;
    protected $_categoryModelFactory;
    protected $_PsliderFactory;
    protected $_PsliderCatFactory;

    public function __construct (Context $context,
                                 CategoryFactory $categoryFactory,
                                 PsliderFactory $psliderFactory,
                                 PsliderCatsFactory $slidercatsFactory)
    {
        parent::__construct ($context);
        $this->_categoryModelFactory = $categoryFactory;
        $this->_PsliderFactory = $psliderFactory;
        $this->_PsliderCatFactory = $slidercatsFactory;
    }
    public function getSliderTypes()
    {
        return [1 => __('Tab Sliders'),2 => __('List Sliders'),3 => __('Tab Vertical')];
    }
    public function getAvaibleGroupsSlider()
    {
        /**
         * @var \Magetop\Pslider\Model\PsliderCats $model
         * @var \Magetop\Pslider\Model\ResourceModel\PsliderCats\Collection $collection
         * @var \Magetop\Pslider\Model\ResourceModel\PsliderCats\Collection $item
         */
        $model = $this->_PsliderCatFactory->create();
        $collection = $model->getCollection()->load();
        $optionsArr = [' ------ Select Group ------'];
        foreach ($collection as $item) {
            $optionsArr[$item->getId()] = $item->getTitle();
        }
        return $optionsArr;
    }
    public function installGuide( $groupId , $layout = 1 )
    {
        $html = '1, To embed Menu Group in CMS/Static Block:

{{block class="Magetop\Pslider\Block\IndexBlock" name="pslider_' . $groupId . '" group_id="' . $groupId . '"}}

2, To reference in custom xml:

<referenceContainer name="content">
    <block class="Magetop\Pslider\Block\IndexBlock" name="pslider_' . $groupId . '">
        <arguments>
            <argument name="group_id" xsi:type="number">' . $groupId . '</argument>
        </arguments>
    </block>
</referenceContainer>
';

        return $html;
    }
    public function getAvaibleCategories()
    {
        /**
         * @var \Magento\Catalog\Model\Category $model
         * @var \Magento\Catalog\Model\ResourceModel\Category\Collection $list             *
         */
        $model = $this->_categoryModelFactory->create();
        $list = $model->getCollection()->load();
        return $this->getCategoriesOptions($list);
    }
    public function getCategoriesOptions($list)
    {
        $categoryArr = ['------ All Products ------'];
        foreach ($list as $item) {
            /**
             * @var \Magento\Catalog\Model\Category $item
             * @var \Magento\Catalog\Model\Category $list             *
             */
            if($item->getId() == 1)
            {
                continue;
            }
            $item->load($item->getId());
            $level = $item->getLevel();
			if($level == 2){
				for($i=0;$i<=$level;$i++)
				{
					$sperator = '--- ';
				}
				$categoryArr[$item->getId()] = $sperator . $item->getName();
				if($item->getChildrenCount() > 0)
				{
					$child = $item->getCategories($item->getId(),0,false,true,true);
					foreach ($child as $v) {
						$sperator2 = '------ ';
						$categoryArr[$v->getId()] = $sperator2 . $v->getName();
					}
				}
			}
        }
        return $categoryArr;
    }
    public function getAvaibleType()
    {
        return [
                self::LASTET => __('Latest Products'),
                self::BESTSELL => __('Best Sellers'),
                self::TOPRATE => __('Top Rated'),
                self::MOSTVIEW => __('Most Viewed'),
                self::TOPDOWN => __('Top Download Products'),
                self::NEWPRODUCT => __('New Products (Set the [New from/to date] attr in Product manage)'),
                self::SALEPRODUCT => __('Special Price (Set the [Special Price & from/to date] attr in Product manage)')
        ];
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
}