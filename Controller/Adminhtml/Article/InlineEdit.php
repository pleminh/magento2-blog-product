<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Article;

use Magento\Backend\App\Action\Context;
use Gemtoo\Blog\Model\ArticleFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Gemtoo\Blog\Controller\Adminhtml\Article as ArticleController;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Registry;
use Gemtoo\Blog\Model\Article;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

/**
 * Class InlineEdit
 * @package Gemtoo\Blog\Controller\Adminhtml\Article
 */
class InlineEdit extends ArticleController
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * InlineEdit constructor.
     * @param JsonFactory $jsonFactory
     * @param Registry $registry
     * @param ArticleFactory $articleFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Registry $registry,
        ArticleFactory $articleFactory,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context

    ) {
        $this->jsonFactory = $jsonFactory;
        parent::__construct($registry, $articleFactory, $resultRedirectFactory, $dateFilter, $context);

    }

    /**
     * @return mixed
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $articleId) {
            $article = $this->articleFactory->create()->load($articleId);
            try {
                $articleData = $this->filterData($postItems[$articleId]);
                $article->addData($articleData);

                $article->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithArticleId($article, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithArticleId($article, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithArticleId(
                    $article,
                    __('Something went wrong while saving the page.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param Article $article
     * @param $errorText
     * @return string
     */
    protected function getErrorWithArticleId(Article $article, $errorText)
    {
        return '[Article ID: ' . $article->getId() . '] ' . $errorText;
    }
}
