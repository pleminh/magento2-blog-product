<?php
namespace Gemtoo\Blog\Block\Adminhtml\Article\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Config\Model\Config\Source\Yesno as BooleanOptions;

class Article extends GenericForm implements TabInterface
{
    /**
     * @var WysiwygConfig
     */
    protected $wysiwygConfig;

    /**
     * @var BooleanOptions
     */
    protected $booleanOptions;

    /**
     * @param WysiwygConfig $wysiwygConfig
     * @param Type $typeOptions
     * @param BooleanOptions $booleanOptions
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        WysiwygConfig $wysiwygConfig,
        BooleanOptions $booleanOptions,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    )
    {
        $this->wysiwygConfig    = $wysiwygConfig;
        $this->booleanOptions   = $booleanOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Gemtoo\Blog\Model\Article $article */
        $article = $this->_coreRegistry->registry('gemtoo_blog_article');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('article');
        $form->setFieldNameSuffix('article');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Article Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        $fieldset->addType('image', 'Gemtoo\Blog\Block\Adminhtml\Article\Helper\Image');
        $fieldset->addType('file', 'Gemtoo\Blog\Block\Adminhtml\Article\Helper\File');

        if ($article->getId()) {
            $fieldset->addField(
                'article_id',
                'hidden',
                ['name' => 'article_id']
            );
        }
        $fieldset->addField(
            'name',
            'text',
            [
                'name'      => 'name',
                'label'     => __('Name'),
                'title'     => __('Name'),
                'required'  => true,
            ]
        );
        $fieldset->addField(
            'url_key',
            'text',
            [
                'name'      => 'url_key',
                'label'     => __('URL Key'),
                'title'     => __('URL Key'),
            ]
        );
        if ($this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name'      => 'stores[]',
                    'value'     => $this->_storeManager->getStore(true)->getId()
                ]
            );
            $article->setStoreId($this->_storeManager->getStore(true)->getId());
        }
        $fieldset->addField(
            'is_active',
            'select',
            [
                'label'     => __('Is Active'),
                'title'     => __('Is Active'),
                'name'      => 'is_active',
                'required'  => true,
                'options'   => $article->getAvailableStatuses(),
            ]
        );
        $fieldset->addField(
            'in_rss',
            'select',
            [
                'label'     => __('Show in RSS'),
                'title'     => __('Show in RSS'),
                'name'      => 'in_rss',
                'required'  => true,
                'options'   => $this->booleanOptions->toArray()
            ]
        );
        $fieldset->addField(
            'content',
            'editor',
            [
                'name'      => 'content',
                'label'     => __('Content'),
                'title'     => __('Content'),
                'style'     => 'height:36em',
                'required'  => true,
                'config'    => $this->wysiwygConfig->getConfig()
            ]
        );
        $fieldset->addField(
            'dop',
            'date',
            [
                'name'        => 'dop',
                'label'       => __('Date of publication'),
                'title'       => __('Date of publication'),
                'image'       => $this->getViewFileUrl('images/grid-cal.png'),
                'date_format' => $this->_localeDate->getDateFormat(
                    \IntlDateFormatter::SHORT
                ),
                'class' => 'validate-date'
            ]
        );
        $fieldset->addField(
            'image',
            'image',
            [
                'name'        => 'image',
                'label'       => __('Image'),
                'title'       => __('Image'),
            ]
        );
        $fieldset->addField(
            'file',
            'file',
            [
                'name'        => 'file',
                'label'       => __('File'),
                'title'       => __('File'),
            ]
        );

        $articleData = $this->_session->getData('gemtoo_blog_article_data', true);
        if ($articleData) {
            $article->addData($articleData);
        } else {
            if (!$article->getId()) {
                $article->addData($article->getDefaultValues());
            }
        }
        $form->addValues($article->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Article');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
