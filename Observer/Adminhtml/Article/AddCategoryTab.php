<?php
namespace Gemtoo\Blog\Observer\Adminhtml\Article;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddCategoryTab implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Block\Adminhtml\Category\Tabs $tabs */
        $tabs = $observer->getEvent()->getTabs();
        $container = $tabs->getLayout()->createBlock(
            'Magento\Backend\Block\Template',
            'category.article.grid.wrapper'
        );
        /** @var \Magento\Backend\Block\Template  $container */
        $container->setTemplate('Gemtoo_Blog::catalog/category/article.phtml');
        $tab = $tabs->getLayout()->createBlock(
            'Gemtoo\Blog\Block\Adminhtml\Catalog\Category\Tab\Article',
            'category.gemtoo_blog.article.grid'
        );

        $container->setChild('grid', $tab);
        $content = $container->toHtml();
        $tabs->addTab('gemtoo_blog_articles', array(
            'label'     => __('Articles'),
            'content'   => $content,
        ));
        return $this;
    }
}
