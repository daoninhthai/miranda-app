<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://Magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
 
namespace Magetop\Themes\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_labels = null;
    protected $_themeCfg = array();
    public function getConfig($cfg=null)
    {
        if($cfg) return $this->scopeConfig->getValue( $cfg, \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        return $this->scopeConfig;
    }
    public function getThemeCfg($cfg=null)
    {
        if(!$this->_themeCfg) $this->_themeCfg = $this->getConfig('themes');
        if(!$cfg) return $this->_themeCfg;
        elseif(isset($this->_themeCfg[$cfg])) return $this->_themeCfg[$cfg];
    }
    public function getLabels($product)
    {
        if($this->_labels==null) $this->_labels = $this->getThemeCfg('labels');		
        $html  = '';
		$saleLabel = isset($this->_labels['saleText']) ? $this->_labels['saleText'] : '';
        if($saleLabel && $this->isOnSale($product)){
			$html .= '<span class="Magetop-label onsale"><span class="labelsale">' . __($saleLabel) . '</span></span>';
		}else{
			$newText = isset($this->_labels['newText']) ? $this->_labels['newText'] : '';
			if($newText && $this->isNew($product)) $html .= '<span class="Magetop-label onnew"><span class="labelnew">' . __($newText) . '</span></span>';
		}                     
        return $html;
    }
    protected function isNew($product)
    {
        return $this->_nowIsBetween($product->getData('news_from_date'), $product->getData('news_to_date'));
    }
    protected function isOnSale($product)
    {
        $specialPrice = number_format($product->getFinalPrice(), 2);
        $regularPrice = number_format($product->getPrice(), 2);
        if ($specialPrice != $regularPrice) return $this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date'));
        else return false;
    } 
    protected function _nowIsBetween($fromDate, $toDate)
    {
        if ($fromDate){
            $fromDate = strtotime($fromDate);
            $toDate = strtotime($toDate);
            $now = strtotime(date("Y-m-d H:i:s"));
            
            if ($toDate){
                if ($fromDate <= $now && $now <= $toDate) return true;
            }else {
                if ($fromDate <= $now) return true;
            }
        }
        return false;
    }
    public function getResponsiveBreakpoints()
    {
        return array('col-lg-'=>'visible', 'col-md-'=>'desktop', 'col-sm-'=>'notebook', 'col-xs-'=>'tablet', 'col-xss-'=>'smallmobile');
    }
    public function getGridClass()
    {
        $stylesClass = '';
        $listCfg  = $this->getConfig('themes/grid');
        $breakpoints = $this->getResponsiveBreakpoints();
        foreach ($breakpoints as $key => $value) {
			$amountCol=12/$listCfg[$value];
			$stylesClass .= $key . $amountCol . ' ';
        }
        return  $stylesClass;
    }
    public function getConfgRUC($type)
    {
        $data = $this->getConfig('themes/' .$type);
        $breakpoints = $this->getResponsiveBreakpoints();
        $total = count($breakpoints);
        if($data['slide']){
            $data['vertical-Swiping'] = $data['vertical'];
            $responsive = '[';
            foreach ($breakpoints as $size => $opt) {
                $responsive .= '{"breakpoint": "'.$size.'", "settings": {"slidesToShow": "'.$data[$opt].'"}}';
                $total--;
                if($total) $responsive .= ', ';
            }
            $responsive .= ']';
            $data['slides-To-Show'] = $data['visible'];
            $data['swipe-To-Slide'] = 'true';
            $data['responsive'] = $responsive;
            $Rm = array('slide', 'visible', 'desktop', 'notebook', 'tablet', 'landscape', 'portrait', 'mobile');
            foreach ($Rm as $vl) { unset($data[$vl]); }

            return $data;

        } else {
            $options = array();
            $breakpoints = $this->getResponsiveBreakpoints(); ksort($breakpoints);
            foreach ($breakpoints as $size => $screen) {
                $options[]= array($size => $data[$screen]);
            }
            return array('padding' => $data['padding'], 'responsive' =>json_encode($options));
        }
    }
}
