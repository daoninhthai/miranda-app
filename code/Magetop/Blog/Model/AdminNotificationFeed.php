<?php
/**
 * Copyright © 2015  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Blog admin notification feed model
 */
class AdminNotificationFeed extends \Magento\AdminNotification\Model\Feed
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param InboxFactory $inboxFactory
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Module\Manager $moduleManager,
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $backendConfig, $inboxFactory, $curlFactory, $deploymentConfig, $productMetadata, $urlBuilder, $resource, $resourceCollection, $data);
        $this->_backendAuthSession  = $backendAuthSession;
        $this->_moduleList = $moduleList;
        $this->_moduleManager = $moduleManager;
    }

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = 'https://www.magetop.com/feed/';
        }

        $urlInfo = parse_url($this->urlBuilder->getBaseUrl());
        $domain = isset($urlInfo['host']) ? $urlInfo['host'] : '';

        $url = $this->_feedUrl . 'domain/' . urlencode($domain);

        $modulesParams = array();
        foreach($this->getMagetopModules() as $key => $module) {
            $key = str_replace('Magetop_', '', $key);
            $modulesParams[] = $key . ',' . $module['setup_version'];
        }

        if (count($modulesParams)) {
            $url .= '/modules/'.base64_encode(implode(';', $modulesParams));
        }

        return $url;
    }

    /**
     * Get Magetop Modules Info
     *
     * @return $this
     */
    protected function getMagetopModules()
    {
        $modules = array();
        foreach($this->_moduleList->getAll() as $moduleName => $module) {
            if ( strpos($moduleName, 'Magetop_') !== false && $this->_moduleManager->isEnabled($moduleName) ) {
                $modules[$moduleName] = $module;
            }
        }

        return $modules;
    }

    /**
     * Check feed for modification
     *
     * @return $this
     */
    public function checkUpdate()
    {
        $session = $this->_backendAuthSession;
        $time = time();
        $frequency = $this->getFrequency();
        if (($frequency + $session->getMfNoticeLastUpdate() > $time)
            || ($frequency + $this->getLastUpdate() > $time)
        ) {
            return $this;
        }

        $session->setMfNoticeLastUpdate($time);
        return parent::checkUpdate();
    }

    /**
     * Retrieve Update Frequency
     *
     * @return int
     */
    public function getFrequency()
    {
        return 86400;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load('magetop_admin_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'magetop_admin_notifications_lastcheck');
        return $this;
    }

}
