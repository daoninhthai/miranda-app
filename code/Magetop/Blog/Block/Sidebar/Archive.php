<?php
/**
 * Copyright © 2016  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Block\Sidebar;

/**
 * Blog sidebar archive block
 */
class Archive extends \Magetop\Blog\Block\Post\PostList\AbstractList
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'archive';

    /**
     * Available months
     * @var array
     */
    protected $_months;

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection->getSelect()->group(
            'MONTH(main_table.publish_time)',
            'DESC'
        );
    }

    /**
     * Retrieve available months
     * @return array
     */
    public function getMonths()
    {
        if (is_null($this->_months)) {
            $this->_months = array();
            $this->_preparePostCollection();
            foreach($this->_postCollection as $post) {
                $time = strtotime($post->getData('publish_time'));
                $this->_months[date('Y-m', $time)] = $time;
            }
        }


        return $this->_months;
    }

    /**
     * Retrieve year by time
     * @param  int $time
     * @return string
     */
    public function getYear($time)
    {
        return date('Y', $time);
    }

    /**
     * Retrieve month by time
     * @param  int $time
     * @return string
     */
    public function getMonth($time)
    {
        return __(date('F', $time));
    }

    /**
     * Retrieve archive url by time
     * @param  int $time
     * @return string
     */
    public function getTimeUrl($time)
    {
        return $this->_url->getUrl(
            date('Y-m', $time),
            \Magetop\Blog\Model\Url::CONTROLLER_ARCHIVE
        );
    }

	/**
     * Retrieve blog identities
     * @return array
     */
	public function getIdentities()
    {
        return [\Magento\Cms\Model\Block::CACHE_TAG . '_blog_archive_widget'  ];
    }

}
