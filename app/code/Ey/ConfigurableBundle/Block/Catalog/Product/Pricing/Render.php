<?php

namespace Ey\ConfigurableBundle\Block\Catalog\Product\Pricing;

class Render extends \Magento\Catalog\Pricing\Render
{
    /**
     * Produce and return block's html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        return str_replace('data-price-type="finalPrice"', '', parent::_toHtml());
    }

    /**
     * Returns saleable item instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct()
    {
        $parentBlock = $this->getParentBlock();

        $product = $parentBlock && $parentBlock->getProduct()
            ? $parentBlock->getProduct()
            : $this->registry->registry('product');
        return $product;
    }
}