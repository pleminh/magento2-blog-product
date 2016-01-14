<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Article;

use Gemtoo\Blog\Controller\Adminhtml\Article as ArticleController;
use Magento\Framework\Registry;
use Gemtoo\Blog\Model\ArticleFactory;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

/**
 * Class Edit
 * @package Gemtoo\Blog\Controller\Adminhtml\Article
 */
class Edit extends ArticleController
{
    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param ArticleFactory $articleFactory
     * @param BackendSession $backendSession
     * @param RedirectFactory $resultRedirectFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        ArticleFactory $articleFactory,
        BackendSession $backendSession,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context

    )
    {
        $this->backendSession = $backendSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($registry, $articleFactory, $resultRedirectFactory, $dateFilter, $context);
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Gemtoo_Blog::article');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('article_id');

        $article = $this->initArticle();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Gemtoo_Blog::article');
        $resultPage->getConfig()->getTitle()->set((__('Articles')));

        if ($id) {
            $article->load($id);
            if (!$article->getId()) {
                $this->messageManager->addError(__('This article no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'gemtoo_blog/*/edit',
                    [
                        'article_id' => $article->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }

        $title = $article->getId() ? $article->getName() : __('New Article');
        $resultPage->getConfig()->getTitle()->append($title);
        $data = $this->backendSession->getData('gemtoo_blog_article_data', true);

        if (!empty($data)) {
            $article->setData($data);
        }

        return $resultPage;
    }
}
