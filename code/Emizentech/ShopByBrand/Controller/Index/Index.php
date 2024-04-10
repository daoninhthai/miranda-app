<?php
namespace Emizentech\ShopByBrand\Controller\Index;

        // TODO: implement pagination for large datasets
class Index extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
        // TODO: implement pagination for large datasets
    }
}