<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Coupon
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerCoupon\Block\Adminhtml\SellerCoupon\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $_sellercouponCollection;
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
        \Magetop\SellerCoupon\Model\ResourceModel\SellerCoupon\Collection $sellercouponCollection,
        \Magento\Customer\Model\CustomerFactory $customerFactory,   
        \Magento\Framework\App\ResourceConnection $resource, 
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_sellercouponCollection = $sellercouponCollection;
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
        $isElementDisabled = !$this->_isAllowedAction('Magetop_SellerCoupon::sellercoupon');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('sellercoupon_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Coupon Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
             
        $tableSellers = $this->_resource->getTableName('multivendor_user');
		$customerModel = $this->_customerFactory->create();
		$sellers = $customerModel->getCollection();
		$sellers->getSelect()->joinLeft(array('table_sellers'=>$tableSellers),'e.entity_id = table_sellers.user_id',array('*'))->where('table_sellers.userstatus = 1');
        $seller = array();
        foreach($sellers as $data){
            $seller[$data->getUserId()] = $data->getStoretitle();
        }
        $fieldset->addField(
            'seller_id',
            'select',
            [
                'label' => __('Seller'),
                'title' => __('Seller'),
                'name' => 'seller_id',
                'required' => true,
                'options' => $seller,
                'disabled' => $isElementDisabled
            ]
        );	
        $fieldset->addField(
            'seller_coupon_code',
            'text',
            [
                'name' => 'seller_coupon_code',
                'label' => __('Coupon Code'),
                'title' => __('Coupon Code'),
                'required' => true,
                'disabled' => $model->getId()?true:false
            ]
        );
        $fieldset->addField(
            'seller_coupon_type',
            'select',
            [
                'name' => 'seller_coupon_type',
                'label' => __('Coupon Type'),
                'title' => __('Coupon Type'),
                'required' => true,
                'options' => [
                    1 => __('Price'),
                    2 => __('Percent')
                ],
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'seller_coupon_price',
            'text',
            [
                'name' => 'seller_coupon_price',
                'label' => __('Coupon Value'),
                'title' => __('Coupon Value'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'created_at',
            'date',
            [
                'name' => 'created_at',
                'label' => __('From Date'),
                'title' => __('From Date'),
                'required' => true,
                //'readonly' => true,
                'singleClick'=> true,
                'date_format'=>'yyyy-MM-dd',
                'time'=>false,
                'disabled' => $isElementDisabled
            ]
        );	
        $fieldset->addField(
            'expire_date',
            'date',
            [
                'name' => 'expire_date',
                'label' => __('Expire Date'),
                'title' => __('Expire Date'),
                'required' => true,
                //'readonly' => true,
                'singleClick'=> true,
                'date_format'=>'yyyy-MM-dd',
                'time'=>false,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );	
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }                       
        $this->_eventManager->dispatch('magetop_sellercoupon_sellercoupon_edit_tab_main_prepare_form', ['form' => $form]);

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
        return __('Coupon Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Coupon Information');
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