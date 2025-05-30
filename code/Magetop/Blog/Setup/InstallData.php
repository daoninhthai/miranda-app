<?php
/**
 * Copyright © 2015  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Setup;

use Magetop\Blog\Model\Post;
use Magetop\Blog\Model\PostFactory;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Post factory
     *
     * @var \Magetop\Blog\Model\PostFactory
     */
    private $_postFactory;

    /**
     * Init
     *
     * @param \Magetop\Blog\Model\PostFactory $postFactory
     */
    public function __construct(\Magetop\Blog\Model\PostFactory $postFactory)
    {
        $this->_postFactory = $postFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [
            'title' => 'Hello world!',
            'meta_keywords' => 'magento 2 blog',
            'meta_description' => 'Magento 2 blog default post.',
            'identifier' => 'hello-world',
            'content_heading' => 'Hello world!',
            'content' => 'Welcome to <a target="_blank" href="http://magetop.com/" title="Magetop - solutions for Magento 2">Magetop</a> blog extension for Magento&reg; 2. This is your first post. Edit or delete it, then start blogging!',
            'stores' => [0]
        ];

        $this->_postFactory->create()->setData($data)->save();
    }

}
