<?php
namespace Gemtoo\Blog\Model\Article;

use Gemtoo\Blog\Model\ResourceModel\Article\CollectionFactory;
use Magento\Catalog\Model\Product as ProductModel;

class Product
{
    /**
     * @var \Gemtoo\Blog\Model\ArticleFactory
     */
    protected $articleCollectionFactory;

    /**
     * @param CollectionFactory $articleCollectionFactory
     */
    public function __construct(CollectionFactory $articleCollectionFactory)
    {
        $this->articleCollectionFactory = $articleCollectionFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return \Gemtoo\Blog\Model\Article[]
     */
    public function getSelectedArticles(ProductModel $product)
    {
        if (!$product->hasSelectedArticles()) {
            $articles = [];
            foreach ($this->getSelectedArticlesCollection($product) as $article) {
                $articles[] = $article;
            }
            $product->setSelectedArticles($articles);
        }
        return $product->getData('selected_articles');
    }

    /**
     * @access public
     * @param \Magento\Catalog\Model\Product $product
     * @return \Gemtoo\Blog\Model\ResourceModel\Article\Collection
     */
    public function getSelectedArticlesCollection(ProductModel $product)
    {
        $collection = $this->articleCollectionFactory->create()
            ->addProductFilter($product);
        return $collection;
    }
}
