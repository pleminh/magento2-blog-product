<?php
namespace Gemtoo\Blog\Block\Adminhtml\Catalog\Category\Tab;

use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Gemtoo\Blog\Model\ResourceModel\Article\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Gemtoo\Blog\Model\Article\Category as ArticleCategory;

/**
 * @method Article setCategoryArticles(array $articles)
 */
class Article extends ExtendedGrid
{
    /**
     * @var \Gemtoo\Blog\Model\ArticleFactory
     */
    protected $articleCollectionFactory;

    /**
     * @var \Gemtoo\Blog\Model\Article\Category
     */
    protected $articleCategory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param CollectionFactory $articleCollectionFactory
     * @param ArticleCategory $articleCategory
     * @param Registry $registry
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param array $data
     */
    public function __construct(
        CollectionFactory $articleCollectionFactory,
        ArticleCategory $articleCategory,
        Registry $registry,
        Context $context,
        BackendHelper $backendHelper,
        array $data = array()
    ) {
        $this->articleCollectionFactory = $articleCollectionFactory;
        $this->articleCategory = $articleCategory;
        $this->registry = $registry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @access public
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('catalog_category_gemtoo_blog_article');
        $this->setDefaultSort('article_id');
        $this->setUseAjax(true);
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(['in_articles'=>1]);
        }
    }

    /**
     * get current category
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * prepare collection
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->articleCollectionFactory->create();
        if ($this->getCategory()->getId()){
            $constraint = 'related.category_id='.$this->getCategory()->getId();
        }
        else{
            $constraint = 'related.category_id=0';
        }
        $collection->getSelect()->joinLeft(
            ['related' => $collection->getTable('gemtoo_blog_article_category')],
            'related.article_id=main_table.article_id AND '.$constraint,
            ['position']
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_articles',
            [
                'header_css_class'  => 'a-center',
                'type'  => 'checkbox',
                'name'  => 'in_articles',
                'values'=> $this->_getSelectedArticles(),
                'align' => 'center',
                'index' => 'article_id'
            ]
        );
        $this->addColumn(
            'article_id',
            [
                'header'=> __('Id'),
                'type'  => 'number',
                'align' => 'left',
                'index' => 'article_id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header'=> __('Name'),
                'align' => 'left',
                'index' => 'name',
            ]
        );
        $this->addColumn(
            'position',
            [
                'header'        => __('Position'),
                'name'          => 'position',
                'width'         => 60,
                'type'        => 'number',
                'validate_class'=> 'validate-number',
                'index'         => 'position',
                'editable'      => true,
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * get selected articles
     * @return array
     */
    protected function _getSelectedArticles()
    {
        $articles = $this->getCategoryArticles();
        if (!is_array($articles)) {
            $articles = array_keys($this->getSelectedArticles());
        }
        return $articles;
    }

    /**
     * @access public
     * @return array
     */
    public function getSelectedArticles()
    {
        $articles = array();
        $selected = $this->articleCategory->getSelectedArticles($this->getCategory());
        if (!is_array($selected)){
            $selected = array();
        }
        foreach ($selected as $article) {
            /** @var \Gemtoo\Blog\Model\Article $article */
            $articles[$article->getId()] = $article->getPosition();
        }
        return $articles;
    }

    /**
     * get row URL
     * @param \Gemtoo\Blog\Model\Article $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return '#';
    }

    /**
     * get grid url
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'gemtoo_blog/catalog_category/articlesGrid',
            ['id'=>$this->getCategory()->getId()]
        );
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_articles') {
            $articleIds = $this->_getSelectedArticles();
            if (empty($articleIds)) {
                $articleIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('article_id', ['in'=>$articleIds]);
            } else {
                if($articleIds) {
                    $this->getCollection()->addFieldToFilter('article_id', ['nin'=>$articleIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
}
