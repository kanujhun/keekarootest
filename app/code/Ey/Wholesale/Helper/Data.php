<?php

namespace Ey\Wholesale\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $coreRegistry;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry
    )
    {
        $this->coreRegistry = $registry;
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