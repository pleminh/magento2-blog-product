<?php
namespace Gemtoo\Blog\Controller\Article;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Gemtoo\Blog\Model\ArticleFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Gemtoo\Blog\Model\Article\Url as UrlModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class View extends Action
{
    const BREADCRUMBS_CONFIG_PATH = 'gemtoo_blog/article/breadcrumbs';
    /**
     * @var \Gemtoo\Blog\Model\ArticleFactory
     */
    protected $articleFactory;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Gemtoo\Blog\Model\Article\Url
     */
    protected $urlModel;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param ArticleFactory $articleFactory
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param UrlModel $urlModel
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ArticleFactory $articleFactory,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        UrlModel $urlModel,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->articleFactory = $articleFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->urlModel = $urlModel;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $articleId = (int) $this->getRequest()->getParam('id');

        $article = $this->articleFactory->create();
        $article->load($articleId);

        if (!$article->isActive()) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $this->coreRegistry->register('current_article', $article);

        $resultPage = $this->resultPageFactory->create();
        $title = ($article->getMetaTitle()) ?: $article->getName();
        $resultPage->getConfig()->getTitle()->set($title);
        $resultPage->getConfig()->setDescription($article->getMetaDescription());
        $resultPage->getConfig()->setKeywords($article->getMetaKeywords());
        if ($this->scopeConfig->isSetFlag(self::BREADCRUMBS_CONFIG_PATH, ScopeInterface::SCOPE_STORE)) {
            /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbsBlock */
            $breadcrumbsBlock = $resultPage->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbsBlock) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'link'  => $this->_url->getUrl('')
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'articles',
                    [
                        'label' => __('Articles'),
                        'link'  => $this->urlModel->getListUrl()
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'article-'.$article->getId(),
                    [
                        'label' => $article->getName()
                    ]
                );
            }
        }

        return $resultPage;
    }
}
