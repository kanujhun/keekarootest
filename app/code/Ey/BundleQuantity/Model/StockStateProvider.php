<?php

namespace Ey\BundleQuantity\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

class StockStateProvider extends \Magento\CatalogInventory\Model\StockStateProvider
{
    /**
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @param int|float $summaryQty
     * @param int|float $origQty
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function checkQuoteItemQty(StockItemInterface $stockItem, $qty, $summaryQty, $origQty = 0)
    {
        $result = parent::checkQuoteItemQty($stockItem, $qty, $summaryQty, $origQty);
        $qty = $this->getNumber($qty);
        if ($stockItem->getMinSaleQty() && $qty < $stockItem->getMinSaleQty()) {
            $result->setHasError(true)
                ->setMessage(
                    __(
                        'The fewest you may purchase for %1 is %2.',
                        $stockItem->getProductName(),
                        $stockItem->getMinSaleQty() * 1
                    )
                )
                ->setErrorCode('qty_min')
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($stockItem->getMaxSaleQty() && $qty > $stockItem->getMaxSaleQty()) {
            $result->setHasError(true)
                ->setMessage(
                    __(
                        'The most you may purchase for %1 is %2.',
                        $stockItem->getProductName(),
                        $stockItem->getMaxSaleQty() * 1
                    )
                )
                ->setErrorCode('qty_max')
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }
        return $result;
    }
}