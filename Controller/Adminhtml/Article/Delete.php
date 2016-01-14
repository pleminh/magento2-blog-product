<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Article;

use Gemtoo\Blog\Controller\Adminhtml\Article;

/**
 * Class Delete
 * @package Gemtoo\Blog\Controller\Adminhtml\Article
 */
class Delete extends Article
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('article_id');
        if ($id) {
            $name = '';
            try {
                $article = $this->articleFactory->create();
                $article->load($id);
                $name = $article->getName();
                $article->delete();
                $this->messageManager->addSuccess(__('The article has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_gemtoo_blog_article_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('gemtoo_blog/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_gemtoo_blog_article_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                $this->messageManager->addError($e->getMessage());
                $resultRedirect->setPath('gemtoo_blog/*/edit', ['article_id' => $id]);
                return $resultRedirect;
            }
        }

        $this->messageManager->addError(__('We can\'t find an article to delete.'));

        $resultRedirect->setPath('gemtoo_blog/*/');
        return $resultRedirect;
    }
}
