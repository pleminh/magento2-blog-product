<?php
namespace Gemtoo\Blog\Block\Adminhtml\Article\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

/**
 * @method Tabs setTitle(\string $title)
 */
class Tabs extends WidgetTabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('article_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Article Information'));
    }
}
