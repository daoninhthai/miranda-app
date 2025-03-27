<?php
/**
 * @author      Magetop
 * @package     Magetop_Api
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Api\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magetop\Api\Helper\Data as DataHelper;
use Magento\Framework\Controller\ResultFactory;

abstract class AbstractController extends Action
{
    /**
     * @var \Magetop\Api\Hepler\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Event\Manager
     */
    protected $_eventManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    public function __construct(
        Context $context,
        EventManager $eventManager,
        AppEmulation $appEmulation,
        DataHelper $dataHelper
    ) {
        $this->_eventManager = $eventManager;
        $this->_appEmulation = $appEmulation;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, User-Agent');
    }

    /**
     * Format response result data
     *
     * @param bool $status
     * @param string $message
     * @param array $data
     *
     * @return Array
     */
    protected function getResponseData($status = true, $message = '', $data = [])
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Create json result
     *
     * @param array $responseData
     *
     * @return \Magento\Framework\Controller\ResultFactory::TYPE_JSON
     */
    protected function returnResultJson($responseData = [])
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }

    /**
     * Get passing parameter
     *
     * @param string $name
     * @param bool $required
     * @param string $errorMessage
     *
     * @return mixed|null
     */
    protected function getParam($name, $required = false, $defaultValue = null, $errorMessage = '')
    {
        $param = $this->getRequest()->getParam($name, $defaultValue);
        if ($required && !$param) {
            throw new \Exception($errorMessage);
        }
        return $param;
    }

    protected function returnResultRedirect($url){
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }
}