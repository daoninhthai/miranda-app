<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetop\ReviewManagement\Controller\Adminhtml;

use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;

class SaveReview extends ProductController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (($data = $this->getRequest()->getPostValue()) && ($reviewId = $this->getRequest()->getParam('id'))) {
            $review = $this->reviewFactory->create()->load($reviewId);
            if (!$review->getId()) {
                $this->messageManager->addError(__('The review was removed by another user or does not exist.'));
            } else {
                try {
                    $review->addData($data)->save();

                    $arrRatingId = $this->getRequest()->getParam('ratings', []);
                    /** @var \Magento\Review\Model\Rating\Option\Vote $votes */
                    $votes = $this->_objectManager->create('Magento\Review\Model\Rating\Option\Vote')
                        ->getResourceCollection()
                        ->setReviewFilter($reviewId)
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                    foreach ($arrRatingId as $ratingId => $optionId) {
                        if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            $this->ratingFactory->create()
                                ->setVoteId($vote->getId())
                                ->setReviewId($review->getId())
                                ->updateOptionVote($optionId);
                        } else {
                            $this->ratingFactory->create()
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->addOptionVote($optionId, $review->getEntityPkValue());
                        }
                    }

                    $review->aggregate();
                    
                    //save image and video
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
    							if (isset($data[$_itemfile]['delete'])) {
    								$params[$_itemfile] = '';
    								$params['delete_image'] = true;
    							} else if (isset($params[$_itemfile]['value'])) {
    								$params[$_itemfile] = $params[$_itemfile]['value'];
    							} else {
    								$params[$_itemfile] = '';
    							}						
        					}					
        				}		
        			}
                    if(@$params[$_itemfile]){
                        $reviewsOptionOld = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement')
                                                                                               ->getCollection()
                                                                                               ->addFieldToFilter('review_id',$review->getId())
                                                                                               ->addFieldToFilter('type','image')
                                                                                               ->getFirstItem();
                        if($reviewsOptionOld->getId()){
                            $reviewsOption = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement')->load($reviewsOptionOld->getId());
                            $reviewsOption->setContent($params[$_itemfile]);
                            $reviewsOption->save();
                        }else{
                            $reviewsOption = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement');
                            $customer = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session');
                            $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                            $reviewsOption->setReviewId($review->getId());
                            $reviewsOption->setCustomerId($customer->getId());
                            $reviewsOption->setType('image');
                            $reviewsOption->setContent($params[$_itemfile]);
                            $reviewsOption->setPostBy('admin');
                            $reviewsOption->setCreatedAt(date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                            $reviewsOption->setStatus(1);
                            $reviewsOption->save();
                        }
                    }else{
                        if(@$params['delete_image']){
                            $reviewsOptionOld = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement')
                                                                                                   ->getCollection()
                                                                                                   ->addFieldToFilter('review_id',$review->getId())
                                                                                                   ->addFieldToFilter('type','image')
                                                                                                   ->getFirstItem();
                            if($reviewsOptionOld->getId()){
                                $reviewsOption = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement')->load($reviewsOptionOld->getId());
                                $reviewsOption->setContent('');
                                $reviewsOption->save();
                            }
                        }
                    }
                    //save image and video
                    //save image
                    if(@$data['review_video']){
                        $reviewsOptionOld = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement')
                                                                                               ->getCollection()
                                                                                               ->addFieldToFilter('review_id',$review->getId())
                                                                                               ->addFieldToFilter('type','video')
                                                                                               ->getFirstItem();
                        if($reviewsOptionOld->getId()){
                            $reviewsOption = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement')->load($reviewsOptionOld->getId());
                            $reviewsOption->setContent($data['review_video']);
                            $reviewsOption->save();
                        }else{
                            $reviewsOption = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement');
                            $customer = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session');
                            $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                            $reviewsOption->setReviewId($review->getId());
                            $reviewsOption->setCustomerId($customer->getId());
                            $reviewsOption->setType('video');
                            $reviewsOption->setContent($data['review_video']);
                            $reviewsOption->setPostBy('admin');
                            $reviewsOption->setCreatedAt(date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                            $reviewsOption->setStatus(1);
                            $reviewsOption->save();
                        }
                    }else{
                        $reviewsOptionOld = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement')
                                                                                               ->getCollection()
                                                                                               ->addFieldToFilter('review_id',$review->getId())
                                                                                               ->addFieldToFilter('type','video')
                                                                                               ->getFirstItem();
                        if($reviewsOptionOld->getId()){
                            $reviewsOption = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement')->load($reviewsOptionOld->getId());
                            $reviewsOption->setContent('');
                            $reviewsOption->save();
                        }
                    }
                    //end save image
                    
                    $this->messageManager->addSuccess(__('You saved the review.'));
                } catch (LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving this review.'));
                }
            }

            $nextId = (int)$this->getRequest()->getParam('next_item');
            if ($nextId) {
                $resultRedirect->setPath('review/*/edit', ['id' => $nextId]);
            } elseif ($this->getRequest()->getParam('ret') == 'pending') {
                $resultRedirect->setPath('*/*/pending');
            } else {
                $resultRedirect->setPath('*/*/');
            }
            return $resultRedirect;
        }
        $resultRedirect->setPath('review/*/');
        return $resultRedirect;
    }
}
