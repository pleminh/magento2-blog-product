<?php
namespace Gemtoo\Blog\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Article extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_order';
        $this->_blockGroup = 'Gemtoo_Blog';
        $this->_headerText = __('Articles');
        $this->_addButtonLabel = __('Create New Article');
        parent::_construct();
    }
}
