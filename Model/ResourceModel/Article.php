<?php
namespace Gemtoo\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime as LibDateTime;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Gemtoo\Blog\Model\Article as ArticleModel;
use Magento\Framework\Event\ManagerInterface;
use Magento\Catalog\Model\Product;
use Gemtoo\Blog\Model\Article\Product as ArticleProduct;
use Gemtoo\Blog\Model\Article\Category as ArticleCategory;

class Article extends AbstractDb
{
    /**
     * Store model
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var string
     */
    protected $articleProductTable;

    /**
     * @var string
     */
    protected $articleCategoryTable;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Gemtoo\Blog\Model\Article\Product
     */
    protected $articleProduct;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param LibDateTime $dateTime
     * @param ManagerInterface $eventManager
     * @param ArticleProduct $articleProduct
     * @param ArticleCategory $articleCategory
     */
    public function __construct(
        Context $context,
        DateTime $date,
        StoreManagerInterface $storeManager,
        LibDateTime $dateTime,
        ManagerInterface $eventManager,
        ArticleProduct $articleProduct,
        ArticleCategory $articleCategory
    )
    {
        $this->date             = $date;
        $this->storeManager     = $storeManager;
        $this->dateTime         = $dateTime;
        $this->eventManager     = $eventManager;
        $this->articleProduct    = $articleProduct;
        $this->articleCategory   = $articleCategory;

        parent::__construct($context);
        $this->articleProductTable  = $this->getTable('gemtoo_blog_article_product');
        $this->articleCategoryTable = $this->getTable('gemtoo_blog_article_category');

    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gemtoo_blog_article', 'article_id');
    }

    /**
     * Process article data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['article_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('gemtoo_blog_article_store'), $condition);
        return parent::_beforeDelete($object);
    }

    /**
     * before save callback
     *
     * @param AbstractModel|\Gemtoo\Blog\Model\Article $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        foreach (['dop'] as $field) {
            $value = !$object->getData($field) ? null : $object->getData($field);
            $object->setData($field, $this->dateTime->formatDate($value));
        }
        $object->setUpdatedAt($this->date->gmtDate());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->gmtDate());
        }
        $urlKey = $object->getData('url_key');
        if ($urlKey == '') {
            $urlKey = $object->getName();
        }
        $urlKey = $object->formatUrlKey($urlKey);
        $object->setUrlKey($urlKey);
        $validKey = false;
        while (!$validKey) {
            if ($this->getIsUniqueArticleToStores($object)) {
                $validKey = true;
            } else {
                $parts = explode('-', $urlKey);
                $last = $parts[count($parts) - 1];
                if (!is_numeric($last)) {
                    $urlKey = $urlKey.'-1';
                } else {
                    $suffix = '-'.($last + 1);
                    unset($parts[count($parts) - 1]);
                    $urlKey = implode('-', $parts).$suffix;
                }
                $object->setData('url_key', $urlKey);
            }
        }
        return parent::_beforeSave($object);
    }

    /**
     * Assign article to store views
     *
     * @param AbstractModel|\Gemtoo\Blog\Model\Article $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveStoreRelation($object);
        $this->saveProductRelation($object);
        $this->saveCategoryRelation($object);
        return parent::_afterSave($object);
    }

    /**
     * Load an object using 'url_key' field if there's no field specified and value is not numeric
     *
     * @param AbstractModel|\Gemtoo\Blog\Model\Article $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'url_key';
        }
        return parent::load($object, $value, $field);
    }

    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Gemtoo\Blog\Model\Article $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [
                Store::DEFAULT_STORE_ID,
                (int)$object->getStoreId()
            ];
            $select->join(
                [
                    'gemtoo_blog_article_store' => $this->getTable('gemtoo_blog_article_store')
                ],
                $this->getMainTable() . '.article_id = gemtoo_blog_article_store.article_id',
                []
            )//TODO: check if is_active filter is needed
                ->where('is_active = ?', 1)
                ->where(
                    'gemtoo_blog_article_store.store_id IN (?)',
                    $storeIds
                )
                ->order('gemtoo_blog_article_store.store_id DESC')
                ->limit(1);
        }
        return $select;
    }

    /**
     * Retrieve load select with filter by url_key, store and activity
     *
     * @param string $urlKey
     * @param int|array $store
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     */
    protected function getLoadByUrlKeySelect($urlKey, $store, $isActive = null)
    {
        $select = $this->getConnection()
            ->select()
            ->from(['article' => $this->getMainTable()])
            ->join(
                ['article_store' => $this->getTable('gemtoo_blog_article_store')],
                'article.article_id = article_store.article_id',
                []
            )
            ->where(
                'article.url_key = ?',
                $urlKey
            )
            ->where(
                'article_store.store_id IN (?)',
                $store
            );
        if (!is_null($isActive)) {
            $select->where('article.is_active = ?', $isActive);
        }
        return $select;
    }


