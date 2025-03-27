<?php
/**
 * Magetop Development
 *
 * @category  Magetop Extension
 * @package   Magetop_Marketplace
 * @author    Magetop
 * @copyright Copyright (c) Magetop (https://www.magetop.com)
 * @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Magetop\Marketplace\Block;

use Magento\Catalog\Model\Category;
use Magetop\Marketplace\Helper\Collection as MpHelper;

class Sellercategory extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;

    /**
     * @var \Magetop\Marketplace\Helper\Data $helper
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Category $category
     * @param \Magetop\Marketplace\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Category $category,
        MpHelper $helper,
        array $data = []
    ) {
        $this->category = $category;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get Category By Id
     *
     * @param int $id
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategoryById($id)
    {
        return $this->category->load($id);
    }

    /**
     * Get Seller Profile Details
     *
     * @return \Magetop\Marketplace\Model\Seller | bool
     */
    public function getProfileDetail()
    {
        return $this->helper->getProfileDetail();
    }
}
