<?php
namespace Magetop\SellerDHLShipping\Controller\Shipping;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;

class Index extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;
    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $_customerMapper;
    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerDataFactory;
    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param CustomerRepositoryInterface $customerRepository,
     * @param CustomerInterfaceFactory $customerDataFactory,
     * @param DataObjectHelper $dataObjectHelper,
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper,
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->_customerMapper = $customerMapper;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
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
        $loginUrl = $this->_objectManager
                ->get('Magento\Customer\Model\Url')
                ->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Save Seller's DHL configuration Data.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $customerData = $this->getRequest()->getParams();
            $customerId = $this->_getSession()->getCustomerId();
            $savedData = $this->_customerRepository->getById($customerId);
            $customer = $this->_customerDataFactory->create();
            $customerData = array_merge(
                $this->_customerMapper->toFlatArray($savedData),
                $customerData
            );
            $customerData['id'] = $customerId;
            $this->_dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                '\Magento\Customer\Api\Data\CustomerInterface'
            );
            try {
                $this->_customerRepository->save($customer);
                $this->messageManager->addSuccess(__('DHL Shipping details saved successfully.'));
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception('Can not save the records.');
            }

            return $this->resultRedirectFactory->create()
                ->setPath(
                    'sellerdhlshipping/shipping/view',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
        }
        return $this->resultRedirectFactory->create()
                ->setPath(
                    'sellerdhlshipping/shipping/view',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
    }
}