<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://Magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Themes\Block\Socail;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Instagram extends Template 
{	
	public function _iscurl(){
		if(function_exists('curl_version')) {
			return true;
		} else {
			return false;
		}
	}	
	public function getInstagramData($user_id = NULL, $access_token = NULL, $count = NULL, $width = NULL, $height = NULL) {
		$host = "https://api.instagram.com/v1/users/".$user_id."/media/recent/?access_token=".$access_token."&count=".$count;
		if($this->_iscurl()) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			//curl_setopt($ch1, CURLOPT_POSTFIELDS, $para1);
			$content = curl_exec($ch);
			curl_close($ch);
		}
		else {
			$content = file_get_contents($host);
		}
		$content = json_decode($content, true);
		$j = 0;
		$i = 0;
		if(isset($content['data'])) {
			foreach($content['data'] as $contents){
				$j++;
			}
		}
		if(!(isset($content['data'][$i]['images']['low_resolution']['url'])) || !$content['data'][$i]['images']['low_resolution']['url']) {
			echo 'There are not any images in this instagram.';
			return false;
		}
		if(!$width){
			$width = 100;
		}
		if(!$height){
			$height = 100;
		}
		for($i=0 ; $i<$j; $i++){
			$html = "<a href='".$content['data'][$i]['link']."' rel='nofollow' target='_blank'><img width='".$width."' height='".$height."' src='".$content['data'][$i]['images']['low_resolution']['url']."' alt='' /></a>";
			echo $html;
		}
	}	
}