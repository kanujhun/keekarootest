<?php

namespace Ey\ConfigurableBundle\Plugin;

use Magento\Framework\Stdlib\ArrayManager;

class BundlePanel
{
    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * BundlePanel constructor.
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param \Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel $subject
     * @param $result
     * @return array
     */
    public function afterModifyMeta(
        \Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel $subject,
        $meta
    ) {
        $path = 'bundle-items/children/bundle_options/children/record/children/product_bundle_container/children/bundle_selections/children/record/children/selection_qty/arguments/data/config/validation/validate-greater-than-zero';
        if($exist = $this->arrayManager->exists($path, $meta)){
            if($validateGreaterThanZero = $this->arrayManager->get($path, $meta)){
                $meta = $this->arrayManager->set($path, $meta, false);
            }
        }

        return $meta;
    }
}