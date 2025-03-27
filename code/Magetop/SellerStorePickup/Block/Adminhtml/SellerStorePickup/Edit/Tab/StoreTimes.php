<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Block\Adminhtml\SellerStorePickup\Edit\Tab;

class StoreTimes extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $_sellerstorepickupCollection;
    protected $_customerFactory;    
    protected $_resource;   

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magetop\SellerStorePickup\Model\ResourceModel\SellerStorePickup\Collection $sellerstorepickupCollection,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Customer\Model\CustomerFactory $customerFactory,   
        \Magento\Framework\App\ResourceConnection $resource,  
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_sellerstorepickupCollection = $sellerstorepickupCollection;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_customerFactory = $customerFactory;  
        $this->_resource = $resource;  
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magefan\Blog\Model\Category */
        $model = $this->_coreRegistry->registry('current_model');

        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = !$this->_isAllowedAction('Magetop_SellerStorePickup::sellerstorepickup');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('sellerstorepickup_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Store Times (In 24 Hour Time Format)')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        
        $fieldset->addField(
            'store_time', 
            'text', 
            [
                'name' => 'store_time',
                'label' => __('Store Time'),
                'title' => __('Store Time'),
                'required' => true,
                'disabled' => $isElementDisabled                 
            ]
        )->setRenderer($this->_rendererFieldset->setTemplate('Magetop_SellerStorePickup::store_times.phtml'));   
        
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }                       
        $this->_eventManager->dispatch('magetop_sellerstorepickup_sellerstorepickup_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Store Times (In 24 Hour Time Format)');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Store Times (In 24 Hour Time Format)');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}