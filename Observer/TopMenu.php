<?php
namespace Gemtoo\Blog\Observer;

use Gemtoo\Blog\Model\Article\Url as ArticleUrl;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class TopMenu
 * @package Gemtoo\Blog\Observer
 */
class TopMenu implements ObserverInterface
{
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var ArticleUrl
     */
    protected $articleUrl;

    /**
     * TopMenu constructor.
     * @param Http $request
     * @param ArticleUrl $articleUrl
     */
    public function __construct(
        Http $request,
        ArticleUrl $articleUrl
    )
    {
        $this->request = $request;
        $this->articleUrl = $articleUrl;
    }

    /**
     * @param $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $menu = $observer->getMenu();
        $tree = $menu->getTree();
        $fullAction = $this->request->getFullActionName();
        $selectedActions = ['gemtoo_blog_article_index', 'gemtoo_blog_article_view'];
        $articleNodeId = 'articles';

        $data = [
            'name'      => __('Blog'),
            'id'        => $articleNodeId,
            'url'       => $this->articleUrl->getListUrl(),
            'is_active' => in_array($fullAction, $selectedActions)
        ];
        $articlesNode = new Node($data, 'id', $tree, $menu);
        $menu->addChild($articlesNode);
        return $this;
    }
}
