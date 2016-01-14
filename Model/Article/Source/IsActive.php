<?php
namespace Gemtoo\Blog\Model\Article\Source;

use Magento\Framework\Option\ArrayInterface;
use Gemtoo\Blog\Model\Article;

class IsActive implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Article::STATUS_ENABLED,
                'label' => __('Yes')
            ],[
                'value' => Article::STATUS_DISABLED,
                'label' => __('No')
            ],
        ];
    }

    /**
     * get options as key value pair
     *
     * @return array
     */
    public function getOptions()
    {
        $_tmpOptions = $this->toOptionArray();
        $_options = [];
        foreach ($_tmpOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
}
