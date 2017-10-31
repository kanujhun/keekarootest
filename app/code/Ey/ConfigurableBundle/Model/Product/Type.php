<?php

namespace Ey\ConfigurableBundle\Model\Product;

class Type extends \Magento\Bundle\Model\Product\Type
{
    /**
     * Checking if we can sale this bundle
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        $optionCollection = $this->getOptionsCollection($product);
        if (count($optionCollection->getItems())) {
            $selectionCollection = $this->getSelectionsCollection($optionCollection->getAllIds(), $product);
            if (count($selectionCollection->getItems())) {
                foreach ($selectionCollection->getItems() as $selection){
                    if($selection->getTypeId() == 'configurable' &&
                        $selection->getIsSalable() == '1'){
                        $hasConfigurable = true;
                    }
                }
            }
        }

        if (isset($hasConfigurable) && $hasConfigurable) {
            $product->setData('all_items_salable', true);
        }

        return parent::isSalable($product);
    }

    /**
     * Before save type related data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeSave($product)
    {
        parent::beforeSave($product);
    }
}