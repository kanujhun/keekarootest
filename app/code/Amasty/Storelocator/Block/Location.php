<?php

namespace Amasty\Storelocator\Block;

/**
 * @author    Amasty Team
 * @copyright Copyright (c) 2014 Amasty (http://www.amasty.com)
 * @package   Amasty_Locator
 */
class Location extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * IO File
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_ioFile;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection
     */
    protected $attributeCollection;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $timezoneInterface;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * Location constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param \Magento\Framework\Registry                      $coreRegistry
     * @param \Magento\Framework\Json\EncoderInterface         $jsonEncoder
     * @param \Magento\Framework\Filesystem\Io\File            $ioFile
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection,
        \Amasty\Base\Model\Serializer $serializer,
        array $data
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_filesystem = $context->getFilesystem();
        $this->_jsonEncoder = $jsonEncoder;
        $this->_ioFile = $ioFile;
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->attributeCollection = $attributeCollection;
        $this->timezoneInterface = $context->getLocaleDate();
        $this->serializer = $serializer;
    }

    public function getLocationCollection()
    {
        if (!$this->_coreRegistry->registry('amlocator_location')) {
            /**
             * \Amasty\Storelocator\Model\Location $locationCollection
             */
            $locationCollection = $this->_objectManager->get('Amasty\Storelocator\Model\Location')->getCollection();
            $locationCollection->applyDefaultFilters();
            $locationCollection->load();
            $this->_coreRegistry->register('amlocator_location', $locationCollection);
        }

        return $this->_coreRegistry->registry('amlocator_location');
    }

    public function validateLocations($locationCollection, $product)
    {
        foreach ($locationCollection as $location) {
            $valid = $this->dataHelper->validateLocation($location, $product);
            if ($valid) {
                return true;
            }
        }

        return false;
    }

    public function getBaloonTemplate()
    {
        $baloon = $this->_scopeConfig->getValue(
            'amlocator/locator/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $store_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $store_url =  $store_url . 'amasty/amlocator/';

        $baloon = str_replace(
            '{{photo}}',
            '<img src="' . $store_url . '{{photo}}">',
            $baloon
        );

        $attributeTemplate = $this->_scopeConfig->getValue(
            'amlocator/locator/attribute_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $this->_jsonEncoder->encode(array("baloon" => $baloon, "attributeTemplate" => $attributeTemplate));
    }

    public function getAmStoreMediaUrl()
    {
        $store_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $store_url =  $store_url . 'amasty/amlocator/';

        return $store_url;
    }

    public function getGeoUse()
    {
        $geoUse = $this->_scopeConfig->getValue(
            'amlocator/geoip/usebrowserip',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $geoUse;
    }

    public function getJsonLocations()
    {
        $locations = $this->getLocations();
        $locationArray = [];
        $locationArray['items'] = [];
        foreach ($locations as $location) {
            //$location->setData('schedule', $this->_jsonEncoder->encode($this->serializer->unserialize($location->getData('schedule'))));
            $locationArray['items'][] = $location->getData();
        }
        $locationArray['totalRecords'] = count($locationArray['items']);
        $store = $this->_storeManager->getStore(true)->getId();
        $locationArray['currentStoreId'] = $store;

        return $this->_jsonEncoder->encode($locationArray);
    }

    public function getLocations()
    {
        $locations = $this->getLocationCollection();
        $product = false;
        $locationsArray = [];
        $productId = $this->getRequest()->getParam('product');

        if ($productId) {
            $product = $this->getProductById($productId);
        }

        foreach($locations as $location) {
            $location->load($location->getId());
            $location->setData('schedule_array', $this->serializer->unserialize($location->getData('schedule')));
            //$location->setData('schedule', $this->_jsonEncoder->encode($this->serializer->unserialize($location->getData('schedule'))));
            if ($product) {
                if (!$this->dataHelper->validateLocation($location, $product)) {
                    continue;
                }
            }
            $locationsArray[] = $location;
        }

        return $locationsArray;
    }

    public function getApiKey($isClean = false)
    {
        $apiKey = $this->_scopeConfig->getValue(
            'amlocator/locator/api',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$isClean) {
            if ($apiKey != "") {
                $apiKey = "&key=" . $apiKey;
            } else {
                $apiKey = "";
            }
        }
        return $apiKey;
    }

    public function getDistanceConfig()
    {
        $distance = $this->_scopeConfig->getValue(
            'amlocator/locator/distance',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $distance == "choose" ? true : false;
    }

    public function getShowAttributes()
    {
        $showAttributes = $this->_scopeConfig->getValue(
            'amlocator/locator/show_attrs',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $showAttributes;
    }

    public function getLinkToMap($productId)
    {
        $link  = $this->getUrl(
            $this->_scopeConfig->getValue(
                'amlocator/general/url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            array('_query' => array("product" => $productId))
        );

        return $link;
    }

    public function getQueryString()
    {
        if ($this->getRequest()->getParam('product') !== null) {
            return '?' . http_build_query($this->getRequest()->getParams());
        }
        return '';
    }

    public function getProduct()
    {
        if ($this->_coreRegistry->registry('current_product')) {
            return $this->_coreRegistry->registry('current_product');
        }

        return false;
    }

    public function getProductById($productId)
    {
        $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);

        return $product;
    }

    public function getLinkText()
    {
        $linkText = $this->_scopeConfig->getValue(
            'amlocator/locator/linktext',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $linkText;
    }

    public function getTarget()
    {
        $target = '';

        $newPage = $this->_scopeConfig->getValue(
            'amlocator/locator/new_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($newPage) {
            $target = 'target="_blank"';
        }
        return $target;
    }

    public function getAttributes() {
        $collection = $this->attributeCollection
            ->joinAttributes();
        $attrAsArray = $collection->getAttributes();

        $storeId = $this->_storeManager->getStore(true)->getId();

        $attributes = [];

        foreach ($attrAsArray as $attribute) {
            $attributeId = $attribute['attribute_id'];
            if (!array_key_exists($attributeId, $attributes)) {
                $attrLabel = $attribute['frontend_label'];
                $labels = $this->serializer->unserialize($attribute['label_serialized']);
                if(isset($labels[$storeId]) && $labels[$storeId]) {
                    $attrLabel = $labels[$storeId];
                }
                $attributes[$attributeId] = [
                    'attribute_id' => $attributeId,
                    'label' => $attrLabel,
                    'options' => []
                ];
            }

            if ($attribute['frontend_input'] == 'boolean') {
                $attributes[$attributeId]['options'][0] = __('No');
                $attributes[$attributeId]['options'][1] = __('Yes');
            } else {
                $options = $this->serializer->unserialize($attribute['options_serialized']);
                $optionLabel = $options[0];
                if(isset($options[$storeId]) && $options[$storeId]) {
                    $optionLabel = $options[$storeId];
                }
                $attributes[$attributeId]['options'][$attribute['value_id']] = $optionLabel;
            }
        }

        return $attributes;
    }
}
