<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2013-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Controller\Adminhtml\Import;
use Magento\Framework\App\Filesystem\DirectoryList;
class Save extends \Magetop\Menupro\Controller\Adminhtml\Action
{
    protected $_store    = 0;
    protected $_filePath = '';
    protected $_dir = '';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $resultRedirect = $this->_resultRedirectFactory->create();
        
        if($this->getRequest()->getParam('groupmenu_id')) $this->ImportXml();

        return $resultRedirect->setPath('*/*/index');
    }

    public function ImportXml()
    {
        $groupmenu_id      = $this->getRequest()->getParam('groupmenu_id');
        $this->_filePath = sprintf(self::CMS, $groupmenu_id);
        $this->_dir      = $this->_filesystem->getDirectoryWrite(DirectoryList::APP);
        $request = $this->getRequest()->getParams();
        $stores = isset($request['store_ids']) ? $request['store_ids'] : array(0);
        $scope  = 'default';
        if(isset($request['scope']) && isset($request['scope_id'])){
            $scope = $request['scope'];
            if($request['scope'] == 'websites'){
                $stores = $this->_storeManager->getWebsite($request['scope_id'])->getStoreIds();
            }else {
                $stores  = $request['scope_id']; 
            }
        }
        $this->_store = is_array($stores) ? $stores : explode(',', $stores);
        if($request['action']){
            $this->ImportBlock();       
            $this->ImportMcpGroup();
        } else {
            $this->messageManager->addSuccess(__('This feature not available.'));
        }

    }

    public function ImportBlock()
    {
        $fileName = 'block.xml';
        $filePath = $this->_filePath .$fileName;
        $backupFilePath = $this->_dir->getAbsolutePath($filePath);
        $storeIds = $this->_store;
        try{
            if (!is_readable($backupFilePath)) throw new \Exception(__("Can't read data file: %1", $backupFilePath));
            $xmlObj = new \Magento\Framework\Simplexml\Config($backupFilePath);
            $num = 0;
            $block = $xmlObj->getNode('block');
            if($block){
                foreach ($block->children() as $item){
                    //Check if Block already exists
                    $collection = $this->_objectManager->create('\Magento\Cms\Model\ResourceModel\Block\Collection');
                    $oldBlocks = $collection->addFieldToFilter('identifier', $item->identifier)->addStoreFilter($storeIds);
                    if (count($oldBlocks) > 0){
                        $conflictingOldItems[] = $item->identifier;
                        foreach ($oldBlocks as $old) $old->delete();
                    }
                    $model = $this->_objectManager->create('Magento\Cms\Model\Block');
                    $model->setData($item->asArray())->setStores($storeIds)->save();
                    $num++;
                }               
            }

            $this->messageManager->addSuccess(__('Import (%1) Item(s) in file "%2".', $num, $backupFilePath));  

        } catch (\Exception $e) {
                // $this->messageManager->addError(__('Can not import file "%1".<br/>"%2"', $backupFilePath, $e->getMessage()));
        }
    }
	public function ImportMcpGroup()
    {
        $fileName = 'mcpgroup.xml';
        $filePath = $this->_filePath .$fileName;
        $backupFilePath = $this->_dir->getAbsolutePath($filePath);
        $storeIds = $this->_store;
        try{
            if (!is_readable($backupFilePath)) throw new \Exception(__("Can't read data file: %1", $backupFilePath));
            $xmlObj = new \Magento\Framework\Simplexml\Config($backupFilePath);
            $mcpgroup = $xmlObj->getNode('magetop_groupmenu');
            if($mcpgroup){
                foreach ($mcpgroup->children() as $item){
                    $model = $this->_objectManager->create('Magetop\Menupro\Model\Groupmenu');
                    $model->setData($item->asArray())->save();
                }
				$this->ImportMcp($model->getGroupmenuId());	
            }             

        } catch (\Exception $e) {
                $this->messageManager->addError(__('Can not import file "%1".<br/>"%2"', $backupFilePath, $e->getMessage()));
        }
    }
    public function ImportMcp($_groupmenuId)
    {
        $fileName = 'mcp.xml';
        $filePath = $this->_filePath .$fileName;
        $backupFilePath = $this->_dir->getAbsolutePath($filePath);
        $storeIds = $this->_store;
        try{
            if (!is_readable($backupFilePath)) throw new \Exception(__("Can't read data file: %1", $backupFilePath));
            $xmlObj = new \Magento\Framework\Simplexml\Config($backupFilePath);
            $num = 0;
            $mcp = $xmlObj->getNode('magetop_menu');
            if($mcp){
                foreach ($mcp->children() as $item){
                    //Check if Extra Menu already exists
                    $collection = $this->_objectManager->create('Magetop\Menupro\Model\ResourceModel\Menu\Collection');
                    $oldMenus   =  $collection->addFieldToFilter('menu_id', $item->menu_id)->load();
                    //If items can be overwritten
                    $overwrite = false; // get in cfg
                    if ($overwrite){
                        if (count($oldMenus) > 0){
                            foreach ($oldMenus as $old) $old->delete();
                        }
                    }else {
                        if (count($oldMenus) > 0){
                            continue;
                        }
                    }
                    $model = $this->_objectManager->create('Magetop\Menupro\Model\Menu');
                    $model->setData($item->asArray());
					$model->setGroupmenuId($_groupmenuId);
					$model->save();
                    $num++;
                }               
            }

            $this->messageManager->addSuccess(__('Import (%1) Item(s) in file "%2".', $num, $backupFilePath));              

        } catch (\Exception $e) {
                $this->messageManager->addError(__('Can not import file "%1".<br/>"%2"', $backupFilePath, $e->getMessage()));
        }
    }
	
	/**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetop_Menupro::import_setting');
    }
}