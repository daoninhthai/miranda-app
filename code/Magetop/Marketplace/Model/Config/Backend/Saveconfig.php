<?php
/**
 * @author      Magetop Developer (Uoc)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Model\Config\Backend;

class Saveconfig extends \Magento\Framework\App\Config\Value
{
    protected $marketplaceHelper;
    protected $actModelFactory;
    protected $urlInterface;
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magetop\Marketplace\Helper\Data $marketplaceHelper,
        \Magetop\Marketplace\Model\ActFactory $actModelFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        array $data = []
    ) {
        $this->marketplaceHelper = $marketplaceHelper;
        $this->actModelFactory = $actModelFactory;
        $this->urlInterface = $urlInterface;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }
    public function afterSave()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $curlClient = $objectManager->get('Magento\Framework\HTTP\Client\Curl');
        $license = $this->actModelFactory->create();
        $path = $this->getPath();
        $license_key = trim($this->getValue());
        $domain = $_SERVER['SERVER_NAME'];
        $mainDomain = $this->marketplaceHelper->getYourDomain($domain);
        $data = array(
            'license_key' => $license_key,
            'domain' => $domain,
            'main_domain'=>$mainDomain
        );
        $url = "aHR0cHM6Ly9rZXkubWFnZXRvcC5jb20vbGljZW5zZS9jaGVja2xpY2Vuc2UvaW5kZXg=";
        $datasend = urlencode(serialize($data));
        $getlink = base64_decode($url)."?str=".$datasend;
        $curlClient->get($getlink);
        $result = $curlClient->getBody();
        $result_data = json_decode($result, true);
        $status = isset($result_data['status']) ? $result_data['status'] : '';
        $extension_code = isset($result_data['extension_code']) ? $result_data['extension_code'] : 'NO DATA';
        $domain_count = isset($result_data['domain_count']) ? $result_data['domain_count'] : '';
        $current_time = date('Y-m-d H:i:s');
        if ($status == "true")
        {
           $licenseId = $this->getCurLicense($domain);
            $license->setDomains($domain);
            $license->setDomainCount($domain_count);
            $license->setExtensionCode($extension_code);
            $license->setActKey($license_key);
            $license->setPath($path);
            $license->setCreatedTime($current_time);
            $license->setIsValid(1);
            if($licenseId > 0) {
                $license->setId($licenseId);
            }
            $license->save();
        }
        else {
            $licenseId = $this->getCurLicense($domain);
            if($licenseId > 0) {
                $license->setActKey($license_key);
                $license->setIsValid(0);
                $license->setId($licenseId);
                $license->save();
            }
        }
        $this->_cacheManager->clean();
        return parent::afterSave();
    }
    function getCurLicense($domain)
    {
        $collection = $this->actModelFactory->create()->getCollection()
            ->addFieldToFilter('domain_list', array('finset'=> $domain));
        return $collection && $collection->getSize() > 0 ? (int)$collection->getFirstItem()->getId() : 0;
    }

}
