<?php

namespace Ey\MageCore\Helper\Catalog;

use Magento\Catalog\Model\Category as ModelCategory;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\Filter\Template;

class Output extends \Magento\Catalog\Helper\Output
{
    /**
     * Prepare product attribute html output
     *
     * @param ModelProduct $product
     * @param string $attributeHtml
     * @param string $attributeName
     * @param int $length
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function productAttribute($product, $attributeHtml, $attributeName, $length = null)
    {
        $attribute = $this->_eavConfig->getAttribute(ModelProduct::ENTITY, $attributeName);
        if ($attribute &&
            $attribute->getId() &&
            $attribute->getFrontendInput() != 'media_image' &&
            (!$attribute->getIsHtmlAllowedOnFront() &&
                !$attribute->getIsWysiwygEnabled())
        ) {
            if ($attribute->getFrontendInput() != 'price') {
                $attributeHtml = $this->_escaper->escapeHtml($attributeHtml);
            }
            if ($attribute->getFrontendInput() == 'textarea') {
                $attributeHtml = nl2br($attributeHtml);
            }
        }
        if ($attribute->getIsHtmlAllowedOnFront() && $attribute->getIsWysiwygEnabled()) {
            if ($this->_catalogData->isUrlDirectivesParsingAllowed()) {
                $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
            }
        }
        if (($attributeName == 'name' || $attributeName == 'short_name') && $length) {
            if (strlen($attributeHtml) > $length) {
                $attributeHtml = substr($attributeHtml, 0, $length) . '...';
            }
        }

        $attributeHtml = $this->process(
            'productAttribute',
            $attributeHtml,
            ['product' => $product, 'attribute' => $attributeName]
        );

        return $attributeHtml;
    }
}