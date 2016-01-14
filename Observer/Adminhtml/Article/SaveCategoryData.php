<?php
namespace Gemtoo\Blog\Observer\Adminhtml\Article;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Gemtoo\Blog\Model\ResourceModel\Article;

class SaveProductData extends Catalog implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $post = $this->context->getRequest()->getPostValue('category_gemtoo_blog_articles', -1);
        if ($post != '-1') {
            $post = json_decode($post, true);
            $category = $this->coreRegistry->registry('category');
            $this->articleResource->saveArticleCategoryRelation($category, $post);
        }
        return $this;
    }
}
