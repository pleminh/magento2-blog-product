<?php
namespace Gemtoo\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Gemtoo\Blog\Model\Article\Url;
use Gemtoo\Blog\Model\Article\Source\IsActive;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @method string getName()
 * @method Article setUpdatedAt(\string $date)
 * @method Article setCreatedAt(\string $date)
 * @method Article setUrlKey(\string $urlKey)
 * @method array getStores()
 * @method int getStoreId()
 * @method bool hasStores()
 * @method Article setStoreId(\int $storeId)
 * @method Article setImage(\string $image)
 * @method string getImage()
 * @method Article setFile(\string $file)
 * @method string getFile()
 * @method ResourceModel\Article _getResource()
 * @method ResourceModel\Article getResource()
 * @method string getUrlKey()
 * @method int getIsActive()
 * @method Article setIsActive(\bool $active)
 * @method string getContent()
 * @method string getDateOfPublication()
 * @method string getMetaTitle()
 * @method string getMetaDescription()
 * @method string getMetaKeywords()
 * @method Article setProductsData(array $products)
 * @method Article setIsChangedProductList(\bool $changed)
 * @method array|null getProductsData()
 * @method Article setAffectedProductIds(array $productIds)
 * @method int getPosition()
 * @method array getCategoriesIds()
 * @method Article setCategoriesIds(array $categoryIds)
 * @method Article setIsChangedCategoryList(\bool $changed)
 * @method Article setAffectedCategoryIds(array $categoryIds)
 */
class Article extends AbstractModel
{
    /**
     * status enabled
     *
     * @var int
     */
    const STATUS_ENABLED = 1;
    /**
     * status disabled
     *
     * @var int
     */
    const STATUS_DISABLED = 0;

    /**
     * @var Url
     */
    protected $urlModel;
    /**
     * cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'gemtoo_blog_article';

    /**
     * cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'gemtoo_blog_article';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'gemtoo_blog_article';

    /**
     * filter model
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var IsActive
     */
    protected $statusList;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $categoryCollection;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param FilterManager $filter
     * @param Url $urlModel
     * @param IsActive $statusList
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        FilterManager $filter,
        Url $urlModel,
        IsActive $statusList,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->filter                    = $filter;
        $this->urlModel                  = $urlModel;
        $this->statusList                = $statusList;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Gemtoo\Blog\Model\ResourceModel\Article');
    }

    /**
     * Check if article url key exists
     * return article id if article exists
     *
     * @param string $urlKey
     * @param int $storeId
     * @return int
     */
    public function checkUrlKey($urlKey, $storeId)
    {
        return $this->_getResource()->checkUrlKey($urlKey, $storeId);
    }

    /**
     * Prepare article's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return $this->statusList->getOptions();
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get default article values
     * @return array
     */
    public function getDefaultValues()
    {
        return [
            'is_active' => self::STATUS_ENABLED
        ];
    }

    /**
     * sanitize the url key
     *
     * @param $string
     * @return string
     */
    public function formatUrlKey($string)
    {
        return $this->filter->translitUrl($string);
    }

    /**
     * @return mixed
     */
    public function getArticleUrl()
    {
        return $this->urlModel->getArticleUrl($this);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getIsActive();
    }

    /**
     * @return array|mixed
     */
    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return array();
        }
        $array = $this->getData('products_position');
        if (is_null($array)) {
            $array = $this->getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }
        return $array;
    }

    /**
     * @param string $attributes
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getSelectedProductsCollection($attributes = '*')
    {
        if (is_null($this->productCollection)) {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect($attributes);
            $collection->joinField(
                'position',
                'gemtoo_blog_article_product',
                'position',
                'product_id=entity_id',
                '{{table}}.article_id='.$this->getId(),
                'inner'
            );
            $this->productCollection = $collection;
        }
        return $this->productCollection;
    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setData('category_ids', $ids);
        }
        return (array) $this->_getData('category_ids');
    }

    /**
     * @param string $attributes
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getSelectedCategoriesCollection($attributes = '*')
    {
        if (is_null($this->categoryCollection)) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->addAttributeToSelect($attributes);
            $collection->joinField(
                'position',
                'gemtoo_blog_article_category',
                'position',
                'category_id=entity_id',
                '{{table}}.article_id='.$this->getId(),
                'inner'
            );
            $this->categoryCollection = $collection;
        }
        return $this->categoryCollection;
    }
}
