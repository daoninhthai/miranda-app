<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Assign_Product
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magetop\SellerAssignProduct\Block\Adminhtml;

class SellerAssignProduct extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'sellerassignproduct/grid.phtml';
    protected $_customerCollectionFactory;
    protected $_sellersCollectionFactory;
    protected $_objectmanager;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magetop\Marketplace\Model\SellersFactory $sellersFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_sellersCollectionFactory = $sellersFactory;	
        $this->_objectmanager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and Create sellerassignproduct , edit/add sellerassignproduct row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\SellerAssignProduct
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'sellerassignproduct',
            $this->getLayout()->createBlock('Magetop\SellerAssignProduct\Block\Adminhtml\SellerAssignProduct\Grid', 'sellerassignproduct.view.grid')
        );
        return parent::_prepareLayout();
    }
                
    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'sellerassignproduct/*/new'
        );
    }
 
    /**
     * Render sellerassignproduct
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('sellerassignproduct');
    }
    
    public function getInforSeller()
    {
        return $this->_customerCollectionFactory->create()->load($this->getRequest()->getParam('sellerid'));
    }
    
    public function getInforPaymentSeller()
    {
        $data = $this->_sellersCollectionFactory->create()->getCollection()->addFieldToFilter('user_id',$this->getRequest()->getParam('sellerid'))->getFirstItem();
        return $data['paymentsource'];
    }
    
    public function getTotalAmounPay()
    {
        $data = $this->_objectmanager->create('Magetop\Marketplace\Model\Partner')->getCollection()->addFieldToFilter('sellerid',$this->getRequest()->getParam('sellerid'))->getFirstItem();
        return $data['amountremain'];
    }
    
    public function getTotalAmounSeller()
    {
        $data = $this->_objectmanager->create('Magetop\Marketplace\Model\Partner')->getCollection()->addFieldToFilter('sellerid',$this->getRequest()->getParam('sellerid'))->getFirstItem();
        return $this->_objectmanager->create('Magento\Framework\Pricing\Helper\Data')->currency($data['amountremain']);
    }
    
    public function getTotalAmounReceivedSeller()
    {
        $data = $this->_objectmanager->create('Magetop\Marketplace\Model\Partner')->getCollection()->addFieldToFilter('sellerid',$this->getRequest()->getParam('sellerid'))->getFirstItem();
        return $this->_objectmanager->create('Magento\Framework\Pricing\Helper\Data')->currency($data['amountreceived']);
    }
    
    public function getBackUrl(){
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/sellerassignproduct/index', array('sellerid'=>$this->getRequest()->getParam('sellerid')));
        return $url;
    }
    
    public function getPayAction(){
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/pay/index', array('sellerid'=>$this->getRequest()->getParam('sellerid')));
        return $url;
    }
}