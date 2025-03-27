<?php
/**
 * Magehq
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the magehq.com license that is
 * available through the world-wide-web at this URL:
 * https://magehq.com/license.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   magehq
 * @package    Magehq_BuyNow
 * @copyright  Copyright (c) 2022 magehq (https://magehq.com/)
 * @license    https://magehq.com/license.html
 */

namespace Magehq\BuyNow\Controller\Ajax;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $json;
    protected $resultJsonFactory;
 
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magehq\BuyNow\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->helper = $helper;
        $this->json = $json;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $response = $this->getRequest()->getParams();
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        
        if(isset($response['currentUrl'])) {
            $currentUrl = $response['currentUrl'];
            return  $resultJson->setData(['success' =>$this->helper->restoreQuote($currentUrl)]);
        } else {
            return false;
        }
    }
}