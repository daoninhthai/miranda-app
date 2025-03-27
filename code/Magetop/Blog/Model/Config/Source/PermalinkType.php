<?php
/**
 * Copyright © 2016  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Model\Config\Source;

use Magetop\Blog\Model\Url;

/**
 * Used in creating options for permalink config value selection
 */
class PermalinkType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Url::PERMALINK_TYPE_DEFAULT, 'label' => __('Default: mystore.com/{blog_route}/{post_route}/post-title/')],
            ['value' => Url::PERMALINK_TYPE_SHORT, 'label' => __('Short: mystore.com/{blog_route}/post-title/')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
