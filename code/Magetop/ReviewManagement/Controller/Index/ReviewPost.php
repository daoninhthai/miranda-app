<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetop\ReviewManagement\Controller\Index;

use Magento\Review\Controller\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;
use Magento\Framework\App\Filesystem\DirectoryList;

class ReviewPost extends ProductController
{
    /**
     * Submit new review action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $data = $this->reviewSession->getFormData(true);
        if ($data) {
            $rating = [];
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPostValue();
            $rating = $this->getRequest()->getParam('ratings', []);
        }
        if (!empty($data)) {
            /** @var \Magento\Review\Model\Review $review */
            $review = $this->reviewFactory->create()->setData($data);
            $review->unsetData('review_id');

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($data['product_id'])
                        ->setStatusId(Review::STATUS_PENDING)
                        ->setCustomerId($this->customerSession->getCustomerId())
                        ->setStoreId($this->storeManager->getStore()->getId())
                        ->setStores([$this->storeManager->getStore()->getId()])
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->ratingFactory->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId($this->customerSession->getCustomerId())
                            ->addOptionVote($optionId, $data['product_id']);
                    }
                    //save image
                    $path_images = 'Magetop/Reviews/images';
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
                    if(@$params[$_itemfile]){
                        $reviewsOption = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement');
                        $customer = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session');
                        $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                        $reviewsOption->setReviewId($review->getId());
                        $reviewsOption->setCustomerId($customer->getId());
                        $reviewsOption->setType('image');
                        $reviewsOption->setContent($params[$_itemfile]);
                        $reviewsOption->setPostBy($customer->getCustomer()->getName());
                        $reviewsOption->setCreatedAt(date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                        $reviewsOption->setStatus(1);
                        $reviewsOption->save();
                    }
                    //end save image
                    //save video
                    if(@$data['video']){
                        $reviewsOption = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement');
                        $customer = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session');
                        $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                        $reviewsOption->setReviewId($review->getId());
                        $reviewsOption->setCustomerId($customer->getId());
                        $reviewsOption->setType('video');
                        $reviewsOption->setContent($data['video']);
                        $reviewsOption->setPostBy($customer->getCustomer()->getName());
                        $reviewsOption->setCreatedAt(date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                        $reviewsOption->setStatus(1);
                        $reviewsOption->save();
                    }
                    //end save video

                    $review->aggregate();
                    $this->messageManager->addSuccess(__('You submitted your review for moderation.'));
                } catch (\Exception $e) {
                    $this->reviewSession->setFormData($data);
                    $this->messageManager->addError(__('We can\'t post your review right now.'));
                }
            } else {
                $this->reviewSession->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                } else {
                    $this->messageManager->addError(__('We can\'t post your review right now.'));
                }
            }
        }
        $redirectUrl = $this->reviewSession->getRedirectUrl(true);
        $resultRedirect->setUrl($redirectUrl ?: $this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
}
