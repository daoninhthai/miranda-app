<?php
/**
 * Copyright © 2016  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Controller\Adminhtml\Import;

/**
 * Blog available imports list controller
 */
class Index extends \Magento\Backend\App\Action
{
	/**
     * Start available import execute
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magetop_Blog::import');
        $title = __('Blog Import');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }
}
