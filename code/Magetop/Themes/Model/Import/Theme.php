<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://Magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Themes\Model\Import;
use Magento\Framework\App\Filesystem\DirectoryList;

class Theme implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    public function getAllOptions($withEmpty = true)
    {
            $path = sprintf(\Magetop\Themes\Controller\Adminhtml\Action::CMS, '');
            $path = str_replace("//", "/", $path);
            $dir = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\Filesystem')->getDirectoryWrite(DirectoryList::APP);
            $path = $dir->getAbsolutePath($path);
            $packages = $this->_listDirectories($path);
            $themeOptions = array();
            foreach ($packages as $pkg) {
                $themes = $this->_listDirectories($path.$pkg);
                foreach ($themes as $theme) {
                    $themeOptions[] = array(
                        'label' => $pkg. '/' .$theme,
                        'value' => $pkg. '/' .$theme
                    );
                }
            }
            $label = $themeOptions ? __('-- Select Theme --') : __('-- Not found theme --');
            if ($withEmpty) {
                array_unshift($themeOptions, array(
                    'value' => '',
                    'label' => $label
                ));
            }
        return $themeOptions;
    }

    private function _listDirectories($path, $fullPath = false)
    {
        $result = array();
        if(is_dir($path)){
            $dir = opendir($path);
            if ($dir) {
                while ($entry = readdir($dir)) {
                    if (substr($entry, 0, 1) == '.' || !is_dir($path . DIRECTORY_SEPARATOR . $entry)){
                        continue;
                    }
                    if ($fullPath) {
                        $entry = $path . DIRECTORY_SEPARATOR . $entry;
                    }
                    $result[] = $entry;
                }
                unset($entry);
                closedir($dir);
            }            
        } 
        return $result;
    }

}
