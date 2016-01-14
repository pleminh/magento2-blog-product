<?php
namespace Gemtoo\Blog\Model\Article;

use Gemtoo\Blog\Model\ResourceModel\Article\CollectionFactory;
use Magento\Catalog\Model\Category as CategoryModel;

class Category
{
    /**
     * @var null|\Gemtoo\Blog\Model\ArticleFactory
     */
    protected $articleCollectionFactory;

    /**
     * @param CollectionFactory $articleCollectionFactory
     */
    public function __construct(
        CollectionFactory $articleCollectionFactory
    )
    {
        $this->articleCollectionFactory = $articleCollectionFactory;
    }

    /**
     * @access public
     * @param \Magento\Catalog\Model\Category $category
     * @return mixed
     */
    public function getSelectedArticles(CategoryModel $category)
    {
        if (!$category->hasSelectedArticles()) {
            $articles = [];
            foreach ($this->getSelectedArticlesCollection($category) as $article) {
                $articles[] = $article;
            }
            $category->setSelectedArticles($articles);
        }
        return $category->getData('selected_articles');
    }

    /**
     * @access public
     * @param \Magento\Catalog\Model\Category $category
     * @return \Gemtoo\Blog\Model\ResourceModel\Article\Collection
     */
    public function getSelectedArticlesCollection(CategoryModel $category)
    {
        $collection = $this->articleCollectionFactory->create()
            ->addCategoryFilter($category);
        return $collection;
    }
}
