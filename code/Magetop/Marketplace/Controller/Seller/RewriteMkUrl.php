<?php
/**
 * Magetop Development
 *
 * @category  Magetop Extension
 * @package   Magetop_Marketplace
 * @author    Magetop
 * @copyright Copyright (c) Magetop (https://www.magetop.com)
 * @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Magetop\Marketplace\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;
use Magetop\Marketplace\Helper\Collection as HelperData;
use Magento\Customer\Model\Url as CustomerUrl;
use Magetop\Marketplace\Model\SellersFactory;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * Magetop Marketplace Account RewriteMkUrl Controller.
 */
class RewriteMkUrl extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var HelperCollection
     */
    protected $helper;

    /**
     * @var CustomerUrl
     */
    protected $customerUrl;

    /**
     * @var SellersFactory
     */
    protected $sellersModel;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @param Context           $context
     * @param Session           $customerSession
     * @param FormKeyValidator  $formKeyValidator
     * @param HelperCollection  $helper
     * @param CustomerUrl       $customerUrl
     * @param SellersFactory     $sellersModel
     * @param UrlRewriteFactory $urlRewriteFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Filesystem $filesystem,
        HelperData $helper,
        CustomerUrl $customerUrl,
        SellersFactory $sellersModel,
        UrlRewriteFactory $urlRewriteFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->helper = $helper;
        $this->customerUrl = $customerUrl;
        $this->sellersModel = $sellersModel;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Seller's Custom URL Post action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/account',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $fields = $this->getRequest()->getParams();
                $sellerId = $this->_getSession()->getCustomerId();
                $collection = $this->sellersModel->create()
                ->getCollection()
                ->addFieldToFilter('user_id', $sellerId);
                foreach ($collection as $value) {
                    $profileurl = $value->getStoreurl();
                }

                $getCurrentStoreId = $this->helper->getCurrentStoreId();

                if ($fields['shop_request_url']) {
                    $sourceUrl = 'marketplace/seller/view/vendor/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $urlId = 0;
                    $profileRequestUrl = '';
                    $urlCollectionData = $this->urlRewriteFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('target_path', $sourceUrl)
                    ->addFieldToFilter('store_id', $getCurrentStoreId);
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $profileRequestUrl = $value->getRequestPath();
                    }
                    if ($profileRequestUrl != $fields['shop_request_url']) {
                        $idPath = rand(1, 100000);
                        $this->urlRewriteFactory->create()
                        ->load($urlId)
                        ->setStoreId($getCurrentStoreId)
                        ->setIsSystem(0)
                        ->setIdPath($idPath)
                        ->setTargetPath($sourceUrl)
                        ->setRequestPath($fields['shop_request_url'])
                        ->save();
                    }
                }
                // clear cache
                $this->helper->clearCache();
                $this->messageManager->addSuccess(__('The URL Rewrite has been saved.'));

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/account',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->helper->logDataInLogger(
                    "Controller_Seller_RewriteMkUrl execute : ".$e->getMessage()
                );
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/account',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/account',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
