<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Article;

use Gemtoo\Blog\Model\Article;

/**
 * Class MassDisable
 * @package Gemtoo\Blog\Controller\Adminhtml\Article
 */
class MassDisable extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 articles have been disabled';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while disabling articles.';
    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @param Article $article
     * @return $this
     */
    protected function doTheAction(Article $article)
    {
        $article->setIsActive($this->isActive);
        $article->save();
        return $this;
    }
}
