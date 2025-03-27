<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetop\Marketplace\Ui\CatalogInventory\Component\Product\Form\Element;

use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Framework\Data\ValueSourceInterface;

/**
 * Class UseConfigSettings sets default value from configuration
 */
class UseConfigSettings extends Checkbox
{
    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (
            isset($config['keyInConfiguration'])
            && isset($config['valueFromConfig'])
            && $config['valueFromConfig'] instanceof ValueSourceInterface
        ) {
            $keyInConfiguration = $config['valueFromConfig']->getValue($config['keyInConfiguration']);
            if (!empty($config['@unserialized']) && @strpos($keyInConfiguration, 'a:') === 0) {
                $om = \Magento\Framework\App\ObjectManager::getInstance();
                $version = $om->get('Magetop\Marketplace\Helper\Data')->getMagentoVersion();
                if(version_compare($version, '2.2.0') >= 0){
                    $config['valueFromConfig'] = $om->get('Magento\Framework\Serialize\Serializer\Json')->unserialize($keyInConfiguration);
                }else{
                    $config['valueFromConfig'] = @unserialize($keyInConfiguration);
                }
            } else {
                $config['valueFromConfig'] = $keyInConfiguration;
            }
        }
        $this->setData('config', (array)$config);

        parent::prepare();
    }
}
