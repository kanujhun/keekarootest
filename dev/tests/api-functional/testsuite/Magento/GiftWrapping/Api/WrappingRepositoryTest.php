<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftWrapping\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Exception\NoSuchEntityException;

class WrappingRepositoryTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/gift-wrappings';
    const SERVICE_NAME = 'giftWrappingWrappingRepositoryV1';
    const SERVICE_VERSION = 'V1';

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var \Magento\GiftWrapping\Api\WrappingRepositoryInterface */
    private $wrappingRepository;

    /** @var string */
    private $testImagePath;

    /**
     * @var \Magento\GiftWrapping\Model\WrappingFactory
     */
    private $wrappingFactory;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->wrappingRepository =
            $this->objectManager->create('Magento\GiftWrapping\Api\WrappingRepositoryInterface');
        $this->searchCriteriaBuilder = $this->objectManager->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );
        $this->filterBuilder = $this->objectManager->create(
            'Magento\Framework\Api\FilterBuilder'
        );
        $this->wrappingFactory = $this->objectManager->create('Magento\GiftWrapping\Model\WrappingFactory');
        $this->testImagePath = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/_files/test_image.jpg');
    }

    /**
     * @magentoApiDataFixture Magento/GiftWrapping/_files/wrapping.php
     */
    public function testGet()
    {
        $wrapping = $this->getWrappingFixture();
        $data = $this->wrappingRepository->get($wrapping->getId());
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $wrapping->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['id' => $wrapping->getId()]);
        $expectedData = [
            'wrapping_id' => $data->getWrappingId(),
            'design' => $data->getDesign(),
            'status' => $data->getStatus(),
            'base_price' => $data->getBasePrice(),
            'image_name' => $data->getImageName(),
            'website_ids' => $data->getWebsiteIds(),
            'base_currency_code' => 'USD'
        ];
        foreach ($expectedData as $key => $value) {
            $resultValue = isset($result[$key]) ? $result[$key] : null;
            $this->assertEquals($value, $resultValue, "Assertion for property {$key} failed");
        }
        $this->assertStringStartsWith('http', $result['image_url'], 'Image URL property is incorrect');
    }

    /**
     * @magentoApiDataFixture Magento/GiftWrapping/_files/wrappings.php
     */
    public function testGetList()
    {
        $result = $this->callSearch([]);
        $this->assertArrayHasKey('items', $result);
        $collection = $this->getWrappingCollection();
        $this->assertCount($collection->count(), $result['items']);
        self::deleteAllFixtures();
    }

    /**
     * Perform search call to API
     *
     * @param array $filters
     * @return array|bool|float|int|string
     */
    private function callSearch(array $filters)
    {
        $storeId = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $filters[] = $this->filterBuilder->setField('store_id')->setValue($storeId)->create();
        $this->searchCriteriaBuilder->addFilters($filters);
        $searchData = $this->searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    public function testCreate()
    {
        $this->_markTestAsRestOnly('Fix inconsistencies in WSDL and Data interfaces');
        /** @var \Magento\GiftWrapping\Model\Wrapping $wrapping */
        $wrapping = $this->wrappingFactory->create();
        $wrapping->setWebsiteIds([1]);
        $wrapping->setStatus(1);
        $wrapping->setDesign('New Wrapping');
        $wrapping->setBasePrice(10.0);
        $wrapping->setImageName('image.jpg');
        $wrapping->setImageBase64Content(base64_encode(file_get_contents($this->testImagePath)));

        $wrappingModel = $this->callCreate($wrapping);
        $this->assertNotNull($wrappingModel['wrapping_id']);

        $actualModel = $this->wrappingRepository->get($wrappingModel['wrapping_id']);
        $this->assertEquals($wrappingModel['wrapping_id'], $actualModel->getWrappingId());
        $this->assertEquals($wrapping->getStatus(), $actualModel->getStatus());
        $this->assertEquals($wrapping->getBasePrice(), $actualModel->getBasePrice());
        $this->assertEquals($wrapping->getImageName(), $actualModel->getImageName());
        $this->assertEquals($wrapping->getDesign(), $actualModel->getDesign());
        $this->assertEquals($wrapping->getWebsiteIds(), $actualModel->getWebsiteIds());
        $this->assertStringStartsWith('http', $actualModel->getImageUrl(), 'Image URL property is incorrect');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The image content must be valid data.
     */
    public function testCreateImageValidationFailed()
    {
        $this->_markTestAsRestOnly('Fix inconsistencies in WSDL and Data interfaces');
        /** @var \Magento\GiftWrapping\Model\Wrapping $wrapping */
        $wrapping = $this->wrappingFactory->create();
        $wrapping->setWebsiteIds([1]);
        $wrapping->setStatus(1);
        $wrapping->setDesign('New Wrapping');
        $wrapping->setBasePrice(10.0);
        $wrapping->setImageName('image.jpg');
        $wrapping->setImageBase64Content('invalid base64 content');
        $this->assertNull($this->callCreate($wrapping));
    }

    /**
     * @magentoApiDataFixture Magento/GiftWrapping/_files/wrapping.php
     */
    public function testUpdate()
    {
        $this->_markTestAsRestOnly('Fix inconsistencies in WSDL and Data interfaces');
        $wrapping = $this->getWrappingFixture();

        /** @var \Magento\GiftWrapping\Model\Wrapping $wrapping */
        $wrappingModel = $this->wrappingFactory->create();
        $wrappingModel->setStatus(0);
        $wrappingModel->setDesign('Changed Wrapping');
        $wrappingModel->setBasePrice(50.0);
        $wrappingModel->setImageName('image_updated.jpg');
        $wrappingModel->setImageBase64Content(base64_encode(file_get_contents($this->testImagePath)));

        $updatedModel = $this->callUpdate($wrapping->getId(), $wrappingModel);
        $wrappingId = $updatedModel['wrapping_id'];
        $this->assertEquals($wrapping->getWrappingId(), $wrappingId);

        $actualDataObject = $this->wrappingRepository->get($wrappingId);
        $this->assertEquals($wrappingId, $actualDataObject->getWrappingId());
        $this->assertEquals($wrappingModel->getStatus(), $actualDataObject->getStatus());
        $this->assertEquals($wrappingModel->getBasePrice(), $actualDataObject->getBasePrice());
        $this->assertEquals($wrappingModel->getImageName(), $actualDataObject->getImageName());
        $this->assertEquals($wrappingModel->getDesign(), $actualDataObject->getDesign());
        $this->assertStringStartsWith('http', $actualDataObject->getImageUrl(), 'Image URL property is incorrect');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Gift Wrapping with specified ID
     */
    public function testUpdateNoSuchEntity()
    {
        /** @var \Magento\GiftWrapping\Model\Wrapping $wrapping */
        $wrappingModel = $this->wrappingFactory->create();
        $wrappingModel->setStatus(0);
        $wrappingModel->setDesign('Changed Wrapping');
        $wrappingModel->setBasePrice(50.0);
        $wrappingModel->setImageName('image_updated.jpg');
        $wrappingModel->setImageBase64Content(base64_encode(file_get_contents($this->testImagePath)));
        $this->assertNull($this->callUpdate(-1, $wrappingModel));
    }

    /**
     * @magentoApiDataFixture Magento/GiftWrapping/_files/wrapping.php
     */
    public function testDelete()
    {
        $wrapping = $this->getWrappingFixture();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $wrapping->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];
        $requestData = ['id' => $wrapping->getId()];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($result);

        try {
            $this->wrappingRepository->get($wrapping->getId());
            $this->fail("Gift Wrapping was not expected to be returned after being deleted.");
        } catch (NoSuchEntityException $e) {
            $this->assertStringStartsWith('Gift Wrapping with specified ID', $e->getMessage());
        }
    }

    /**
     * Perform create call to API
     *
     * @param Data\WrappingInterface $dataObject
     * @return array|bool|float|int|string
     */
    private function callCreate(\Magento\GiftWrapping\Api\Data\WrappingInterface $dataObject)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData = ['data' => $dataObject->getData()];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Perform update call to API
     *
     * @param int $id
     * @param Data\WrappingInterface $dataObject
     * @return array|bool|float|int|string
     */
    private function callUpdate($id, \Magento\GiftWrapping\Api\Data\WrappingInterface $dataObject)
    {
        $dataObject->setWrappingId($id);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $id,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData = ['data' => $dataObject->getData()];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Return collection of wrapping items sorted by ID descending
     *
     * @return \Magento\GiftWrapping\Model\ResourceModel\Wrapping\Collection
     */
    private function getWrappingCollection()
    {
        /** @var \Magento\GiftWrapping\Model\ResourceModel\Wrapping\Collection $collection */
        $collection = $this->objectManager->create('Magento\GiftWrapping\Model\ResourceModel\Wrapping\Collection');
        $collection->setOrder('wrapping_id');
        return $collection;
    }

    /**
     * Return last created wrapping fixture
     *
     * @return \Magento\GiftWrapping\Model\Wrapping
     */
    private function getWrappingFixture()
    {
        $collection = $this->getWrappingCollection();
        $collection->setPageSize(1);
        $collection->load();
        return $collection->fetchItem();
    }

    private static function deleteAllFixtures()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\GiftWrapping\Model\ResourceModel\Wrapping\Collection');
        foreach ($collection as $item) {
            /** @var \Magento\GiftWrapping\Model\Wrapping $item */
            $item->delete();
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::deleteAllFixtures();
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\GiftWrapping\Model\ResourceModel\Wrapping\Collection');
        foreach ($collection as $item) {
            /** @var \Magento\GiftWrapping\Model\Wrapping $item */
            $item->delete();
        }
    }
}
