<?php
namespace Gemtoo\Blog\Block\Adminhtml\Article;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends FormContainer
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    )
    {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize article edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'article_id';
        $this->_blockGroup = 'Gemtoo_Blog';
        $this->_controller = 'adminhtml_article';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Article'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Article'));
    }

    /**
     * Retrieve text for header element depending on loaded article
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Gemtoo\Blog\Model\Article $article */
        $article = $this->coreRegistry->registry('gemtoo_blog_article');
        if ($article->getId()) {
            return __("Edit Article '%1'", $this->escapeHtml($article->getName()));
        }
        return __('New Article');
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('article_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'article_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'article_content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}
