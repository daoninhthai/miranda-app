<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Model\Groupmenu\Source;

class Guide
{
	public function installGuide($position, $groupId) {
		$html = '1, Insert into Static block or CMS page for developing: <br/><br/>{{block class="Magetop\Menupro\Block\Menu" name="menupro_group_' . $groupId . '" groupmenu_id="' . $groupId . '" template="Magetop_Menupro::menupro/menupro.phtml" }}<br/><br/>';
		$html .= '2, Reference via XML layout file( to replace the default menu or other purpose):<br/><br/>';
		$html .= '<block class="Magetop\Menupro\Block\Menu" name="menupro_group_' . $groupId . '" ifconfig="menupro/setting/enable" template="Magetop_Menupro::menupro/menupro.phtml"><br/>';
		$html .= '    <action method="setData"><name>groupmenu_id</name><value>' . $groupId . '</value></action><br/>';
		$html .= '</block>';
		$html .= '<br/><br/>3, Call via frontend template file: <br/><br/><?php echo $this->getLayout()->createBlock("Magetop\Menupro\Block\Menu")->setGroupmenu_id('.$groupId.')->setTemplate("Magetop_Menupro::menupro/menupro.phtml")->toHtml(); ?>';
		return $html;
	}
}
