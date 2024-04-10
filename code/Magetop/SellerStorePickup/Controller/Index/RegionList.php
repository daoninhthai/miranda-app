<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Controller\Index;
use Magento\Framework\App\Filesystem\DirectoryList;
class RegionList extends \Magento\Framework\App\Action\Action
{
    /**
    * @var \Magento\Framework\View\Result\PageFactory
    */
    protected $resultPageFactory;
    /**
    * @var \Magento\Directory\Model\CountryFactory
    */
    protected $_countryFactory;
    
    /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
    */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->_countryFactory = $countryFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    /**
    * Default customer account page
    *
    * @return void
    */
    public function execute()
    {
        $countrycode = $this->getRequest()->getParam('country');
        $statecode = $this->getRequest()->getParam('state');
        $state = "<option value=''>--Please Select--</option>";
        if ($countrycode != '') {
            $statearray = $this->_countryFactory->create()->setId(
                $countrycode
            )->getLoadedRegionCollection()->toOptionArray();
            foreach ($statearray as $_state) {
                if($_state['value']){
                    if($_state['value'] == $statecode){
                        $state .= "<option value='".$_state['value']."' selected='selected'>" . $_state['label'] . "</option>";
                    }else{
                        $state .= "<option value='".$_state['value']."'>" . $_state['label'] . "</option>";
                    }
                }
            }
        }
        if($state != "<option value=''>--Please Select--</option>"){
            $state = "<select id='sellerstorepickup_state' name='state' title='State/Province'>".$state."</select>";
        }else{
            $state = "<input id='sellerstorepickup_state' name='state' value='".$statecode."' title='State/Province' class='input-text admin__control-text required-entry _required'' type='text'>";
        }
        $result['htmlconent'] = $state;
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }  
}