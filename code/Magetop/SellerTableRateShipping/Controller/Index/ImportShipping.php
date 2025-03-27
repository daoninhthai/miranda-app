<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Table_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerTableRateShipping\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magetop\SellerTableRateShipping\Model\SellerTableRateShippingFactory;
use Magento\Customer\Model\Session;

class ImportShipping extends \Magento\Framework\App\Action\Action
{
	protected $_customerFactory;
	protected $_mkHelperMail;
	protected $_SellerTableRateShippingFactory;
    protected $_customerSession;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
		SellerTableRateShippingFactory $SellerTableRateShippingFactory,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
		$this->_SellerTableRateShippingFactory = $SellerTableRateShippingFactory;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
		try{
    		if($this->getRequest()->isPost()){
                if( isset( $_FILES['shipping_file'] ) )
                {
                    $filename =  $_FILES['shipping_file']['name'];
                    $i = strrpos($filename,".");
                    if (!$i) { return ""; }
                    $l = strlen($filename) - $i;
                    $ext = substr($filename,$i+1,$l);
                    if(in_array($ext,array('csv'))){
                        $csv_file = $_FILES['shipping_file']['tmp_name'];
                        if ( ! is_file( $csv_file ) ){
                        	$this->messageManager->addError(__('File not found'));
                        }                     
                        if (($handle = fopen( $csv_file, "r")) !== FALSE){
                            $num = 0;
                            $row = array();
                            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE){
                                if($num ==0){
                                    $row[0] = $data[0];
                                    $row[1] = $data[1];
                                    $row[2] = $data[2];
                                    $row[3] = $data[3];
                                    $row[4] = $data[4];
                                    $row[5] = $data[5];
                                    $row[6] = $data[6];
                                    $row[7] = $data[7];
                                    $row[8] = $data[8];
                                }else{
                                    foreach($row as $key=>$val){
                                        if($val=='seller_id') $seller_id = $data[$key];
                                        if($val=='title') $title = $data[$key];
                                        if($val=='price') $price = $data[$key];
                                        if($val=='country_code') $country_code = $data[$key];
                                        if($val=='region_id') $region_id = $data[$key];
                                        if($val=='zip_from') $zip_from = $data[$key];
                                        if($val=='zip_to') $zip_to = $data[$key];
                                        if($val=='weight_from') $weight_from = $data[$key];
                                        if($val=='weight_to') $weight_to = $data[$key];
                                    }
                                    $tableRate = $this->_objectManager->create('Magetop\SellerTableRateShipping\Model\SellerTableRateShipping');
                                    $tableRate->setSellerId($this->_customerSession->getId());   
                                    $tableRate->setTitle($title); 
                                    $tableRate->setType(1);  
                                    $tableRate->setPrice($price);  
                                    $tableRate->setCountryCode($country_code);   
                                    $tableRate->setRegionId($region_id);   
                                    $tableRate->setZipFrom($zip_from);  
                                    $tableRate->setZipTo($zip_to);  
                                    $tableRate->setWeightFrom($weight_from);   
                                    $tableRate->setWeightTo($weight_to);  
                                    $tableRate->setStatus(1); 
                                    $tableRate->save();
            
                                    $msg = __('Imported successfully.');
                                    $this->messageManager->addSuccess( $msg );  
                                }
                                $num++;
                            }
                        }
                    }else{
                        $this->messageManager->addError(__('File is not csv'));
                    }
                }
            }     
            $this->_redirect( 'sellertablerateshipping' );  
		}catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());                                 
			$this->_redirect( 'sellertablerateshipping' );
		}	 
    }
}