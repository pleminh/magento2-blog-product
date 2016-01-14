<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Article;

use Magento\Framework\Exception\LocalizedException;
use Gemtoo\Blog\Controller\Adminhtml\Article;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Gemtoo\Blog\Model\ArticleFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Ui\Component\MassAction\Filter;
use Gemtoo\Blog\Model\ResourceModel\Article\CollectionFactory;
use Gemtoo\Blog\Model\Article as ArticleModel;

/**
 * Class MassAction
 * @package Gemtoo\Blog\Controller\Adminhtml\Article
 */
abstract class MassAction extends Article
{
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var string
     */
    protected $successMessage = 'Mass Action successful on %1 records';
    /**
     * @var string
     */
    protected $errorMessage = 'Mass Action failed';

    /**
     * MassAction constructor.
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param ArticleFactory $articleFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        Registry $registry,
        ArticleFactory $articleFactory,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($registry, $articleFactory, $resultRedirectFactory, $dateFilter, $context);
    }

    /**
     * @param ArticleModel $article
     * @return mixed
     */
    protected abstract function doTheAction(ArticleModel $article);

    /**
     * @return mixed
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $article) {
                $this->doTheAction($article);
            }
            $this->messageManager->addSuccess(__($this->successMessage, $collectionSize));
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($this->errorMessage));
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('gemtoo_blog/*/index');
        return $redirectResult;
    }
}
