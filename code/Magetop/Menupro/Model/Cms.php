<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Model;


class Cms extends \Magento\Framework\Model\AbstractModel
{
	public function __construct(
		\Magento\Cms\Model\Page $cmsPage,
		\Magento\Framework\UrlInterface $urlBuilder
	)
	{
		$this->_cmsPage = $cmsPage;
		$this->_urlBuilder = $urlBuilder;
	}
	public function getCmsCollectionsForGrid()
	{
		$cms_for_grid=array();
		$cmscollection = $this->_cmsPage->getCollection();
		foreach ($cmscollection as $value){
			if($value->getIsActive()==true){
				$cms_for_grid[$value->getIdentifier()]=$value->getTitle();
			}
		}
		return $cms_for_grid;
	}
	public function getCmsUrl($itemUrlValue) {
		$page = $this->_cmsPage->load($itemUrlValue);
		return $this->_urlBuilder->getUrl(null, ['_direct' => $page->getIdentifier()]);
	}
}
