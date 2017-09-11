<?php

namespace Ey\BundleQuantity\Block\Cart;

class AbstractCart
{
    /**
     * @param \Magento\Checkout\Block\Cart\AbstractCart $subject
     * @param $result
     * @return mixed
     */
    public function afterGetItemRenderer(\Magento\Checkout\Block\Cart\AbstractCart $subject, $result)
    {
        $result->setTemplate('Ey_BundleQuantity::cart/item/default.phtml');
        return $result;
    }
}