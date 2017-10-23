<?php

namespace Ey\Wholesale\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $coreRegistry;
//    protected $productRepo;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductRepository $productRepo
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry
//        \Magento\Catalog\Model\ProductRepository $productRepo
    )
    {
        $this->coreRegistry = $registry;
//        $this->productRepo = $productRepo;
        return parent::__construct($context);
    }

    /**
     * @param $xml_path
     * @param string $storeScope
     * @return mixed
     */
    protected function _getConfigValue($xml_path, $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($xml_path, $storeScope);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
       $xml_path = 'ey_product/bundle_product_list/enabled';
       return $this->_getConfigValue($xml_path) == '1' ? true:false;
    }

//    public function getOptionAttrs($id)
//    {
//        return $this->productRepo->getById($id);
//    }

    /**
     * @param $product
     */
    public function setBundleProduct($product)
    {
        $this->_resetProductRegistry();
        $this->coreRegistry->register('product', $product);
        $this->coreRegistry->register('current_product', $product);
    }

    /**
     * Un-register product
     */
    protected function _resetProductRegistry()
    {
        $this->coreRegistry->unregister('product');
        $this->coreRegistry->unregister('current_product');
    }
}