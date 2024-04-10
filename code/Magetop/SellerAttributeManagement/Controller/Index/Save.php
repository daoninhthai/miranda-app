<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Attribute_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAttributeManagement\Controller\Index;
use Magento\Framework\App\Filesystem\DirectoryList;
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
	/**
     * @var \Magento\Customer\Model\Session
     */
	protected $_customerSession;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    protected $sellerattributevalueFactory; 
         
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession,
        \Magetop\SellerAttributeManagement\Model\SellerAttributeValue $sellerattributevalueFactory        
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->sellerattributevalueFactory = $sellerattributevalueFactory;           
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        try{
            $isseller = $this->_objectManager->get('Magetop\Marketplace\Helper\Data')->checkIsSeller();
            if($isseller){	
                $customerSession = $this->_customerSession;
                if($customerSession->isLoggedIn()){
                    $params = $this->getRequest()->getParams();					
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
        							$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
        												   ->getDirectoryRead(DirectoryList::MEDIA);
        							$result = $uploader->save($mediaDirectory->getAbsolutePath('Magetop/SellerAttributeManagement/images'));
        							$params[$_itemfile] = 'Magetop/SellerAttributeManagement/images' . $result['file'];
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
                    $value = $this->sellerattributevalueFactory->getCollection()->addFieldToFilter('seller_id', $this->_customerSession->getId());
                    if(count($value)){
                        foreach($value as $row){
                            $row->setValue(serialize($params));
                            $row->save();
                        }  
                    }else{
                        $sellerattributevalue = $this->_objectManager->create('Magetop\SellerAttributeManagement\Model\SellerAttributeValue');
                        $sellerattributevalue->setData('seller_id', $this->_customerSession->getId());
                        $sellerattributevalue->setData('value', serialize($params));
                        $sellerattributevalue->save();
                    }
                }
            }  
            $msg = __('You saved successfully.');
            $this->messageManager->addSuccess( $msg );
            $this->_redirect( 'marketplace/seller/account' );  
        }catch (\Exception $e) {    
            $this->messageManager->addError($e->getMessage()); 
            $this->_redirect( 'marketplace/seller/account' );       
        }    
    } 
}