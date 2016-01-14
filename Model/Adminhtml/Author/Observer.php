<?php
namespace Gemtoo\Blog\Model\Adminhtml\Article;

use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Backend\App\Action\Context;
use Gemtoo\Blog\Model\ResourceModel\Article;
use Magento\Framework\Event\Observer as EventObserver;

class Observer
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $coreRegistry;
    /**
     * @var \Magento\Framework\UrlInterface|null
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Backend\Helper\Js|null
     */
    protected $jsHelper;
    /**
     * @var \Magento\Backend\App\Action\Context|null
     */
    protected $context;
    /**
     * @var \Gemtoo\Blog\Model\ResourceModel\Article
     */
    protected $articleResource;

    /**
     * @param Registry $coreRegistry
     * @param UrlInterface $urlBuilder
     * @param JsHelper $jsHelper
     * @param Context $context
     * @param Article $articleResource
     */
    public function __construct(
        Registry $coreRegistry,
        UrlInterface $urlBuilder,
        JsHelper $jsHelper,
        Context $context,
        Article $articleResource
    )
    {
        $this->coreRegistry   = $coreRegistry;
        $this->urlBuilder     = $urlBuilder;
        $this->jsHelper       = $jsHelper;
        $this->context        = $context;
        $this->articleResource = $articleResource;
    }


    /**
     * save product data
     * @param $observer
     * @return $this
     */
    public function saveProductData(EventObserver $observer)
    {
        $post = $this->context->getRequest()->getPost('articles', -1);
        if ($post != '-1') {
            $post = $this->jsHelper->decodeGridSerializedInput($post);
            $product = $this->coreRegistry->registry('product');
            $this->articleResource->saveArticleProductRelation($product, $post);
        }
        return $this;
    }

    public function addCategoryTab(EventObserver $observer)
    {
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

    /**
     * save category data
     * @param $observer
     * @return $this
     */
    public function saveCategoryData($observer) {
        $post = $this->context->getRequest()->getPost('category_gemtoo_blog_articles', -1);
        if ($post != '-1') {
            $post = json_decode($post, true);
            $category = $this->coreRegistry->registry('category');
            $this->articleResource->saveArticleCategoryRelation($category, $post);
        }
        return $this;
    }
}
