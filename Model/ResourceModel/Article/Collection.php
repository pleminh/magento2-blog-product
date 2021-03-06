<?php
namespace Gemtoo\Blog\Model\ResourceModel\Article;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'article_id';
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'gemtoo_blog_article_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'article_collection';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $_joinedFields = [];

    /**
     * constructor
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param null $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $connection = null,
        AbstractDb $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;

    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gemtoo\Blog\Model\Article', 'Gemtoo\Blog\Model\ResourceModel\Article');
        $this->_map['fields']['article_id'] = 'main_table.article_id';
        $this->_map['fields']['store_id'] = 'store_table.store_id';
    }

    /**
     * after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $items = $this->getColumnValues('article_id');
        $connection = $this->getConnection();
        if (count($items)) {
            $select = $connection->select()->from(
                ['article_store' => $this->getTable('gemtoo_blog_article_store')]
            )
            ->where(
                'article_store.article_id IN (?)',
                $items
            );

            if ($result = $connection->fetchPairs($select)) {
                foreach ($this as $item) {
                    /** @var $item \Gemtoo\Blog\Model\Article */
                    if (!isset($result[$item->getData('article_id')])) {
                        continue;
                    }
                    $item->setData('store_id', $result[$item->getData('article_id')]);
                }
            }
        }
        return parent::_afterLoad();
    }

    /**
     * Add filter by store
     *
     * @param int|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof Store) {
                $store = [$store->getId()];
            }

            if (!is_array($store)) {
                $store = [$store];
            }

            if ($withAdmin) {
                $store[] = Store::DEFAULT_STORE_ID;
            }

            $this->addFilter('store_id', ['in' => $store], 'public');
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store_id')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('gemtoo_blog_article_store')],
                'main_table.article_id = store_table.article_id',
                []
            )
            ->group('main_table.article_id');
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }

    /**
     * @param $product
     * @return $this
     */
    public function addProductFilter($product)
    {
        if ($product instanceof Product) {
            $product = $product->getId();
        }
        if (!isset($this->_joinedFields['product'])) {
            $this->getSelect()->join(
                ['related_product' => $this->getTable('gemtoo_blog_article_product')],
                'related_product.article_id = main_table.article_id',
                ['position']
            );
            $this->getSelect()->where('related_product.product_id = ?', $product);
            $this->_joinedFields['product'] = true;
        }
        return $this;
    }

    /**
     * @param $category
     * @return $this
     */
    public function addCategoryFilter($category)
    {
        if ($category instanceof Category){
            $category = $category->getId();
        }
        if (!isset($this->_joinedFields['category'])) {
            $this->getSelect()->join(
                ['related_category' => $this->getTable('gemtoo_blog_article_category')],
                'related_category.article_id = main_table.article_id',
                ['position']
            );

            $this->getSelect()->where('related_category.category_id = ?', $category);
            $this->_joinedFields['category'] = true;
        }
        return $this;
    }

}
