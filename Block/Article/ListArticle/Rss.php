<?php
namespace Gemtoo\Blog\Block\Article\ListArticle;

use Magento\Framework\View\Element\AbstractBlock;
use Gemtoo\Blog\Model\Article\Rss as RssModel;
use Gemtoo\Blog\Model\Article\Url;
use Magento\Framework\View\Element\Context;
use Gemtoo\Blog\Model\ResourceModel\Article\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Store\Model\ScopeInterface;

class Rss extends AbstractBlock implements DataProviderInterface
{
    /**
     * @var string
     */
    const CACHE_LIFETIME_CONFIG_PATH = 'gemtoo_blog/article/rss_cache';

    /**
     * @var \Gemtoo\Blog\Model\Article\Rss
     */
    protected $rssModel;

    /**
     * @var \Gemtoo\Blog\Model\Article\Url
     */
    protected $urlModel;

    /**
     * @var \Gemtoo\Blog\Model\ResourceModel\Article\CollectionFactory
     */
    protected $articleCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param RssModel $rssModel
     * @param Url $urlModel
     * @param CollectionFactory $articleCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        RssModel $rssModel,
        Url $urlModel,
        CollectionFactory $articleCollectionFactory,
        StoreManagerInterface $storeManager,
        Context $context,
        array $data = []
    )
    {
        $this->rssModel = $rssModel;
        $this->urlModel = $urlModel;
        $this->articleCollectionFactory = $articleCollectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * @return array
     */
    public function getRssData()
    {
        $url = $this->urlModel->getListUrl();
        $data = ['title' => __('Articles'), 'description' => __('Articles'), 'link' => $url, 'charset' => 'UTF-8'];


        $collection = $this->articleCollectionFactory->create();
        $collection->addStoreFilter($this->getStoreId());
        $collection->addFieldToFilter('is_active', 1); //TODO: use constant
        $collection->addFieldToFilter('in_rss', 1); //TODO: use constant
        foreach ($collection as $item) {
            /** @var \Gemtoo\Blog\Model\Article $item */
            //TODO: add more attributes to RSS
            $description = '<table><tr><td><a href="%s">%s</a></td></tr></table>';
            $description = sprintf($description, $item->getArticleUrl(), $item->getName());
            $data['entries'][] = [
                'title' => $item->getName(),
                'link' => $item->getArticleUrl(),
                'description' => $description,
            ];
        }
        return $data;
    }

    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     */
    public function isAllowed()
    {
        return $this->rssModel->isRssEnabled();
    }

    /**
     * Get information about all feeds this Data Provider is responsible for
     *
     * @return array
     */
    public function getFeeds()
    {
        $feeds = [];
        $feeds[] = [
            'label' => __('Articles'),
            'link' => $this->rssModel->getRssLink(),
        ];
        $result = ['group' => __('Blog'), 'feeds' => $feeds];
        return $result;
    }

    /**
     * @return bool
     */
    public function isAuthRequired()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        $lifetime = $this->_scopeConfig->getValue(
            self::CACHE_LIFETIME_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
        return $lifetime ?: null;
    }
}
