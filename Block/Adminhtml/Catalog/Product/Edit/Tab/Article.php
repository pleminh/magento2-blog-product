<?php
namespace Gemtoo\Blog\Block\Adminhtml\Catalog\Product\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Registry;
use Gemtoo\Blog\Model\ResourceModel\Article\CollectionFactory as ArticleCollectionFactory;
use Gemtoo\Blog\Model\Article\Product as ArticleProduct;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;

/**
 * @method Article setUseAjax(\bool $useAjax)
 * @method array getProductArticles()
 * @method Article setProductArticles(array $articles)
 */
class Article extends ExtendedGrid implements TabInterface
{
    /**
     * @var \Gemtoo\Blog\Model\ResourceModel\Article\CollectionFactory
     */
    protected $articleCollectionFactory;
    /**
     * @var \Gemtoo\Blog\Model\Article\Product
     */
    protected $articleProduct;
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $registry;
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $productBuilder;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @param ArticleCollectionFactory $articleCollectionFactory
     * @param ArticleProduct $articleProduct
     * @param Registry $registry
     * @param ProductBuilder $productBuilder
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param array $data
     */
    public function __construct(
        ArticleCollectionFactory $articleCollectionFactory,
        ArticleProduct $articleProduct,
        Registry $registry,
        ProductBuilder $productBuilder,
        Context $context,
        BackendHelper $backendHelper,
        array $data = []
    )
    {
        $this->articleCollectionFactory = $articleCollectionFactory;
        $this->articleProduct = $articleProduct;
        $this->registry = $registry;
        $this->productBuilder = $productBuilder;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * set grid parameters
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('article_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getProduct()->getId()) {
            $this->setDefaultFilter(['in_articles'=>1]);
        }
    }

    /**
     * prepare collection
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->articleCollectionFactory->create();
        if ($this->getProduct()->getId()) {
            $constraint = 'related.product_id='.$this->getProduct()->getId();
        } else {
            $constraint = 'related.product_id=0';
        }
        $collection->getSelect()->joinLeft(
            ['related' => $collection->getTable('gemtoo_blog_article_product')],
            'related.article_id = main_table.article_id AND '.$constraint,
            ['position']
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * no mass action here
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
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
                'type'  => 'checkbox',
                'name'  => 'in_articles',
                'values'=> $this->_getSelectedArticles(),
                'align' => 'center',
                'index' => 'article_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
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
     * @return array
     */
    protected function _getSelectedArticles()
    {
        $articles = $this->getProductArticles();
        if (!is_array($articles)) {
            $articles = array_keys($this->getSelectedArticles());
        }
        return $articles;
    }

    /**
     * get selected articles
     * @return array
     */
    public function getSelectedArticles()
    {
        $articles = [];
        $selected = $this->articleProduct->getSelectedArticles($this->getProduct());
        if (!is_array($selected)) {
            $selected = [];
        }
        foreach ($selected as $article) {
            /** @var \Gemtoo\Blog\Model\Article $article */
            $articles[$article->getId()] = ['position' => $article->getPosition()];
        }
        return $articles;
    }

    /**
     * get row url
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $item
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
        return $this->_urlBuilder->getUrl(
            '*/*/articlesGrid',
            [
                'id'=>$this->getProduct()->getId()
            ]
        );
    }

    /**
     * get current product
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (is_null($this->_product)) {
            if ($this->registry->registry('current_product')) {
                $this->_product = $this->registry->registry('current_product');
            } else {
                $product = $this->productBuilder->build($this->getRequest());
                $this->_product = $product;
            }
        }
        return $this->_product;
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
                if ($articleIds) {
                    $this->getCollection()->addFieldToFilter('article_id', ['nin'=>$articleIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Articles');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('gemtoo_blog/catalog_product/articles', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
