<?php
namespace Gemtoo\Blog\Observer\Adminhtml\Article;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Gemtoo\Blog\Model\ResourceModel\Article;

abstract class Catalog implements ObserverInterface
{
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var Article
     */
    protected $articleResource;
    /**
     * @var Registry
     */
    protected $coreRegistry;
    /**
     * @var JsHelper
     */
    protected $jsHelper;

    /**
     * @param Context $context
     * @param Article $articleResource
     * @param JsHelper $jsHelper
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Article $articleResource,
        JsHelper $jsHelper,
        Registry $coreRegistry
    )
    {
        $this->context        = $context;
        $this->articleResource = $articleResource;
        $this->jsHelper       = $jsHelper;
        $this->coreRegistry   = $coreRegistry;
    }
}
