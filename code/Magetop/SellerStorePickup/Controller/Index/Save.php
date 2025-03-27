<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Controller\Index;
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
    protected $_sellerstorepickupFactory; 
         
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession,
        \Magetop\SellerStorePickup\Model\SellerStorePickup $sellerstorepickupFactory        
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_sellerstorepickupFactory = $sellerstorepickupFactory;           
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
                    $data = $this->getRequest()->getPostValue(); 
                    $storepickup = $this->_sellerstorepickupFactory;
                    if($data['id']){
                        $storepickup->load($data['id']);
                        $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                        $storepickup->setData('updated_time', date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                    }else{
                        $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                        $storepickup->setData('created_time', date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                    }
                    $request = $this->getRequest();
                    $params = $request->getParams();                                                                                
                    $path_images = 'Magetop/SellerStorePickup/images';
                    if (count($this->getRequest()->getFiles())) {				
        				foreach($this->getRequest()->getFiles() as $_itemfile=>$_itemfilevalue){
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
                    $storepickup->setData('seller_id', $this->_customerSession->getId());
                    $storepickup->setData('title', $data['title']);
                    $storepickup->setData('phone_number', $data['phone_number']);
                    $storepickup->setData('mapicon', @$params['mapicon']?$params['mapicon']:'');
                    $storepickup->setData('address', $data['address']);
                    $storepickup->setData('city', $data['city']);
                    $storepickup->setData('state', $data['state']);
                    $storepickup->setData('zipcode', $data['zipcode']);
                    $storepickup->setData('country', $data['country']);
                    $storepickup->setData('store_time', json_encode($data['store_time']));
                    $storepickup->setData('longitude', $data['longitude']);
                    $storepickup->setData('latitude', $data['latitude']);
                    $storepickup->setData('zoom_level', $data['zoom_level']);
                    $storepickup->setData('shop_location', $data['shop_location']);
                    $storepickup->setData('status', $data['status']);
                    $storepickup->save();
                }
            }  
            $msg = __('You saved successfully.');
            $this->messageManager->addSuccess( $msg );
            $this->_redirect( 'sellerstorepickup/index/liststore' );  
        }catch (\Exception $e) {    
            $this->messageManager->addError($e->getMessage()); 
            $this->_redirect( 'sellerstorepickup/index/liststore' );       
        }    
    } 
}