    /**
     * Check if article url_key exist
     * return article id if article exists
     *
     * @param string $urlKey
     * @param int $storeId
     * @return int
     */
    public function checkUrlKey($urlKey, $storeId)
    {
        $stores = [Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->getLoadByUrlKeySelect($urlKey, $stores, 1);
        $select->reset(\Zend_Db_Select::COLUMNS)
            ->columns('article.article_id')
            ->order('article_store.store_id DESC')
            ->limit(1);
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieves article name from DB by passed url key.
     *
     * @param string $urlKey
     * @return string|bool
     */
    public function getArticleNameByUrlKey($urlKey)
    {
        $stores = [Store::DEFAULT_STORE_ID];
        if ($this->store) {
            $stores[] = (int)$this->getStore()->getId();
        }
        $select = $this->getLoadByUrlKeySelect($urlKey, $stores);
        $select->reset(\Zend_Db_Select::COLUMNS)
            ->columns('article.name')
            ->order('article.store_id DESC')
            ->limit(1);
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieves article name from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getArticleNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('article_id = :article_id');
        $binds = ['article_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Retrieves article url key from DB by passed id.
     *
     * @param int $id
     * @return string|bool
     */
    public function getArticleUrlKeyById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'url_key')
            ->where('article_id = :article_id');
        $binds = ['article_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $articleId
     * @return array
     */
    public function lookupStoreIds($articleId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->getTable('gemtoo_blog_article_store'),
            'store_id'
        )
            ->where(
                'article_id = ?',
                (int)$articleId
            );
        return $adapter->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->store);
    }

    /**
     * check if url key is unique
     *
     * @param AbstractModel|\Gemtoo\Blog\Model\Article $object
     * @return bool
     */
    public function getIsUniqueArticleToStores(AbstractModel $object)
    {
        if ($this->storeManager->hasSingleStore() || !$object->hasStores()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('stores');
        }
        $select = $this->getLoadByUrlKeySelect($object->getData('url_key'), $stores);
        if ($object->getId()) {
            $select->where('article_store.article_id <> ?', $object->getId());
        }
        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }
        return true;
    }

    /**
     * @param ArticleModel $article
     * @return array
     */
    public function getProductsPosition(ArticleModel $article)
    {
        $select = $this->getConnection()->select()->from(
            $this->articleProductTable,
            ['product_id', 'position']
        )
        ->where(
            'article_id = :article_id'
        );
        $bind = ['article_id' => (int)$article->getId()];
        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * @param ArticleModel $article
     * @return $this
     */
    protected function saveStoreRelation(ArticleModel $article)
    {
        $oldStores = $this->lookupStoreIds($article->getId());
        $newStores = (array)$article->getStores();
        if (empty($newStores)) {
            $newStores = (array)$article->getStoreId();
        }
        $table = $this->getTable('gemtoo_blog_article_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = [
                'article_id = ?' => (int)$article->getId(),
                'store_id IN (?)' => $delete
            ];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    'article_id' => (int)$article->getId(),
                    'store_id' => (int)$storeId
                ];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        return $this;
    }

    /**
     * @param ArticleModel $article
     * @return $this
     */
    protected function saveProductRelation(ArticleModel $article)
    {
        $article->setIsChangedProductList(false);
        $id = $article->getId();
        $products = $article->getProductsData();

        if ($products === null) {
            return $this;
        }
        $oldProducts = $article->getProductsPosition();
        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);
        $update = array_intersect_key($products, $oldProducts);
        $_update = array();
        foreach ($update as $key=>$settings) {
            if (isset($oldProducts[$key]) && $oldProducts[$key] != $settings['position']) {
                $_update[$key] = $settings;
            }
        }
        $update = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['product_id IN(?)' => array_keys($delete), 'article_id=?' => $id];
            $adapter->delete($this->articleProductTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $productId => $position) {
                $data[] = [
                    'article_id' => (int)$id,
                    'product_id' => (int)$productId,
                    'position' => (int)$position
                ];
            }
            $adapter->insertMultiple($this->articleProductTable, $data);
        }

        if (!empty($update)) {
            foreach ($update as $productId => $position) {
                $where = ['article_id = ?' => (int)$id, 'product_id = ?' => (int)$productId];
                $bind = ['position' => (int)$position['position']];
                $adapter->update($this->articleProductTable, $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $productIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'gemtoo_blog_article_change_products',
                ['article' => $article, 'product_ids' => $productIds]
            );
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $article->setIsChangedProductList(true);
            $productIds = array_keys($insert + $delete + $update);
            $article->setAffectedProductIds($productIds);
        }
        return $this;
    }

