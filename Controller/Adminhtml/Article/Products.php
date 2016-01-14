<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Article;

use Gemtoo\Blog\Controller\Adminhtml\Article;
use Magento\Framework\Registry;
use Gemtoo\Blog\Model\ArticleFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

/**
 * Class Products
 * @package Gemtoo\Blog\Controller\Adminhtml\Article
 */
class Products extends Article
{

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Products constructor.
     * @param LayoutFactory $resultLayoutFactory
     * @param Registry $registry
     * @param ArticleFactory $articleFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        LayoutFactory $resultLayoutFactory,
        Registry $registry,
        ArticleFactory $articleFactory,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context
    )
    {
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($registry, $articleFactory, $resultRedirectFactory, $dateFilter, $context);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $this->initArticle();
        $resultLayout = $this->resultLayoutFactory->create();

        $productsBlock = $resultLayout->getLayout()->getBlock('article.edit.tab.product');
        if ($productsBlock) {
            $productsBlock->setArticleProducts($this->getRequest()->getPost('article_products', null));
        }
        return $resultLayout;
    }
}
