<?php
namespace Magetop\Marketplace\Model\Layout\Update;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\Dom\ValidationSchemaException;
use Magento\Framework\Config\DomFactory;
use Magento\Framework\Config\ValidationStateInterface;

class Validator extends \Magento\Framework\View\Model\Layout\Update\Validator
{
    public function __construct(
        DomFactory $domConfigFactory,
        UrnResolver $urnResolver,
        ValidationStateInterface $validationState = null
    ) {
        $this->_domConfigFactory = $domConfigFactory;
        $this->_initMessageTemplates();
        $this->_xsdSchemas = [
            self::LAYOUT_SCHEMA_PAGE_HANDLE => $urnResolver->getRealPath(
                'urn:magento:framework:View/Layout/etc/page_layout.xsd'
            ),
            self::LAYOUT_SCHEMA_MERGED => $urnResolver->getRealPath(
                'urn:magetop:module:Magetop_Marketplace:etc/layout_merged.xsd'
            ),
        ];
        $this->validationState = $validationState
            ?: ObjectManager::getInstance()->get(ValidationStateInterface::class);
    }
}