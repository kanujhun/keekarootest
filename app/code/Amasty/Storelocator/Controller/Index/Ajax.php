<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Index;

class Ajax  extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    private $dataHelper;

    /**
     * Ajax constructor.
     *
     * @param \Magento\Framework\App\Action\Context     $context
     * @param \Magento\Framework\Json\EncoderInterface  $jsonEncoder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        $this->_objectManager = $context->getObjectManager();
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $locationCollection = $this->_objectManager->get('Amasty\Storelocator\Model\Location')->getCollection();

        $locationCollection->applyDefaultFilters();

        $productId = $this->getRequest()->getParam('product');

        $product = false;

        if ($productId) {
            $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
        }

        $locationCollection->load();

        $this->_view->loadLayout();
        $left = $this->_view->getLayout()->getBlock('amlocatorAjax')->toHtml();

        $arrayCollection = [];

        foreach ($locationCollection as $item) {
            if ($product) {
                $valid = $this->dataHelper->validateLocation($item, $product);
                if (!$valid) {
                    continue;
                }
            }
            $arrayCollection['items'][] = $item->getData();
        }

        $arrayCollection['totalRecords'] = isset($arrayCollection['items']) ? count($arrayCollection['items']) : 0;

        $res = array_merge_recursive(
            $arrayCollection, array('block' => $left)
        );

        $json = $this->_jsonEncoder->encode($res);

        $this->getResponse()->setBody($json);
    }
}
