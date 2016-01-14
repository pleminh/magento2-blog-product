<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Catalog\Product;

use Magento\Catalog\Controller\Adminhtml\Product\Edit;

/**
 * Class Articles
 * @package Gemtoo\Blog\Controller\Adminhtml\Catalog\Product
 */
class Articles extends Edit
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Articles constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    )
    {
        parent::__construct($context, $productBuilder, $resultPageFactory, $resultRedirectFactory);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('id');
        $product = $this->productBuilder->build($this->getRequest());

        if ($productId && !$product->getId()) {
            $this->messageManager->addError(__('This product no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/*/');
        }

        $resultLayout = $this->resultLayoutFactory->create();
        $articlesBlock = $resultLayout->getLayout()->getBlock('gemtoo_blog.article');

        if ($articlesBlock) {
            $articlesBlock->setProductArticles($this->getRequest()->getPost('product_articles', null));
        }
        return $resultLayout;
    }
}
