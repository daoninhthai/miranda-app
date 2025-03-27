<?php
namespace Magetop\Marketplace\Block\Adminhtml\Form\Renderer\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Magetop extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = base64_decode('PGRpdiBzdHlsZT0iY2xlYXI6IGJvdGg7IiA+PGEgaHJlZj0iaHR0cHM6Ly93d3cubWFnZXRvcC5jb20vbWFnZW50by0yLW11bHRpLXZlbmRvci1tYXJrZXRwbGFjZS1leHRlbnNpb24uaHRtbCIgdGFyZ2V0PSJfYmxhbmsiID48aW1nIHdpZHRoPSIxMDAlIiBzcmM9Imh0dHBzOi8vd3d3Lm1hZ2V0b3AuY29tL3NraW4vZnJvbnRlbmQvcndkL21hZ2V0b3AyMDIwL2ltYWdlcy9sb2dvX2NoYXJjb2FsLnBuZyIgYWx0PSIiIC8+PC9hPjwvZGl2Pg==');
        return $html;       
    }
}