<?php
namespace Magetop\Marketplace\Controller\Adminhtml\Products;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magetop\Marketplace\Block\Adminhtml\Products\Grid;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem\Driver\File;

class ExportCsv extends Action
{
    /**
     * @var FileFactory
     */
    private $fileFactory;


    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var File
     */
    private $fileDriver;

    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        PageFactory $resultPageFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $resultPage = $this->resultPageFactory->create();
            $grid = $resultPage->getLayout()->createBlock(Grid::class);

            // Lấy nội dung CSV
            $csvContent = $grid->getCsv();

            // Xử lý nội dung CSV
            $cleanContent = $this->cleanCsvContent($csvContent);

            $fileName = "products_export_" . date('Ymd_His') . '.csv';

            return $this->fileFactory->create(
                $fileName,
                [
                    'type' => 'string',
                    'value' => $cleanContent,
                    'rm' => true
                ],
                DirectoryList::VAR_DIR,
                'text/csv'
            );

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Could not export products. Error: %1', $e->getMessage())
            );
            $this->_redirect('*/*/index');
        }
    }

    protected function cleanCsvContent($csvContent)
    {
        $lines = explode("\n", $csvContent);
        $cleanLines = [];

        foreach ($lines as $line) {
            $values = str_getcsv($line);
            $cleanValues = array_map([$this, 'cleanHtmlValue'], $values);
            $cleanLines[] = $this->convertToCsvLine($cleanValues);
        }

        return implode("\n", $cleanLines);
    }

    protected function cleanHtmlValue($value)
    {
        // Kiểm tra null hoặc rỗng
        if ($value === null || $value === '') {
            return $value;
        }

        // Xử lý đặc biệt cho thẻ img
        if (is_string($value) && strpos($value, '<img') !== false) {
            preg_match('/src="([^"]+)"/', $value, $matches);
            return $matches[1] ?? '';
        }

        // Xử lý chung
        $value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', strip_tags((string)$value)));
    }

    protected function convertToCsvLine($array)
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $array);
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return trim($csv);
    }
}
