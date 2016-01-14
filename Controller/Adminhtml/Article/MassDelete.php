<?php
namespace Gemtoo\Blog\Controller\Adminhtml\Article;

use Gemtoo\Blog\Model\Article;

/**
 * Class MassDelete
 * @package Gemtoo\Blog\Controller\Adminhtml\Article
 */
class MassDelete extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 record(s) have been deleted';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while deleting record(s).';

    /**
     * @param Article $article
     * @return $this
     */
    protected function doTheAction(Article $article)
    {
        $article->delete();
        return $this;
    }
}
