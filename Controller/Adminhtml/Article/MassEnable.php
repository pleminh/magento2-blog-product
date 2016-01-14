<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Article;

/**
 * Class MassEnable
 * @package Gemtoo\Blog\Controller\Adminhtml\Article
 */
class MassEnable extends MassDisable
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 articles have been enabled';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while enabling articles.';
    /**
     * @var bool
     */
    protected $isActive = true;
}
