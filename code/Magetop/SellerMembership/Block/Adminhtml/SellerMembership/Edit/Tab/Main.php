<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Block\Adminhtml\SellerMembership\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements
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

    protected $_sellermembershipCollection;
    protected $_membershipCollection;
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
        \Magetop\SellerMembership\Model\ResourceModel\SellerMembership\Collection $sellermembershipCollection,
        \Magetop\SellerMembership\Model\ResourceModel\Membership\Collection $membershipCollection,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Customer\Model\CustomerFactory $customerFactory,   
        \Magento\Framework\App\ResourceConnection $resource,  
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_sellermembershipCollection = $sellermembershipCollection;
        $this->_membershipCollection = $membershipCollection;
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
        $isElementDisabled = !$this->_isAllowedAction('Magetop_SellerMembership::sellermembership');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('sellermembership_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Membership Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        	
        $ids = array();
        $seller_membership = $this->_sellermembershipCollection;
        foreach($seller_membership as $lt){
            $ids[] = $lt->getSellerId();
        }      
        $tableSellers = $this->_resource->getTableName('multivendor_user');
		$customerModel = $this->_customerFactory->create();
		$sellers = $customerModel->getCollection();
		$sellers->getSelect()->joinLeft(array('table_sellers'=>$tableSellers),'e.entity_id = table_sellers.user_id',array('*'))->where('table_sellers.userstatus = 1');
        $seller = array();
        foreach($sellers as $data){
            if(!in_array($data->getUserId(),$ids)){
                $seller[$data->getUserId()] = $data->getStoretitle();
            }
            if ($model->getId()) {                
                if($data->getUserId() == $model->getSellerId()){
                    $seller = array();
                    $seller[$data->getUserId()] = $data->getStoretitle();
                }
            }
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
        $membership_type = array();
        $membership = $this->_membershipCollection;
        foreach($membership as $mb){
            $membership_type[$mb->getId()] = $mb->getTitle();
        }                
        $fieldset->addField(
            'membership_id',
            'select',
            [
                'name' => 'membership_id',
                'label' => __('Membership Type'),
                'title' => __('Membership Type'),
                'required' => true,
                'options' => $membership_type,                
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'created_at',
            'date',
            [
                'name' => 'created_at',
                'label' => __('Created At'),
                'title' => __('Created At'),
                'required' => true,
                'readonly' => true,
                'singleClick'=> true,
                'date_format'=>'yyyy-MM-dd',
                'time'=>false,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'paid_status',
            'select',
            [
                'label' => __('Paid Status'),
                'title' => __('Paid Status'),
                'name' => 'paid_status',
                'required' => true,
                'options' => [
                    '0' => 'Not Paid',                
                    '1' => 'Paid'
                ],
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
        $this->_eventManager->dispatch('magetop_sellermembership_sellermembership_edit_tab_main_prepare_form', ['form' => $form]);

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
        return __('Membership Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Membership Information');
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