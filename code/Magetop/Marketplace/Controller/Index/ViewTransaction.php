<?php
/**
* @author      Magetop Developer (Kien)
* @package     Magento Multi Vendor Marketplace
* @copyright   Copyright (c) Magetop (https://www.magetop.com)
* @terms       https://www.magetop.com/terms
* @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
**/
namespace Magetop\Marketplace\Controller\Index;
 
use Magento\Framework\App\Action\Context;

class ViewTransaction extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }
 
    public function execute()
    {
        $html = $this->_objectManager->create('\Magento\Framework\View\LayoutInterface')
            ->createBlock('Magetop\Marketplace\Block\Transactionlist')
            ->setTranId($this->getRequest()->getParam('tran_id'))
            ->setTemplate('seller/view_pay.phtml')
            ->toHtml();
        $this->getResponse()->appendBody($html); 
    }
}
 