    /**
     * @param Product $product
     * @param $articles
     * @return $this
     */
    public function saveArticleProductRelation(Product $product, $articles)
    {
        $product->setIsChangedArticleList(false);
        $id = $product->getId();
        if ($articles === null) {
            return $this;
        }
        $oldArticleObjects = $this->articleProduct->getSelectedArticles($product);
        if (!is_array($oldArticleObjects)) {
            $oldArticleObjects = [];
        }
        $oldArticles = [];
        foreach ($oldArticleObjects as $article) {
            /** @var \Gemtoo\Blog\Model\Article $article */
            $oldArticles[$article->getId()] = ['position' => $article->getPosition()];
        }
        $insert = array_diff_key($articles, $oldArticles);

        $delete = array_diff_key($oldArticles, $articles);

        $update = array_intersect_key($articles, $oldArticles);
        $toUpdate = [];
        foreach ($update as $productId => $values) {
            if (isset($oldArticles[$productId]) && $oldArticles[$productId]['position'] != $values['position']) {
                $toUpdate[$productId] = [];
                $toUpdate[$productId]['position'] = $values['position'];
            }
        }

        $update = $toUpdate;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['article_id IN(?)' => array_keys($delete), 'product_id=?' => $id];
            $adapter->delete($this->articleProductTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $articleId => $position) {
                $data[] = [
                    'product_id' => (int)$id,
                    'article_id' => (int)$articleId,
                    'position' => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->articleProductTable, $data);
        }

        if (!empty($update)) {
            foreach ($update as $articleId => $position) {
                $where = ['product_id = ?' => (int)$id, 'article_id = ?' => (int)$articleId];
                $bind = ['position' => (int)$position['position']];
                $adapter->update($this->articleProductTable, $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $articleIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'gemtoo_blog_product_change_articles',
                ['product' => $product, 'article_ids' => $articleIds]
            );
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $product->setIsChangedArticleList(true);
            $articleIds = array_keys($insert + $delete + $update);
            $product->setAffectedArticleIds($articleIds);
        }
        return $this;
    }

    protected function saveCategoryRelation(ArticleModel $article)
    {
        $article->setIsChangedCategoryList(false);
        $id = $article->getId();
        $categories = $article->getCategoriesIds();

        if ($categories === null) {
            return $this;
        }
        $oldCategoryIds = $article->getCategoryIds();
        $insert = array_diff_key($categories, $oldCategoryIds);
        $delete = array_diff_key($oldCategoryIds, $categories);

        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = array('category_id IN(?)' => $delete, 'article_id=?' => $id);
            $adapter->delete($this->articleCategoryTable, $condition);
        }
        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $categoryId) {
                $data[] = array(
                    'article_id' => (int)$id,
                    'category_id' => (int)$categoryId,
                    'position' => 1
                );
            }
            $adapter->insertMultiple($this->articleCategoryTable, $data);
        }

        if (!empty($insert) || !empty($delete)) {
            $categoryIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'gemtoo_blog_article_change_categories',
                array('article' => $article, 'category_ids' => $categoryIds)
            );
        }

        if (!empty($insert) /*|| !empty($update)*/ || !empty($delete)) {
            $article->setIsChangedCategoryList(true);
            $categoryIds = array_keys($insert + $delete /* + $update*/);
            $article->setAffectedCategoryIds($categoryIds);
        }
        return $this;
    }

    /**
     * @param ArticleModel $article
     *
     * @return array
     */
    public function getCategoryIds(ArticleModel $article)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->articleCategoryTable,
            'category_id'
        )
        ->where(
            'article_id = ?',
            (int)$article->getId()
        );
        return $adapter->fetchCol($select);
    }

    /**
     * @param $category
     * @param $articles
     * @return $this
     */
    public function saveArticleCategoryRelation($category, $articles)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category->setIsChangedArticleList(false);
        $id = $category->getId();
        if ($articles === null) {
            return $this;
        }
        $oldArticleObjects = $this->articleCategory->getSelectedArticles($category);
        if (!is_array($oldArticleObjects)) {
            $oldArticleObjects = array();
        }
        $oldArticles = [];
        foreach ($oldArticleObjects as $article) {
            /** @var \Gemtoo\Blog\Model\Article $article */
            $oldArticles[$article->getId()] = $article->getPosition();
        }
        $insert = array_diff_key($articles, $oldArticles);
        $delete = array_diff_key($oldArticles, $articles);
        $update = array_intersect_key($articles, $oldArticles);
        $update = array_diff_assoc($update, $oldArticles);


        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = array('article_id IN(?)' => array_keys($delete), 'article_id=?' => $id);
            $adapter->delete($this->articleCategoryTable, $condition);
        }
        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $articleId => $position) {
                $data[] = [
                    'category_id' => (int)$id,
                    'article_id' => (int)$articleId,
                    'position' => (int)$position
                ];
            }
            $adapter->insertMultiple($this->articleCategoryTable, $data);
        }

        if (!empty($update)) {
            foreach ($update as $articleId => $position) {
                $where = ['category_id = ?' => (int)$id, 'article_id = ?' => (int)$articleId];
                $bind = ['position' => (int)$position];
                $adapter->update($this->articleCategoryTable, $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $articleIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'gemtoo_blog_category_change_articles',
                array('category' => $category, 'article_ids' => $articleIds)
            );
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $category->setIsChangedArticleList(true);
            $articleIds = array_keys($insert + $delete + $update);
            $category->setAffectedArticleIds($articleIds);
        }
        return $this;
    }

}
