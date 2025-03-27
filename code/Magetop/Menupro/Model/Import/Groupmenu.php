<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2013-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Model\Import;
use Magento\Framework\App\Filesystem\DirectoryList;

class Groupmenu implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    public function getAllOptions($withEmpty = true)
    {
            $path = sprintf(\Magetop\Menupro\Controller\Adminhtml\Action::CMS, '');
            $path = str_replace("//", "/", $path);
            $GroupOptions = array();
            for ($i=1; $i<=8; $i++){
                $GroupOptions[] = array(
                    'label' => 'GroupId_' .$i,
                    'value' => 'GroupId_' .$i
                );
            }
            if ($withEmpty) {
                array_unshift($GroupOptions, array(
                    'value' => '',
                    'label' => __('-- Select Group Id --')
                ));
            }
        return $GroupOptions;
    }
}