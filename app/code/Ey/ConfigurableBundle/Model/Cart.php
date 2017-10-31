<?php

namespace Ey\ConfigurableBundle\Model;

class Cart extends \Magento\Checkout\Model\Cart
{
    /**
     * Add product to shopping cart (quote)
     *
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addProduct($productInfo, $requestInfo = null)
    {
        $isConfigurableBundleOnly = false;
        if(
            array_key_exists('configurable_bundle_option', $requestInfo) &&
            array_key_exists('configurable_bundle_product', $requestInfo) &&
            array_key_exists('configurable_bundle_super_attribute', $requestInfo) &&
            array_key_exists('configurable_bundle_qty', $requestInfo)
        ){
            $storeId = $this->_storeManager->getStore()->getStoreId();
            /** add configurable bundle to the cart */
            foreach ($requestInfo['configurable_bundle_option'] as $key => $id){
                $configurableParams['product'] = $requestInfo['configurable_bundle_product'][$key];
                $configurableParams['uenc'] = $requestInfo['uenc'];
                $configurableParams['form_key'] = $requestInfo['form_key'];
                $configurableParams['super_attribute'] = $requestInfo['configurable_bundle_super_attribute'][$key];
                $configurableParams['qty'] = $requestInfo['configurable_bundle_qty'][$key];
                $product = $this->productRepository->getById(
                    $configurableParams['product'],
                    false,
                    $storeId
                );
                $this->addProduct($product, $configurableParams);
            }

            if(!array_key_exists('bundle_option', $requestInfo) ||
                !array_key_exists('bundle_option_qty', $requestInfo)){
                $isConfigurableBundleOnly = true;
            }
        }

        if($isConfigurableBundleOnly === false){
            $product = $this->_getProduct($productInfo);
            $request = $this->_getProductRequest($requestInfo);
            $productId = $product->getId();

            if ($productId) {
                $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
                $minimumQty = $stockItem->getMinSaleQty();
                //If product was not found in cart and there is set minimal qty for it
                if ($minimumQty
                    && $minimumQty > 0
                    && $request->getQty() < $minimumQty
                    && !$this->getQuote()->hasProductId($productId)
                ) {
                    $request->setQty($minimumQty);
                }
            }

            if ($productId) {
                try {
                    $result = $this->getQuote()->addProduct($product, $request);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_checkoutSession->setUseNotice(false);
                    $result = $e->getMessage();
                }
                /**
                 * String we can get if prepare process has error
                 */
                if (is_string($result)) {
                    if ($product->hasOptionsValidationFail()) {
                        $redirectUrl = $product->getUrlModel()->getUrl(
                            $product,
                            ['_query' => ['startcustomization' => 1]]
                        );
                    } else {
                        $redirectUrl = $product->getProductUrl();
                    }
                    $this->_checkoutSession->setRedirectUrl($redirectUrl);
                    if ($this->_checkoutSession->getUseNotice() === null) {
                        $this->_checkoutSession->setUseNotice(true);
                    }
                    throw new \Magento\Framework\Exception\LocalizedException(__($result));
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
            }

            $this->_eventManager->dispatch(
                'checkout_cart_product_add_after',
                ['quote_item' => $result, 'product' => $product]
            );
            $this->_checkoutSession->setLastAddedProductId($productId);
        }

        return $this;
    }

}