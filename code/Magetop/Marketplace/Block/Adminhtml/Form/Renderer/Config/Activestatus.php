<?php
namespace Magetop\Marketplace\Block\Adminhtml\Form\Renderer\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Activestatus extends Field
{
    protected $marketplaceHelper;
    protected $actModelFactory;
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magetop\Marketplace\Helper\Data $marketplaceHelper,
        \Magetop\Marketplace\Model\ActFactory $actModelFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->marketplaceHelper = $marketplaceHelper;
        $this->actModelFactory = $actModelFactory;
    }
    /*protected function _getElementHtml(AbstractElement $element)
    {
        $main_domain = $this->marketplaceHelper->get_domain( $_SERVER['SERVER_NAME'] );
		if ( $main_domain != 'dev' ) {
            $rakes = $this->actModelFactory->create()->getCollection();
            $rakes->addFieldToFilter('path', 'marketplace/act/key' );
            $valid = false;
            if ( count($rakes) > 0 ) {
                foreach ( $rakes as $rake )  {
                    if ( $rake->getExtensionCode() == md5($main_domain.trim($this->marketplaceHelper->getStoreConfigData('marketplace/act/key')) ) ) {
                        $valid = true;	
                    }
                }
            }	
            $html = base64_decode('PHAgc3R5bGU9ImNvbG9yOiByZWQ7Ij48Yj5OT1QgVkFMSUQ8L2I+PC9wPjxhIGhyZWY9Imh0dHBzOi8vd3d3Lm1hZ2V0b3AuY29tL21hZ2VudG8tMi1tdWx0aS12ZW5kb3ItbWFya2V0cGxhY2UtZXh0ZW5zaW9uLmh0bWwiIHRhcmdldD0iX2JsYW5rIj5WaWV3IFByaWNlPC9hPjwvYnI+');
            if ( $valid == true ) {
            //if ( count($rakes) > 0 ) {  
                foreach ( $rakes as $rake )  {
                    if ( $rake->getExtensionCode() == md5($main_domain.trim($this->marketplaceHelper->getStoreConfigData('marketplace/act/key')) ) ) {
                        $html = base64_decode('PGhyIHdpZHRoPSIyODAiPjxiPltEb21haW5Db3VudF0gRG9tYWluIExpY2Vuc2U8L2I+PGJyPjxiPkFjdGl2ZSBEYXRlOiA8L2I+W0NyZWF0ZWRUaW1lXTxicj48YSBocmVmPSJodHRwczovL3d3dy5tYWdldG9wLmNvbS9tYWdlbnRvLTItbXVsdGktdmVuZG9yLW1hcmtldHBsYWNlLWV4dGVuc2lvbi5odG1sIiB0YXJnZXQ9Il9ibGFuayI+VmlldyBQcmljZTwvYT48YnI+');
                        $html = str_replace(array('[DomainCount]','[CreatedTime]'),array($rake->getDomainCount(),$rake->getCreatedTime()),$html);
                    }
                }
            }
		} else { 
		    $html = base64_decode('PHAgc3R5bGU9ImNvbG9yOiByZWQ7Ij48Yj5OT1QgVkFMSUQ8L2I+PC9wPjxhIGhyZWY9Imh0dHBzOi8vd3d3Lm1hZ2V0b3AuY29tL21hZ2VudG8tMi1tdWx0aS12ZW5kb3ItbWFya2V0cGxhY2UtZXh0ZW5zaW9uLmh0bWwiIHRhcmdldD0iX2JsYW5rIj5WaWV3IFByaWNlPC9hPjwvYnI+');
		}	
        return $html;       
    }*/
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = base64_decode('PHAgc3R5bGU9ImNvbG9yOiByZWQ7Ij48Yj5OT1QgVkFMSUQ8L2I+PC9wPjxhIGhyZWY9Imh0dHBzOi8vd3d3Lm1hZ2V0b3AuY29tL21hZ2VudG8tMi1tdWx0aS12ZW5kb3ItbWFya2V0cGxhY2UtZXh0ZW5zaW9uLmh0bWwiIHRhcmdldD0iX2JsYW5rIj5WaWV3IFByaWNlPC9hPjwvYnI+');
        $mainDomain = $this->marketplaceHelper->getYourDomain( $_SERVER['SERVER_NAME']);
        if($mainDomain != 'dev') {
            $isVaild = false;
            $domainCount = 0;
            $timeActive = '';
            $collection = $this->actModelFactory->create()->getCollection()
                ->addFieldToFilter('path','marketplace/act/key');
            if($collection->getSize() > 0) {
                foreach ($collection as $item)
                {
                    if($item->getData('extension_code') == md5($mainDomain.$item->getData('act_key'))) {
                        $isVaild = true;
                        $domainCount = $item->getData('domain_count');
                        $timeActive = $item->getData('created_time');
                        break;
                    }
                }
            }
            if($isVaild) {
                $html = base64_decode('PGhyIHdpZHRoPSIyODAiPjxiPltEb21haW5Db3VudF0gRG9tYWluIExpY2Vuc2U8L2I+PGJyPjxiPkFjdGl2ZSBEYXRlOiA8L2I+W0NyZWF0ZWRUaW1lXTxicj48YSBocmVmPSJodHRwczovL3d3dy5tYWdldG9wLmNvbS9tYWdlbnRvLTItbXVsdGktdmVuZG9yLW1hcmtldHBsYWNlLWV4dGVuc2lvbi5odG1sIiB0YXJnZXQ9Il9ibGFuayI+VmlldyBQcmljZTwvYT48YnI+');
                $html = str_replace(array('[DomainCount]','[CreatedTime]'),array($domainCount,$timeActive),$html);
            }
        }
        return $html;
    }
}