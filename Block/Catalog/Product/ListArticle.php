<?php
namespace Gemtoo\Blog\Block\Catalog\Product;

use Gemtoo\Blog\Model\Article;
use Gemtoo\Blog\Model\Article\Product as ArticleProduct;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\BlockFactory;

/**
 * @method ListArticle setTitle(\string $title)
 */
class ListArticle extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Gemtoo\Blog\Model\Article\Product
     */
    protected $articleProduct;

    protected $blockFactory;

    /**
     * @var \Gemtoo\Blog\Model\ResourceModel\Article\Collection|null
     */
    protected $articleCollection;

    /**
     * @param ArticleProduct $articleProduct
     * @param Registry $registry
     * @param BlockFactory $blockFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ArticleProduct $articleProduct,
        Registry $registry,
        BlockFactory $blockFactory,
        Context $context,
        array $data = []
    )
    {
        $this->articleProduct = $articleProduct;
        $this->registry = $registry;
        $this->blockFactory = $blockFactory;
        parent::__construct($context, $data);
        $this->setTabTitle();
    }

    /**
     * @return \Gemtoo\Blog\Model\ResourceModel\Article\Collection
     */
    public function getArticleCollection()
    {
        if (is_null($this->articleCollection)) {
            $collection = $this->articleProduct->getSelectedArticlesCollection($this->getProduct());
            $collection->addStoreFilter($this->_storeManager->getStore()->getId());
            $collection->addFieldToFilter('is_active', Article::STATUS_ENABLED);
            $collection->getSelect()->order('position');
            $this->articleCollection = $collection;
        }
        return $this->articleCollection;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager');
        $pager->setNameInLayout('gemtoo_blog.article.list.pager');
        $pager->setPageVarName('p-article');
        $pager->setLimitVarName('l-article');
        $pager->setFragment('catalog.product.list.gemtoo.blog.article');
        $pager->setCollection($this->getArticleCollection());
        $this->setChild('pager', $pager);
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return $this
     */
    public function setTabTitle()
    {
        $title = $this->getCollectionSize()
            ? __('Articles %1', '<span class="counter">' . $this->getCollectionSize() . '</span>')
            : __('Articles');
        $this->setTitle($title);
        return $this;
    }

    /**
     * @return int
     */
    public function getCollectionSize()
    {
        return $this->getArticleCollection()->getSize();
    }
}
