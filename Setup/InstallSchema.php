<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Gemtoo\Blog\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (!$installer->tableExists('gemtoo_blog_article')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('gemtoo_blog_article'));
            $table->addColumn(
                    'article_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Article ID'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable'  => false,],
                    'Article Name'
                )
                ->addColumn(
                    'url_key',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable'  => false,],
                    'Article Url Key'
                )
                ->addColumn(
                    'content',
                    Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Article Content'
                )
                ->addColumn(
                    'dop',
                    Table::TYPE_DATE,
                    null,
                    [],
                    'Article Date'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Article Type'
                )
                ->addColumn(
                    'image',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Article Image'
                )
                ->addColumn(
                    'file',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Article File'
                )
                ->addColumn(
                    'meta_title',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Article Meta Title'
                )
                ->addColumn(
                    'meta_description',
                    Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Article Meta Description'
                )
                ->addColumn(
                    'meta_keywords',
                    Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Article Meta Keywords'
                )
                ->addColumn(
                    'is_active',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable'  => false,
                        'default'   => '1',
                    ],
                    'Is Article Active'
                )
                ->addColumn(
                    'in_rss',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable'  => false,
                        'default'   => '1',
                    ],
                    'Show in rss'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Update at'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Creation Time'
                )
                ->setComment('Blog articles');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('gemtoo_blog_article'),
                $setup->getIdxName(
                    $installer->getTable('gemtoo_blog_article'),
                    ['name','photo'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                [
                    'name',
                    'content',
                    'url_key',
                    'file',
                    'meta_title',
                    'meta_keywords',
                    'meta_description'
                ],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }

        //Create Articles to Store table
        if (!$installer->tableExists('gemtoo_blog_article_store')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('gemtoo_blog_article_store'));
            $table->addColumn(
                    'article_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'   => true,
                    ],
                    'Article ID'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned'  => true,
                        'nullable'  => false,
                        'primary'   => true,
                    ],
                    'Store ID'
                )
                ->addIndex(
                    $installer->getIdxName('gemtoo_blog_article_store', ['store_id']),
                    ['store_id']
                )
                ->addForeignKey(
                    $installer->getFkName('gemtoo_blog_article_store', 'article_id', 'gemtoo_blog_article', 'article_id'),
                    'article_id',
                    $installer->getTable('gemtoo_blog_article'),
                    'article_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('gemtoo_blog_article_store', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Article To Store Link Table');
            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('gemtoo_blog_article_product')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('gemtoo_blog_article_product'));
            $table->addColumn(
                    'article_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'   => true,
                    ],
                    'Article ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'   => true,
                    ],
                    'Product ID'
                )
                ->addColumn(
                    'position',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'default' => '0'
                    ],
                    'Position'
                )
                ->addIndex(
                    $installer->getIdxName('gemtoo_blog_article_product', ['product_id']),
                    ['product_id']
                )
                ->addForeignKey(
                    $installer->getFkName('gemtoo_blog_article_product', 'article_id', 'gemtoo_blog_article', 'article_id'),
                    'article_id',
                    $installer->getTable('gemtoo_blog_article'),
                    'article_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('gemtoo_blog_article_product', 'product_id', 'catalog_product_entity', 'entity_id'),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName(
                        'gemtoo_blog_article_product',
                        [
                            'article_id',
                            'product_id'
                        ],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'article_id',
                        'product_id'
                    ],
                    [
                        'type' => AdapterInterface::INDEX_TYPE_UNIQUE
                    ]
                )
                ->setComment('Article To product Link Table');
            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('gemtoo_blog_article_category')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('gemtoo_blog_article_category'));
            $table->addColumn(
                    'article_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'   => true,
                    ],
                    'Article ID'
                )
                ->addColumn(
                    'category_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'   => true,
                    ],
                    'Category ID'
                )
                ->addColumn(
                    'position',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'default' => '0'
                    ],
                    'Position'
                )
                ->addIndex(
                    $installer->getIdxName('gemtoo_blog_article_category', ['category_id']),
                    ['category_id']
                )
                ->addForeignKey(
                    $installer->getFkName('gemtoo_blog_article_category', 'article_id', 'gemtoo_blog_article', 'article_id'),
                    'article_id',
                    $installer->getTable('gemtoo_blog_article'),
                    'article_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('gemtoo_blog_article_category', 'category_id', 'catalog_category_entity', 'entity_id'),
                    'category_id',
                    $installer->getTable('catalog_category_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName(
                        'gemtoo_blog_article_category',
                        [
                            'article_id',
                            'category_id'
                        ],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'article_id',
                        'category_id'
                    ],
                    [
                        'type' => AdapterInterface::INDEX_TYPE_UNIQUE
                    ]
                )
                ->setComment('Article To category Link Table');
            $installer->getConnection()->createTable($table);
        }
    }
}
