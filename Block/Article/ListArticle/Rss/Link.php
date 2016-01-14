<?php
namespace Gemtoo\Blog\Block\Article\ListArticle\Rss;

use Magento\Framework\View\Element\Template;
use Gemtoo\Blog\Model\Article\Rss as RssModel;
use Magento\Framework\View\Element\Template\Context;

class Link extends Template
{
    protected $rssModel;

    /**
     * @param RssModel $rssModel
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        RssModel $rssModel,
        Context $context,
        array $data = []
    ) {
        $this->rssModel = $rssModel;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function isRssEnabled()
    {
        return $this->rssModel->isRssEnabled();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Subscribe to RSS Feed');
    }
    /**
     * @return string
     */
    public function getLink()
    {
        return $this->rssModel->getRssLink();
    }
}
