<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Controller\Adminhtml\Menu;

class Save extends \Magetop\Menupro\Controller\Adminhtml\Menu
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
	/**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        //echo "<pre>";print_r($data);exit;
		$imagedata = array();
        if (!empty($_FILES['image']['name'])) {
			 /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
				$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $mediaFolder = 'magetop/menupro/';
			try {
                $ext = substr($_FILES['image']['name'], strrpos($_FILES['image']['name'], '.') + 1);
                $fname = 'Image-' . time() . '.' . $ext;
                $uploader = $this->_objectManager->get('Magento\MediaStorage\Model\File\UploaderFactory')->create(['fileId' => 'image']);
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png')); // or pdf or anything               
				$uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $path = $mediaDirectory->getAbsolutePath($mediaFolder);
                $uploader->save($path, $fname);
                $imagedata['image'] = 'magetop/menupro/'.$fname;
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['menu_id' => $this->getRequest()->getParam('menu_id')]);
            }
        }
        if ($data) {
			if (!empty($imagedata['image'])) {
                $data['image'] = $imagedata['image'];
            }else{
				if (isset($data['image']['delete']) && $data['image']['delete'] == 1) {
                    $data['image'] = '';
                } else {
                    unset($data['image']);
                }
			}
            $id = $this->getRequest()->getParam('menu_id');
			if($id){
				$model = $this->_objectManager->create('Magetop\Menupro\Model\Menu')->load($id);
				if (!$model->getId()) {
					$this->messageManager->addError(__('This Menu no longer exists.'));
					return $resultRedirect->setPath('*/*/');
				}
				$model->setData($data)->setId($id);
			}else{
				$model = $this->_objectManager->create('Magetop\Menupro\Model\Menu');
				// init model and set data
				$model->setData($data)->setId(NULL);
			}			            
            // try to save it
            try {
				
				$storeids="";
				if(isset($_POST['storeids'])){
					foreach($_POST['storeids'] as $value){
						$storeids.=$value.",";
						//If select all store view (value=0)
						if($value=="0"){
							//Load all store view id
							$storeids="0".",";
							$allStores = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStores();
							foreach ($allStores as $_eachStoreId => $val) 
							{
								$_storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore($_eachStoreId)->getId();
								$storeids.=$_storeId.",";
							}
						}
					}
					$model->setStoreids($storeids);
				}else{
					$storeids="";
					$allStores = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStores();
					foreach ($allStores as $_eachStoreId => $val)
					{
						$_storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore($_eachStoreId)->getId();
						$storeids.=$_storeId.",";
					}
					
					$model->setStoreids("0,".$storeids);
				}
				if(isset($_POST['autosub'])){
					$model->setAutosub($_POST['autosub']);
				}else{
					$model->setAutosub(2);
				}
				
				if(isset($_POST['use_category_title'])){
					$model->setUseCategoryTitle($_POST['use_category_title']);
				}else{
					$model->setUseCategoryTitle(2);
				}
                // save the data
                $model->save();
                // display success message
                $this->messageManager->addSuccess(__('You saved the Menu.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['menu_id' => $model->getId()]);
                }
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                return $resultRedirect->setPath('*/*/edit', ['menu_id' => $this->getRequest()->getParam('menu_id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
	/**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetop_Menupro::add_new_item');
    } 
}
