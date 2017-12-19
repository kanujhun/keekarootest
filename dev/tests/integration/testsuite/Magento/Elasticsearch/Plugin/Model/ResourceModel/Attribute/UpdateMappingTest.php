<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Plugin\Model\ResourceModel\Attribute;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\Request\Builder as RequestBuilder;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests Magento\Elasticsearch\Plugin\Model\ResourceModel\Attribute\UpdateMapping.
 *
 * @magentoDbIsolation disabled
 */
class UpdateMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Search\Request\Config $config */
        $config = $this->objectManager->create(\Magento\Framework\Search\Request\Config::class);
        $this->requestBuilder = $this->objectManager->create(
            \Magento\Framework\Search\Request\Builder::class,
            ['config' => $config]
        );

        $this->adapter = $this->objectManager->create(\Magento\Elasticsearch\SearchAdapter\Adapter::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->attributeRepository = $this->objectManager->create(ProductAttributeRepositoryInterface::class);
    }

    /**
     * @return void
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix attribute_test
     * @magentoDataFixture Magento/Elasticsearch/_files/product_searchable_text_attribute.php
     */
    public function testSearchableTextAttributeSave()
    {
        $attributeCode = 'text_attribute';
        $attributeValue = 'London';

        $product = $this->updateProduct($attributeCode, $attributeValue);
        $result = $this->searchByAttribute($attributeValue);

        self::assertCount(1, $result);
        self::assertContains($product->getId(), $result, 'Product not found by attribute.');
    }

    /**
     * @return void
     *
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix attribute_test
     * @magentoDataFixture Magento/Elasticsearch/_files/product_searchable_select_attribute.php
     */
    public function testSearchableSelectAttributeSave()
    {
        $attributeCode = 'select_attribute';
        $attributeValue = 'Select_Option_2';

        $product = $this->updateProduct($attributeCode, $attributeValue);
        $result = $this->searchByAttribute($attributeValue);

        self::assertCount(1, $result);
        self::assertContains($product->getId(), $result, 'Product not found by attribute.');
    }

    /**
     * Execute search query.
     *
     * @return \Magento\Framework\Search\Response\QueryResponse
     */
    private function executeQuery()
    {
        /** @var \Magento\Framework\Search\RequestInterface $queryRequest */
        $queryRequest = $this->requestBuilder->create();
        $queryResponse = $this->adapter->query($queryRequest);

        return $queryResponse;
    }

    /**
     * Returns document ids from query response.
     *
     * @param \Magento\Framework\Search\Response\QueryResponse $queryResponse
     * @return array
     */
    protected function getProductIds(\Magento\Framework\Search\Response\QueryResponse $queryResponse)
    {
        $productIds = [];
        foreach ($queryResponse as $document) {
            /** @var \Magento\Framework\Api\Search\Document $document */
            $productIds[] = $document->getId();
        }

        return $productIds;
    }

    /**
     * Performs product search by attribute value.
     *
     * @param string $searchTerm
     * @return array
     */
    private function searchByAttribute($searchTerm)
    {
        $this->requestBuilder->bind('search_term', $searchTerm);
        $this->requestBuilder->setRequestName('quick_search_container');

        $queryResponse = $this->executeQuery();

        return $this->getProductIds($queryResponse);
    }

    /**
     * Updates product attribute value.
     *
     * @param string $attributeCode
     * @param string $attributeValue
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    private function updateProduct($attributeCode, $attributeValue)
    {
        $product = $this->productRepository->get('simple', false, null, true);
        $attribute = $this->attributeRepository->get($attributeCode);
        $productValue = $attributeValue;
        if ($attribute->usesSource()) {
            $productValue = $attribute->getSource()->getOptionId($attributeValue);
        }

        $product->setData($attributeCode, $productValue);

        return $this->productRepository->save($product);
    }
}
