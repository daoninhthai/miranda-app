<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Locator
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStoreLocator\Controller\Index;
use Magento\Framework\Controller\ResultFactory;
class Search extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
	/**
     * @var \Magento\Customer\Model\Session
     */
	protected $_customerSession;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $data['map_search'] = $this->_objectManager->create('\Magento\Framework\View\LayoutInterface')
            ->createBlock(
                "Magetop\SellerStoreLocator\Block\Search",
                "block_name",
                ['data' => [
                        'type' => $this->getRequest()->getPost('type')
                    ]
                ]
            )
            ->setTemplate('Magetop_SellerStoreLocator::sellerstorelocator/search.phtml')
            ->toHtml();
        $data['slidebar_left'] = $this->_objectManager->create('\Magento\Framework\View\LayoutInterface')
            ->createBlock(
                "Magetop\SellerStoreLocator\Block\Search",
                "block_name",
                ['data' => [
                        'type' => $this->getRequest()->getPost('type')
                    ]
                ]
            )
            ->setTemplate('Magetop_SellerStoreLocator::sellerstorelocator/slidebar.phtml')
            ->toHtml();
        
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);
        return $resultJson;
    } 
}