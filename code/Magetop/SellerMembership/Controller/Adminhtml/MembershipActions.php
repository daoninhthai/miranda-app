<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Controller\Adminhtml;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magetop\Marketplace\Controller\Product\Action\Action\Context;
/**
 * Abstract admin controller
 */
abstract class MembershipActions extends \Magento\Backend\App\Action
{
    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey;

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey;

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass;

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu;

    /**
     * Store config section key
     * @var string
     */
    protected $_configSection;

    /**
     * Request id key
     * @var string
     */
    protected $_idKey = 'id';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';

    /**
     * Save request params key
     * @var string
     */
    protected $_paramsHolder;

    /**
     * Model Object
     * @var \Magento\Framework\Model\AbstractModel
     */
	protected $_model;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Action execute
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $_preparedActions = array('index', 'grid', 'new', 'edit', 'save', 'delete', 'config', 'massStatus');
        $_action = $this->getRequest()->getActionName();
        if (in_array($_action, $_preparedActions)) {
            $method = '_'.$_action.'Action';

            $this->_beforeAction();
            $this->$method();
            $this->_afterAction();
        }
    }

    /**
     * Index action
     * @return void
     */
    protected function _indexAction()
    {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);
        $title = __('Manage '.$this->_getModel(false)->getOwnTitle(true));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }

    /**
     * Grid action
     * @return void
     */
    protected function _gridAction()
    { 
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * New action
     * @return void
     */
    protected function _newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit action
     * @return void
     */
    public function _editAction()
    {
        $model = $this->_getModel();

        $this->_getRegistry()->register('current_model', $model);

        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);

        $title = $model->getOwnTitle();

        if ($model->getId()) {
            $breadcrumbTitle = __('Edit '.$title);
            $breadcrumbLabel = $breadcrumbTitle;
        } else {
            $breadcrumbTitle = __('New '.$title);
            $breadcrumbLabel = __('Create '.$title);
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__($title));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $this->_getModelName($model) : __('New '.$title)
        );

        $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

        // restore data
        $values = $this->_getSession()->getData($this->_formSessionKey, true);
        if ($this->_paramsHolder) {
            $values = isset($values[$this->_paramsHolder]) ? $values[$this->_paramsHolder] : null;
        }

        if ($values) {
            $model->addData($values);
        }

        $this->_view->renderLayout();
    }

    /**
     * Retrieve model name
     * @param  boolean $plural
     * @return string
     */
    protected function _getModelName(\Magento\Framework\Model\AbstractModel $model)
    {
        return $model->getName() ?: $model->getTitle();
    }

    /**
     * Save action
     * @return void
     */
    public function _saveAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/*'));
        }
        $model = $this->_getModel();

        try {
            $params = $this->_paramsHolder ? $request->getParam($this->_paramsHolder) : $request->getParams();
			
			if(!@$params['id']){
                $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                $params['created_at'] = date('Y-m-d H:i:s',$time->scopeTimeStamp());
			}
            
            //upload image
            try {
                $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'product_image']
                );
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
                $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                    ->getDirectoryRead(DirectoryList::MEDIA);
                $config = $this->_objectManager->get('Magento\Catalog\Model\Product\Media\Config');
                $result = $uploader->save($mediaDirectory->getAbsolutePath($config->getBaseTmpMediaPath()));
    
                $this->_eventManager->dispatch(
                    'catalog_product_gallery_upload_image_after',
                    ['result' => $result, 'action' => $this]
                );
    
                unset($result['tmp_name']);
                unset($result['path']);
    
                $result['url'] = $this->_objectManager->get('Magento\Catalog\Model\Product\Media\Config')
                    ->getTmpMediaUrl($result['file']);
                $result['file'] = $result['file'] . '.tmp';
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            }
            //end upload image
            //remove image
            if(@$params['product_image']['delete']){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $entryResolver = $objectManager->get('Magento\Catalog\Model\Product\Gallery\EntryResolver');
                $product = $objectManager->get('Magento\Catalog\Model\Product')->load($params['product_id']);
                $entryId = $entryResolver->getEntryIdByFilePath($product, $product->getImage());
                $this->_objectManager->get('Magento\Catalog\Model\Product\Gallery\GalleryManagement')->remove($product->getSku(), $entryId);
            }
            //end remove image
            
            $_productOld = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($params['product_id']);
            if($_productOld->getId()){
                $_product = $_productOld;
                $_product->setName($params['title']);
                $_product->setSku($params['title']);
                $_product->setPrice($params['fee']);
                $_product->setStatus($params['status']);
                if(@$result['file']){
                    $_product->setImage(
                        $result['file']
                    )->setSmallImage(
                        $result['file']
                    )->setThumbnail(
                        $result['file']
                    );
                    $media_gallery = array(
                        "images"=>array(
                            "ulss0r1gali"=>array(
                                "position"=>"1",
                                "media_type"=>"image",
                                "video_provider"=>"",
                                "file"=>$result['file'],
                                "value_id"=>"",
                                "label"=>"",
                                "disabled"=>"0",
                                "removed"=>"",
                                "video_url"=>"",
                                "video_title"=>"",
                                "video_description"=>"",
                                "video_metadata"=>"",
                                "role"=>""
                            )
                        )
                    );
                    $_product->setMediaGallery($media_gallery);
                }
                $_product->save();
			}else{
                $_product = $this->_objectManager->create('Magento\Catalog\Model\Product');
                $_product->setAttributeSetId(4);
                $_product->setTypeId('membership');
                $_product->setName($params['title']);
                $_product->setSku($params['title']);
                $_product->setPrice($params['fee']);
                $_product->setStockData(array(
                    'use_config_manage_stock' => 0, 
                    'manage_stock' => 1, 
                    'min_sale_qty' => 1, 
                    'max_sale_qty' => 2, 
                    'is_in_stock' => 1, 
                    'qty' => 999 
                    )
                );
                $_product->setStatus($params['status']);
                $_product->setVisibility(4);
                $_product->setWebsiteIds(array(1));
                if(@$result['file']){
                    $_product->setImage(
                        $result['file']
                    )->setSmallImage(
                        $result['file']
                    )->setThumbnail(
                        $result['file']
                    );
                    $media_gallery = array(
                        "images"=>array(
                            "ulss0r1gali"=>array(
                                "position"=>"1",
                                "media_type"=>"image",
                                "video_provider"=>"",
                                "file"=>$result['file'],
                                "value_id"=>"",
                                "label"=>"",
                                "disabled"=>"0",
                                "removed"=>"",
                                "video_url"=>"",
                                "video_title"=>"",
                                "video_description"=>"",
                                "video_metadata"=>"",
                                "role"=>""
                            )
                        )
                    );
                    $_product->setMediaGallery($media_gallery);
                }
                $_product->save();
                $params['product_id'] = $_product->getId();
			}
			
            $model->addData($params);

            $this->_beforeSave($model, $request);
            $model->save();
            $this->_afterSave($model, $request);

            $this->messageManager->addSuccess(__($model->getOwnTitle().' has been saved.'));
            $this->_setFormData(false);

            if ($request->getParam('back')) {
                $this->_redirect('*/*/edit', [$this->_idKey => $model->getId()]);
            } else {
                $this->_redirect('*/*');
            }
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
            $this->_setFormData();
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving this '.strtolower($model->getOwnTitle()).'.').' '.$e->getMessage());
            $this->_setFormData();
        }

        $this->_redirect('*/*/edit', [$this->_idKey => $model->getId()]);
    }

    /**
     * Before model Save action
     * @return void
     */
    protected function _beforeSave($model, $request) {}

    /**
     * After model action
     * @return void
     */
    protected function _afterSave($model, $request) {}

    /**
     * Before action
     * @return void
     */
    protected function _beforeAction() {}

    /**
     * After action
     * @return void
     */
    protected function _afterAction() {}

    /**
     * Delete action
     * @return void
     */
    protected function _deleteAction()
    {
        $ids = $this->getRequest()->getParam($this->_idKey);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $error = false;
        try {
            foreach($ids as $id) {
                $model = $this->_objectManager->create($this->_modelClass)->load($id);
                $this->_objectManager->create('Magento\Catalog\Model\Product')->load($model->getProductId())->delete();
                $model->delete();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException($e, __('We can\'t delete '.strtolower($this->_getModel(false)->getOwnTitle()).' right now. '.$e->getMessage()));
        }

        if (!$error) {
            $this->messageManager->addSuccess(
                __($this->_getModel(false)->getOwnTitle(count($ids) > 1).' have been deleted.')
            );
        }

        $this->_redirect('*/*');
    }

    /**
     * Change status action
     * @return void
     */
    protected function _massStatusAction()
    {
        $ids = $this->getRequest()->getParam($this->_idKey);
        
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $model = $this->_getModel(false);

        $error = false;

        try {

            $status = $this->getRequest()->getParam('status');
            $statusFieldName = $this->_statusField;

            if (is_null($status)) {
                throw new Exception(__('Parameter "Status" missing in request data.'));
            }

            if (is_null($statusFieldName)) {
                throw new Exception(__('Status Field Name is not specified.'));
            }

            foreach($ids as $id) {
                $this->_objectManager->create($this->_modelClass)
                    ->load($id)
                    ->setData($this->_statusField, $status)
                    ->save();
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException($e, __('We can\'t change status of '.strtolower($model->getOwnTitle()).' right now. '.$e->getMessage()));
        }

        if (!$error) {
            $this->messageManager->addSuccess(
                __($model->getOwnTitle(count($ids) > 1).' status have been changed.')
            );
        }

        $this->_redirect('*/*');

    }

    /**
     * Go to config section action
     * @return void
     */
    protected function _configAction()
    {
        $this->_redirect('admin/system_config/edit', ['section' => $this->_configSection()]);
    }

    /**
     * Set form data
     * @return $this
     */
    protected function _setFormData($data = null)
    {
        $this->_getSession()->setData($this->_formSessionKey,
            is_null($data) ? $this->getRequest()->getParams() : $data);

        return $this;
    }

    /**
     * Get core registry
     * @return void
     */
    protected function _getRegistry()
    {
        if (is_null($this->_coreRegistry)) {
            $this->_coreRegistry = $this->_objectManager->get('\Magento\Framework\Registry');
        }
        return $this->_coreRegistry;
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->_allowedKey);
    }

    /**
     * Retrieve model object
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function _getModel($load = true)
    {
    	if (is_null($this->_model)) {
    		$this->_model = $this->_objectManager->create($this->_modelClass);

            $id = (int)$this->getRequest()->getParam($this->_idKey);
		    if ($id && $load) {
		        $this->_model->load($id);
		    }
    	}
    	return $this->_model;
    }
}