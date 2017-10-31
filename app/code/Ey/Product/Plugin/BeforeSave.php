<?php

namespace Ey\Product\Plugin;

class BeforeSave
{
    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param $result
     * @return \Magento\Catalog\Model\Product
     */
    public function afterBeforeSave(\Magento\Catalog\Model\Product $subject, $result)
    {
        /**
         * Temp fix for bug: https://github.com/magento/magento2/issues/2434
         **/
        if( 'configurable' == $subject->getTypeId() ){
            $subject->setTypeHasRequiredOptions(false);
            $subject->setRequiredOptions(false);
        }

        return $subject;
    }
}