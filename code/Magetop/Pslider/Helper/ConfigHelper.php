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


use Magento\Framework\App\Helper\AbstractHelper;
//use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;

class ConfigHelper extends AbstractHelper{
    CONST ENABLE = 'magetop_pslider/setting/enable';
//    CONST JQUERY = 'magetop_pslider/setting/jquery';
    CONST MAXPRODUCT = 'magetop_pslider/setting/maxproduct';
    CONST SLIDERITEM = 'magetop_pslider/setting/slideritem';
    CONST SHOWPRICE = 'magetop_pslider/setting/showprice';
    CONST SHOWADDCART = 'magetop_pslider/setting/showaddcart';
    CONST SHOWREVIEWS = 'magetop_pslider/setting/showreviews';
    CONST AUTOPLAY = 'magetop_pslider/setting/autoplay';
    CONST AUTOTIMEOUT = 'magetop_pslider/setting/autotimeout';
    CONST NAVIGATION = 'magetop_pslider/setting/navigation';
    CONST STOPONHOVER = 'magetop_pslider/setting/stoponhover';
    CONST SLIDERSPEED = 'magetop_pslider/setting/sliderspeed';

    public function __construct (Context $context) {
        parent::__construct ($context);
    }
/*    public function getJqueryLoad()
    {
        return $this->scopeConfig->getValue(self::JQUERY);
    }*/
    public function getMaxProduct()
    {
        $maxproduct = $this->scopeConfig->getValue(self::MAXPRODUCT);
        if($maxproduct == null || $maxproduct < 1) $maxproduct = 12;
        return $maxproduct;
    }
    public function getEnable(){
        return $this->scopeConfig->getValue(self::ENABLE);
    }
    public function getAutoplay()
    {
        if($this->scopeConfig->getValue(self::AUTOPLAY))return 'true';
        return 'false';
    }
	public function getAutotimeout()
    {
       $autoTimeout = $this->scopeConfig->getValue(self::AUTOTIMEOUT);
        return $autoTimeout;
    }
    public function getStopOnHover() {
        if($this->scopeConfig->getValue(self::STOPONHOVER)) return 'true';
        return 'false';
    }
    public function getSliderItem() {
        $slideritem = $this->scopeConfig->getValue(self::SLIDERITEM);
        if($slideritem < 1) $slideritem = 6;
        return $slideritem;
    }
    public function getSliderSpeed() {
        $speed = $this->scopeConfig->getValue(self::SLIDERSPEED);
        if($speed < 1) $speed = 250;
        return $speed;
    }
    public function getTemplateSettings() {
        $setting = new \stdClass();
        $setting->maxproduct = $this->getMaxProduct();
        $setting->showprice = $this->scopeConfig->getValue(self::SHOWPRICE);
        $setting->showreviews = $this->scopeConfig->getValue(self::SHOWREVIEWS);
        $setting->showaddcart = $this->scopeConfig->getValue(self::SHOWADDCART);
        return $setting;
    }
    public function getNavigation()	{
        $navval = $this->scopeConfig->getValue(self::NAVIGATION);
        return $navval;
    }

}