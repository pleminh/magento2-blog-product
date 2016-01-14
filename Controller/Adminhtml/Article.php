<?php
namespace Gemtoo\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Gemtoo\Blog\Model\ArticleFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

/**
 * Class Article
 * @package Gemtoo\Blog\Controller\Adminhtml
 */
abstract class Article extends Action
{
    /**
     * @var ArticleFactory
     */
    protected $articleFactory;
    /**
     * @var Registry
     */
    protected $coreRegistry;
    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;
    /**
     * @var Date
     */
    protected $dateFilter;

    /**
     * Article constructor.
     * @param Registry $registry
     * @param ArticleFactory $articleFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        ArticleFactory $articleFactory,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context

    )
    {
        $this->coreRegistry = $registry;
        $this->articleFactory = $articleFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->dateFilter = $dateFilter;
        parent::__construct($context);
    }

    /**
     * @return \Gemtoo\Blog\Model\Article
     */
    protected function initArticle()
    {
        $articleId  = (int) $this->getRequest()->getParam('article_id');
        $article    = $this->articleFactory->create();
        if ($articleId) {
            $article->load($articleId);
        }
        $this->coreRegistry->register('gemtoo_blog_article', $article);
        return $article;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function filterData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            ['dop' => $this->dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }

}
