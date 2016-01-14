<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Article;

use Magento\Framework\Registry;
use Gemtoo\Blog\Controller\Adminhtml\Article;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Gemtoo\Blog\Model\ArticleFactory;
use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Gemtoo\Blog\Model\Article\Image as ImageModel;
use Gemtoo\Blog\Model\Article\File as FileModel;
use Gemtoo\Blog\Model\Upload;
use Magento\Backend\Helper\Js as JsHelper;

/**
 * Class Save
 * @package Gemtoo\Blog\Controller\Adminhtml\Article
 */
class Save extends Article
{
    /**
     * @var \Gemtoo\Blog\Model\ArticleFactory
     */
    protected $articleFactory;
    /**
     * @var \Gemtoo\Blog\Model\Article\Image
     */
    protected $imageModel;
    /**
     * @var \Gemtoo\Blog\Model\Article\File
     */
    protected $fileModel;
    /**
     * @var \Gemtoo\Blog\Model\Upload
     */
    protected $uploadModel;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;


    /**
     * Save constructor.
     * @param JsHelper $jsHelper
     * @param ImageModel $imageModel
     * @param FileModel $fileModel
     * @param Upload $uploadModel
     * @param Registry $registry
     * @param ArticleFactory $articleFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        JsHelper $jsHelper,
        ImageModel $imageModel,
        FileModel $fileModel,
        Upload $uploadModel,
        Registry $registry,
        ArticleFactory $articleFactory,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context
    )
    {
        $this->jsHelper = $jsHelper;
        $this->imageModel = $imageModel;
        $this->fileModel = $fileModel;
        $this->uploadModel = $uploadModel;
        parent::__construct($registry, $articleFactory, $resultRedirectFactory, $dateFilter, $context);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Model\Exception
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('article');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->filterData($data);
            $article = $this->initArticle();
            $article->setData($data);
            $image = $this->uploadModel->uploadFileAndGetName('image', $this->imageModel->getBaseDir(), $data);
            $article->setImage($image);
            $file = $this->uploadModel->uploadFileAndGetName('file', $this->fileModel->getBaseDir(), $data);
            $article->setFile($file);
            $products = $this->getRequest()->getPost('products', -1);
            if ($products != -1) {
                $article->setProductsData($this->jsHelper->decodeGridSerializedInput($products));
            }
            $this->_eventManager->dispatch(
                'gemtoo_blog_article_prepare_save',
                [
                    'article' => $article,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $article->save();
                $this->messageManager->addSuccess(__('The article has been saved.'));
                $this->_getSession()->setGemtooBlogArticleData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'gemtoo_blog/*/edit',
                        [
                            'article_id' => $article->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('gemtoo_blog/*/');
                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the article.'));
            }

            $this->_getSession()->setGemtooBlogArticleData($data);
            $resultRedirect->setPath(
                'gemtoo_blog/*/edit',
                [
                    'article_id' => $article->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('gemtoo_blog/*/');
        return $resultRedirect;
    }
}
