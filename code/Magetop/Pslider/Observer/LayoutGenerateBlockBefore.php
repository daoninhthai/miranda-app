<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Pslider\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magetop\Pslider\Helper\ConfigHelper;
use Magento\Framework\View\Element\Template\Context;

class LayoutGenerateBlockBefore implements ObserverInterface {
    private $pageConfig;
    public $_configHelper;
    public function __construct (Context $context,ConfigHelper $configHelper) {
        $this->pageConfig = $context->getPageConfig();
        $this->_configHelper = $configHelper;
    }

    public function execute (\Magento\Framework\Event\Observer $observer)
    {
        // TODO: Implement execute() method.
        $this->_setAssets();
    }
    public function _setAssets()
    {
        // SET CSS , JS
        $this->pageConfig->addPageAsset('Magetop_Pslider::css/pslider.css');
        //$this->pageConfig->addPageAsset('Magetop_Pslider::owl-carousel/owl.carousel.css');
        //$this->pageConfig->addPageAsset('Magetop_Pslider::owl-carousel/owl.theme.css');
        $this->pageConfig->addPageAsset('Magetop_Pslider::owl-carousel/owl.transitions.css');
    }
}