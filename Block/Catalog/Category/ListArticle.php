<?php
namespace Gemtoo\Blog\Block\Catalog\Category;

use Magento\Framework\View\Element\Template;
use Gemtoo\Blog\Model\Article\Category as CategoryModel;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

class ListArticle extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Gemtoo\Blog\Model\Article\Category
     */
    protected $categoryModel;

    /**
     * @var \Gemtoo\Blog\Model\ResourceModel\Article\Collection
     */
    protected $articleCollection;

    /**
     * @param CategoryModel $categoryModel
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        CategoryModel $categoryModel,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->categoryModel = $categoryModel;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Gemtoo\Blog\Model\ResourceModel\Article\Collection
     */
    public function getArticleCollection()
    {
        if (is_null($this->articleCollection)) {
            $this->articleCollection = $this->categoryModel
                ->getSelectedArticlesCollection($this->getCategory())
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addFieldToFilter('is_active', 1);//TODO: use constant here
            $this->articleCollection->getSelect()->order('related_category.position');
        }
        return $this->articleCollection;
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->registry->registry('current_category');
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
        $pager->setFragment($this->getAnchorName());
        $pager->setCollection($this->getArticleCollection());
        $this->setChild('pager', $pager);
        $this->getArticleCollection()->load();
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getAnchorName()
    {
        return 'catalog.category.list.gemtoo.blog.article';
    }
}
