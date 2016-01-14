<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Catalog\Category;

use Magento\Catalog\Controller\Adminhtml\Category;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Class ArticlesGrid
 * @package Gemtoo\Blog\Controller\Adminhtml\Catalog\Category
 */
class ArticlesGrid extends Category
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * ArticlesGrid constructor.
     * @param Context $context
     * @param RedirectFactory $resultRedirectFactory
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory,
        LayoutFactory $resultLayoutFactory
    )
    {
        parent::__construct($context, $resultRedirectFactory);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return mixed
     */
    public function execute(){

        $this->_initCategory();
        $resultLayout = $this->resultLayoutFactory->create();
        $articlesBlock = $resultLayout->getLayout()->getBlock('category.gemtoo_blog.article.grid');

        if ($articlesBlock) {
            $articlesBlock->setCategoryArticles($this->getRequest()->getPost('category_gemtoo_blog_articles', null));
        }
        return $resultLayout;
    }
}
