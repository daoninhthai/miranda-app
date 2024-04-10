<?php
namespace Magetop\Notification\Controller\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magetop\Marketplace\Helper\Data as SellerHelper;

class Index extends Action
{
    protected $resultPageFactory;
    protected $sellerHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SellerHelper $sellerHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->sellerHelper = $sellerHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->sellerHelper->getSellerId()) {
            return $this->_redirect('marketplace/account/login');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Notifications'));

        return $resultPage;
    }

    /**
     * Sanitizes a string for safe output.
     *
     * @param string $input
     * @return string
     */
    protected function sanitizeOutput($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

}
