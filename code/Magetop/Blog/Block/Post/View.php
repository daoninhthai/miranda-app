<?php
/**
 * Copyright © 2016  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */
namespace Magetop\Blog\Block\Post;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog post view
 */
class View extends AbstractPost
{
    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $post = $this->getPost();
        $this->_addBreadcrumbs($post);
        $this->pageConfig->addBodyClass('blog-post-' . $post->getIdentifier());
        $this->pageConfig->getTitle()->set($post->getTitle());
        $this->pageConfig->setKeywords($post->getMetaKeywords());
        $this->pageConfig->setDescription($post->getMetaDescription());

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param \Magetop\Blog\Model\Post $post
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs(\Magetop\Blog\Model\Post $post)
    {
        if ($this->_scopeConfig->getValue('web/default/show_cms_breadcrumbs', ScopeInterface::SCOPE_STORE)
            && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'blog',
                [
                    'label' => __('Blog'),
                    'title' => __('Go to Blog Home Page'),
                    'link' => $this->_url->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb('blog_post', [
                'label' => $post->getTitle(),
                'title' => $post->getTitle()
            ]);
        }
    }

}
