<?php

namespace Ey\Wholesale\Block\Product\View\Type;

use Magento\Bundle\Model\Option;

class Bundle extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
{
    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->_coreRegistry->registry('product');
        if ($product && $product->getTypeInstance()->getStoreFilter($product) === null) {
            $product->getTypeInstance()->setStoreFilter($this->_storeManager->getStore(), $product);
        }
        return $product;
    }

    /**
     * Get html for option
     *
     * @param Option $option
     * @return string
     */
    public function getOptionHtml(Option $option)
    {
        if($option->getType() == 'radio'){
            $optionBlock = $this
                ->getLayout()
                ->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Radio');
        }
        if (!isset($optionBlock) || !$optionBlock) {
            return __('There is no defined renderer for "%1" option type.', $option->getType());
        }
        return $optionBlock->setOption($option)->toHtml();
    }
}