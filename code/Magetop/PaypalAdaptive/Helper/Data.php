<?php
namespace Magetop\PaypalAdaptive\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
//use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'payment/paypaladaptive/active';
    const XML_PATH_PAYMENT_MODE = 'payment/paypaladaptive/payment_mode';
    const XML_PATH_PAYMENT_TYPE = 'payment/paypaladaptive/payment_type';
    const XML_PATH_WHO_WILL_PAY_FEE = 'payment/paypaladaptive/who_will_pay_fee';
    const XML_PATH_MERCHANT_PAYPAL_ID = 'payment/paypaladaptive/merchant_paypal_id';
    const XML_PATH_PAYPAL_APPLICATION_ID = 'payment/paypaladaptive/paypal_application_id';
    const XML_PATH_PAYPAL_API_USER_NAME = 'payment/paypaladaptive/paypal_api_user_name';
    const XML_PATH_PAYPAL_API_PASSWORD = 'payment/paypaladaptive/paypal_api_password';
    const XML_PATH_PAYPAL_API_SIGNATURE = 'payment/paypaladaptive/paypal_api_signature';
    
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    //protected $_scopeConfig;
	protected $_objectmanager;
	protected $assetRepo;
	protected $categoryRepository;
	protected $_storeManager;
	protected $_categoryFactory;
	protected $_category;
	protected $_setFactory;
	
    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
		ObjectManagerInterface $objectmanager,
		\Magento\Framework\View\Asset\Repository $assetRepo,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		CategoryRepositoryInterface $categoryRepository,
		\Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
        //ScopeConfigInterface $scopeConfig
    ) 
	{
        parent::__construct($context);
        //$this->_scopeConfig = $scopeConfig;
		$this->assetRepo = $assetRepo;
		$this->_storeManager = $storeManager;
		$this->categoryRepository = $categoryRepository;
		$this->_objectmanager = $objectmanager;
		$this->_setFactory = $setFactory;
		$this->_categoryFactory = $categoryFactory;
        $config = array(
            "environment" => $this->getPaymentMode(),
            "userid" => $this->getPayPalApiUserName(),
            "password" => $this->getPayPalApiPassword(),
            "signature" => $this->getPayPalApiSignature(),
            "appid" => $this->getPayPalApplicationId(), # You can set this when you go live
        );
        $this->config = $config;
    }
    public function isActive($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
    public function getPaymentMode($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_MODE,
            ScopeInterface::SCOPE_STORE
        );
    }
    public function getPaymentType($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }
    public function getWhoWillPayFee($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WHO_WILL_PAY_FEE,
            ScopeInterface::SCOPE_STORE
        );
    }
    public function getMerchantPayPalId($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MERCHANT_PAYPAL_ID,
            ScopeInterface::SCOPE_STORE
        );
    }
    public function getPayPalApplicationId($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYPAL_APPLICATION_ID,
            ScopeInterface::SCOPE_STORE
        );
    }
    public function getPayPalApiUserName($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYPAL_API_USER_NAME,
            ScopeInterface::SCOPE_STORE
        );
    }
    public function getPayPalApiPassword($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYPAL_API_PASSWORD,
            ScopeInterface::SCOPE_STORE
        );
    }
	public function getPayPalApiSignature($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYPAL_API_SIGNATURE,
            ScopeInterface::SCOPE_STORE
        );
    }
    private $urls = array(
        "sandbox" => array(
            "api"      => "https://svcs.sandbox.paypal.com/AdaptivePayments/",
            "redirect" => "https://www.sandbox.paypal.com/webscr",
        ),
        "live" => array(
            "api"      => "https://svcs.paypal.com/AdaptivePayments/",
            "redirect" => "https://www.paypal.com/webscr",
        )
    );
    public function call(array $options = [], string $method = null) {
        $this->prepare($options);
        return $this->_curl($this->api_url($method), $options, $this->headers($this->config));
    }
    public function redirect($response) {
        if(@$response["payKey"]) $redirect_url = sprintf("%s?cmd=_ap-payment&paykey=%s", $this->redirect_url(), $response["payKey"]);
        else $redirect_url = sprintf("%s?cmd=_ap-preapproval&preapprovalkey=%s", $this->redirect_url(), $response["preapprovalKey"]);
        return $redirect_url;
    }
    private function redirect_url() {
        return $this->urls[$this->config["environment"]]["redirect"];
    }
    private function api_url($method) {
        return $this->urls[$this->config["environment"]]["api"].$method;
    }
    private function headers($config){
        $header = array(
            "X-PAYPAL-SECURITY-USERID: ".$config['userid'],
            "X-PAYPAL-SECURITY-PASSWORD: ".$config['password'],
            "X-PAYPAL-SECURITY-SIGNATURE: ".$config['signature'],
            "X-PAYPAL-REQUEST-DATA-FORMAT: JSON",
            "X-PAYPAL-RESPONSE-DATA-FORMAT: JSON",
        );
        
        if(array_key_exists('appid', $config) && !empty($config['appid']))
            $header[] = "X-PAYPAL-APPLICATION-ID: ".$config['appid'];
        else
            $header[] = "X-PAYPAL-APPLICATION-ID: APP-80W284485P519543T";
        
        return $header;
    }
    private function _curl($url, $values, $header) {
        $curl = curl_init($url);
        
        $options = array(
            CURLOPT_HTTPHEADER      => $header,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_POSTFIELDS  => json_encode($values),
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_TIMEOUT        => 10
        );
        
        curl_setopt_array($curl, $options);
        $rep = curl_exec($curl);
        
        $response = json_decode($rep, true);
        curl_close($curl);
        
        return $response;
    }
    private function prepare(&$options) {
        $this->expand_urls($options);
        $this->merge_defaults($options);
    }
    private function expand_urls(&$options) {
        $regex = '#^https?://#i';
        if(array_key_exists('returnUrl', $options) && !preg_match($regex, $options['returnUrl'])) {
            $this->expand_url($options['returnUrl']);
        }
        
        if(array_key_exists('cancelUrl', $options) && !preg_match($regex, $options['cancelUrl'])) {
            $this->expand_url($options['cancelUrl']);
        }
    }
    private function expand_url(&$url) {
        $current_host = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://{$_SERVER["HTTP_HOST"]}";
        if(preg_match("#^/#i", $url)) {
            $url = $current_host.$url;
        }else {
            $directory = dirname($_SERVER['PHP_SELF']);
            $url = $current_host.$directory.$url;
        }
    }
    private function merge_defaults(&$options) {
        $defaults = array(
            'requestEnvelope' => array(
                'errorLanguage' => 'en_US',
            )
        );
        $options = array_merge($defaults, $options);
    }
}