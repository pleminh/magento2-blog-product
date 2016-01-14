<?php
namespace Gemtoo\Blog\Model\Article;

use Magento\Framework\UrlInterface;
use Gemtoo\Blog\Model\Article;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Url
 * @package Gemtoo\Blog\Model\Article
 */
class Url
{
    const LIST_URL_CONFIG_PATH = 'gemtoo_blog/article/list_url';
    const URL_PREFIX_CONFIG_PATH = 'gemtoo_blog/article/url_prefix';
    const URL_SUFFIX_CONFIG_PATH = 'gemtoo_blog/article/url_suffix';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Url constructor.
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getListUrl()
    {
        $sefUrl = $this->scopeConfig->getValue(self::LIST_URL_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        if ($sefUrl) {
            return $this->urlBuilder->getUrl('', ['_direct' => $sefUrl]);
        }
        return $this->urlBuilder->getUrl('gemtoo_blog/article/index');
    }

    /**
     * @param Article $article
     * @return mixed
     */
    public function getArticleUrl(Article $article)
    {
        if ($urlKey = $article->getUrlKey()) {
            $prefix = $this->scopeConfig->getValue(
                self::URL_PREFIX_CONFIG_PATH,
                ScopeInterface::SCOPE_STORE
            );
            $suffix = $this->scopeConfig->getValue(
                self::URL_SUFFIX_CONFIG_PATH,
                ScopeInterface::SCOPE_STORE
            );
            $path = (($prefix) ? $prefix . '/' : '').
                $urlKey .
                (($suffix) ? '.'. $suffix : '');
            return $this->urlBuilder->getUrl('', ['_direct'=>$path]);
        }
        return $this->urlBuilder->getUrl('gemtoo_blog/article/view', ['id' => $article->getId()]);
    }
}
