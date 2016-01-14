<?php
namespace Gemtoo\Blog\Block\Article;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlFactory;
use Gemtoo\Blog\Model\ResourceModel\Article\CollectionFactory as ArticleCollectionFactory;

/**
 * @method \Gemtoo\Blog\Model\ResourceModel\Article\Collection getArticles()
 * @method ListArticle setArticles(\Gemtoo\Blog\Model\ResourceModel\Article\Collection $articles)
 */
class ListArticle extends Template
{
    /**
     * @var ArticleCollectionFactory
     */
    protected $articleCollectionFactory;
    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @param ArticleCollectionFactory $articleCollectionFactory
     * @param UrlFactory $urlFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ArticleCollectionFactory $articleCollectionFactory,
        UrlFactory $urlFactory,
        Context $context,
        array $data = []
    )
    {
        $this->articleCollectionFactory = $articleCollectionFactory;
        $this->urlFactory = $urlFactory;
        parent::__construct($context, $data);
    }

    /**
     * load the articles
     */
    protected  function _construct()
    {
        parent::_construct();
        /** @var \Gemtoo\Blog\Model\ResourceModel\Article\Collection $articles */
        $articles = $this->articleCollectionFactory->create()->addFieldToSelect('*')
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('name', 'ASC');
        $this->setArticles($articles);
    }

    /**
     * @return bool
     */
    public function isRssEnabled()
    {
        return
            $this->_scopeConfig->getValue('rss/config/active', ScopeInterface::SCOPE_STORE) &&
            $this->_scopeConfig->getValue('gemtoo_blog/article/rss', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'gemtoo_blog.article.list.pager');
        $pager->setCollection($this->getArticles());
        $this->setChild('pager', $pager);
        $this->getArticles()->load();
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getRssLink()
    {
        return $this->_urlBuilder->getUrl(
            'gemtoo_blog/article/rss',
            ['store' => $this->_storeManager->getStore()->getId()]
        );
    }
}
