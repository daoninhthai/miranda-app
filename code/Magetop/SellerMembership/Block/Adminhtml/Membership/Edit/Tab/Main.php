<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Block\Adminhtml\Membership\Edit\Tab;

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

    protected $_membershipCollection;
    protected $_customerFactory;    
    protected $_resource;   
    protected $_product;

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
        \Magetop\SellerMembership\Model\ResourceModel\Membership\Collection $membershipCollection,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Customer\Model\CustomerFactory $customerFactory,   
        \Magento\Framework\App\ResourceConnection $resource,  
        \Magento\Catalog\Model\Product $product,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_membershipCollection = $membershipCollection;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_customerFactory = $customerFactory;  
        $this->_resource = $resource;  
        $this->_product = $product;
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
        $isElementDisabled = !$this->_isAllowedAction('Magetop_SellerMembership::membership');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('membership_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Membership Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        
        /*$ids = array();
        $membership = $this->_membershipCollection;
        foreach($membership as $ms){
            $ids[] = $ms->getProductId();
        }
        
        $collection = $this->_product->getCollection()->addAttributeToFilter('type_id','membership');
        $collection->addAttributeToSelect(array('*'));
        $member_ship = array();
        foreach($collection as $data){
            if(!in_array($data->getEntityId(),$ids)){
                $member_ship[$data->getEntityId()] = $data->getName();
            }
            if ($model->getId()) {                
                if($data->getEntityId() == $model->getProductId()){
                    $member_ship[$data->getEntityId()] = $data->getName();
                }
            }
        }*/
        $fieldset->addField('product_id', 'hidden', ['name' => 'product_id']);
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );	   
        $fieldset->addField(
            'fee',
            'text',
            [
                'name' => 'fee',
                'label' => __('Fee'),
                'title' => __('Fee'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'time',
            'text',
            [
                'name' => 'time',
                'label' => __('Time(Days)'),
                'title' => __('Time(Days)'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'number',
            'text',
            [
                'name' => 'number',
                'label' => __('Number of products per month'),
                'title' => __('Number of products per month'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'class' => 'validate-number',
                'note' => 'Note : Enter 0 or blank for unlimited create product each month.'
            ]
        );
        $fieldset->addField(
            'commission',
            'text',
            [
                'name' => 'commission',
                'label' => __('Commission by percent'),
                'title' => __('Commission by percent'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'product_image',
            'image',
            [
                'name' => 'product_image',
                'label' => __('Image'),
                'title' => __('Image'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'note' => 'Allow image type: jpg, jpeg, gif, png'
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
        }else{
            $_product = $this->_product->load($model->getProductId());
            if($_product->getImage()){
                $model->setProductImage('catalog/product' . $_product->getImage());
            }
        }                       
        $this->_eventManager->dispatch('magetop_sellermembership_membership_edit_tab_main_prepare_form', ['form' => $form]);

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