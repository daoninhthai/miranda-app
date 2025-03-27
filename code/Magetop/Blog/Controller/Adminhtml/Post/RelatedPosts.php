<?php
/**
 * Copyright © 2015  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Controller\Adminhtml\Post;

/**
 * Blog post related posts controller
 */
class RelatedPosts extends \Magetop\Blog\Controller\Adminhtml\Post
{
    /**
     * View related posts action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
	public function execute()
    {
        $model = $this->_getModel();
        $this->_getRegistry()->register('current_model', $model);

        $this->_view->loadLayout()
            ->getLayout()
            ->getBlock('blog.post.edit.tab.relatedposts')
            ->setPostsRelated($this->getRequest()->getPost('posts_related', null));
 
        $this->_view->renderLayout();
    }
}
