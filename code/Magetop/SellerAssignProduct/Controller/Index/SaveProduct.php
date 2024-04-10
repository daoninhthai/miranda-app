<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Assign_Product
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAssignProduct\Controller\Index;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;

class SaveProduct extends \Magento\Framework\App\Action\Action{
    protected $resultPageFactory;
	/**
     * @var \Magento\Customer\Model\Session
     */
	protected $_customerSession;
	public function __construct(
		Context $context,
        \Magento\Customer\Model\Session $customerSession
	){
        $this->_customerSession = $customerSession;
		parent::__construct($context);	
	}
	
	public function execute(){
		$resultRedirect = $this->resultRedirectFactory->create();
		$request = $this->getRequest();
		try{
            $isseller = $this->_objectManager->get('Magetop\Marketplace\Helper\Data')->checkIsSeller();
            if($isseller){
                if($this->_customerSession->isLoggedIn()){
                    $path_images = 'Magetop/SellerAssignProduct/images';
        			$params = $request->getParams();
                    $params['status'] = 1;
                    $params['seller_id'] = $this->_customerSession->getId();	
                    $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                    $params['created_at'] = date('Y-m-d H:i:s',$time->scopeTimeStamp());				
        			if (count($_FILES)) {				
        				foreach($_FILES as $_itemfile=>$_itemfilevalue){
        					if(!$_itemfilevalue['error']){
        						try {
        							$uploader = $this->_objectManager->create(
        								'Magento\MediaStorage\Model\File\Uploader',
        								['fileId' => $_itemfile]
        							);
        							$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        
        							/** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
        							$imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
        
        							$uploader->addValidateCallback('market_'.$_itemfile, $imageAdapter, 'validateUploadFile');
        							$uploader->setAllowRenameFiles(true);
        							$uploader->setFilesDispersion(true);
        
        							/** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        							$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA);
        							$result = $uploader->save($mediaDirectory->getAbsolutePath($path_images));
        							$params[$_itemfile] = $path_images . $result['file'];
        						} catch (\Exception $e) {
        							if ($e->getCode() == 0) {
        								$this->messageManager->addError($this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage()));
        							}
        							if (isset($params[$_itemfile]) && isset($params[$_itemfile]['value'])) {
        								if (isset($params[$_itemfile]['delete'])) {
        									$params[$_itemfile] = '';
        									//$params['delete_image'] = true;
        								} else if (isset($params[$_itemfile]['value'])) {
        									$params[$_itemfile] = $params[$_itemfile]['value'];
        								} else {
        									$params[$_itemfile] = '';
        								}
        							}
        						}					
        					}else{												
        						if (isset($params[$_itemfile]) && isset($params[$_itemfile]['value'])) {
        							if (isset($params[$_itemfile]['delete'])) {
        								$params[$_itemfile] = '';
        								//$params['delete_image'] = true;
        							} else if (isset($params[$_itemfile]['value'])) {
        								$params[$_itemfile] = $params[$_itemfile]['value'];
        							} else {
        								$params[$_itemfile] = '';
        							}
        						}						
        					}					
        				}		
        			}
        			try{
        				if ($this->getRequest()->isPost()) {											
        					$_model = $this->_objectManager->create('Magetop\SellerAssignProduct\Model\SellerAssignProduct');
        					$_model->addData($params);
        					$_model->save();
        					$this->messageManager->addSuccess(__('You assigned product'));					
        				}
        			}catch(\Magento\Framework\Exception\LocalizedException $e){
        				$this->messageManager->addError($this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage()));
        			}
                }
            }
		}catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage()));            
        }
		return $resultRedirect->setPath('sellerassignproduct');
	}	
}