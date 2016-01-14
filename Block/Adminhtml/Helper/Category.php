<?php
namespace Gemtoo\Blog\Block\Adminhtml\Helper;

use Magento\Framework\Data\Form\Element\Multiselect;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Backend\Helper\Data as DataHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Escaper;


/**
 * Class Category
 * @package Gemtoo\Blog\Block\Adminhtml\Helper
 */
class Category extends Multiselect {
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var DataHelper
     */
    protected $backendData;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;


    /**
     * Category constructor.
     * @param CollectionFactory $collectionFactory
     * @param DataHelper $backendData
     * @param LayoutInterface $layout
     * @param EncoderInterface $jsonEncoder
     * @param AuthorizationInterface $authorization
     * @param ElementFactory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DataHelper $backendData,
        LayoutInterface $layout,
        EncoderInterface $jsonEncoder,
        AuthorizationInterface $authorization,
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->backendData = $backendData;
        $this->layout = $layout;
        $this->jsonEncoder = $jsonEncoder;
        $this->authorization = $authorization;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

    }

    /**
     * @return bool
     */
    public function getNoDisplay()
    {
        $isNotAllowed = !$this->authorization->isAllowed('Magento_Catalog::categories');
        return $this->getData('no_display') || $isNotAllowed;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        $collection = $this->_getCategoriesCollection();
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode(',', $values);
        }
        $collection->addAttributeToSelect('name');
        $collection->addIdFilter($values);
        $options = [];
        foreach ($collection as $category) {
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }
        return $options;
    }

    /**
     * @return mixed
     */
    protected function _getCategoriesCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return string
     */
    public function getAfterElementHtml()
    {
        if (!$this->isAllowed()) {
            return '';
        }
        $htmlId = $this->getHtmlId();
        $suggestPlaceholder = __('start typing to search category');
        $selectorOptions = $this->jsonEncoder->encode($this->_getSelectorOptions());
        $newCategoryCaption = __('New Category');

        $button = $this->layout->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'add_category_button',
                'label' => $newCategoryCaption,
                'title' => $newCategoryCaption,
                'onclick' => 'jQuery("#new-category").modal("openModal")',
                'disabled' => $this->getDisabled(),
            ]
        );
        $return = <<<HTML
    <input id="{$htmlId}-suggest" placeholder="$suggestPlaceholder" />
    <script>
        require(["jquery", "mage/mage"], function($){
            $('#{$htmlId}-suggest').mage('treeSuggest', {$selectorOptions});
        });
    </script>
HTML;
        return $return . $button->toHtml();
    }

    /**
     * @return array
     */
    protected function _getSelectorOptions()
    {
        return array(
            'source' => $this->backendData->getUrl('catalog/category/suggestCategories'),
            'valueField' => '#' . $this->getHtmlId(),
            'className' => 'category-select',
            'multiselect' => true,
            'showAll' => true
        );
    }

    /**
     * @return mixed
     */
    protected function isAllowed()
    {
        return $this
            ->authorization
            ->isAllowed('Magento_Catalog::categories');
    }
}
