<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ey\ConfigurableBundle\Block\Catalog\Product\View\Type\Bundle\Option;

use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Bundle option configurable type renderer
 */
class Configurable extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/view/type/bundle/option/configurable.phtml';

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    public $imageBuilder;

    /**
     * Configurable constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        array $data = []
    ) {
        $this->imageBuilder = $imageBuilder;
        parent::__construct(
            $context,
            $jsonEncoder,
            $catalogData,
            $registry,
            $string,
            $mathRandom,
            $cartHelper,
            $taxData,
            $pricingHelper,
            $data
        );
    }

    /**
     * Get title price for selection product
     *
     * @param \Magento\Catalog\Model\Product $selection
     * @param bool $includeContainer
     * @return string
     */
    public function getSelectionTitlePrice($selection, $includeContainer = true)
    {
        $priceTitle = '<span class="product-name">' . $this->escapeHtml($selection->getName()) . '</span>';
        
        return $priceTitle;
    }

    /**
     * @param string $image
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($image = 'category_page_grid')
    {
        return $this->imageBuilder->setProduct($this->getProduct())
            ->setImageId($image)
            ->create();
    }
}
