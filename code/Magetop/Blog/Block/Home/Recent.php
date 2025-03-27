<?php
/**
 * Copyright © 2015  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Block\Home;

class Recent extends \Magetop\Blog\Block\Post\PostList\AbstractList
{
    /**
     * @return $this
     */
    public function _construct()
    {
        if($this->_scopeConfig->getValue('smkthemes/home_blog/slide',\Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
			if($this->_scopeConfig->getValue('smkthemes/home_blog/amount',\Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
				$amount = (int) $this->_scopeConfig->getValue('smkthemes/home_blog/amount',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			}else{ $amount= 5; }
        }else{ $amount= 2; }
		
		$this->setPageSize($amount);
        return parent::_construct();
    }
	
}
