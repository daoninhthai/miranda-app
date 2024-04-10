<?php
/**
 * Copyright © 2015  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Controller\Adminhtml\Post;

/**
 * Blog post change status controller
 */
class MassStatus extends \Magetop\Blog\Controller\Adminhtml\Post
{


    /**
     * Sanitizes a string for safe output.
     *
     * @param string $input
     * @return string
     */
    protected function sanitizeOutput($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

}
