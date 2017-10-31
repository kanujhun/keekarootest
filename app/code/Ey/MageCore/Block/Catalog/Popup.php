<?php

namespace Ey\MageCore\Block\Catalog;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var array
     */
    protected $_postData = array();

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    public $cartHelper;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    public $imageBuilder;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
//    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * Popup constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\HTTP\PhpEnvironment\Request $request
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
//     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
//        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_cart = $cart;
        $this->_postData = $request->getPostValue();
        $this->_currency = $currency;
        $this->cartHelper = $cartHelper;
        $this->imageBuilder = $imageBuilder;
        $this->productRepository = $productRepository;
        $this->storeManager = $_storeManager;
        $this->productCollection = $productCollection;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * @return mixed|string
     */
    public function getAddedProductId()
    {
        if(count($this->_postData) > 0){
            if(array_key_exists('product', $this->_postData)){
                return $this->_postData['product'];
            }
        }

        return '';
    }

    /**
     * @return float
     */
    public function getSubToTal()
    {
        return number_format($this->_cart->getQuote()->getSubtotal(), 2, '.', '');
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        $related = $this->getRequest()->getParam('related_product');
        if($related != ''){
            $related = explode(',', $related);
            return count($related)+1;
        }

        return 1;
    }

    /**
     * @return array
     */
    public function getAddedProduct()
    {
        $product = $this->_initProduct();

        /** build configurable options */
        if($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
            $attributes = $product->getTypeInstance()->getConfigurableAttributes($product);
            $selectedAttributes = $this->getRequest()->getParam('super_attribute');
            foreach ($attributes as $attribute){
                if(array_key_exists($attribute->getAttributeId(), $selectedAttributes)){
                    $valueId = $selectedAttributes[$attribute->getAttributeId()];
                    foreach ($attribute->getOptions() as $option){
                        if($valueId == $option['value_index']){
                            $selectedAttributes[$attribute->getAttributeId()] = array(
                                'label' => $attribute->getLabel(),
                                'value_label' => $option['store_label'],
                                'value_index' => $option['value_index']
                            );
                            continue;
                        }
                    }
                }
            }
        }

        /** build bundle pricing */
        if($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE){
            //$price = $product->getPriceModel()->getTotalPrices($product, 'min', false);
            $price = 0;
            $optionCol= $product->getTypeInstance(true)->getOptionsCollection($product);
            $selectionCol= $product->getTypeInstance(true)
                ->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );
            $optionCol->appendSelections($selectionCol);
            $bundleOption = $this->getRequest()->getParam('bundle_option');
            $bundleOptionQty = $this->getRequest()->getParam('bundle_option_qty');
            
            /** check if configurable included in bundle */
            $configBundleOption = $this->getRequest()->getParam('configurable_bundle_option');
            $configBundleOptionQty = $this->getRequest()->getParam('configurable_bundle_qty');
            if(!$bundleOption || !$bundleOptionQty){
                $bundleOption = $configBundleOption;
                $bundleOptionQty = $configBundleOptionQty;
            } elseif($configBundleOption && $configBundleOptionQty){
                $bundleOption = $bundleOption + $configBundleOption;
                $bundleOptionQty = $bundleOptionQty + $configBundleOptionQty;
            }

            foreach ($optionCol as $option) {
                $selections = $option->getSelections();
                foreach ($selections as $selection){
                    $selectionId = $selection->getOptionId();
                    if(
                        array_key_exists($selectionId, $bundleOption) &&
                        $bundleOption[$selectionId] == $selection->getSelectionId()
                    ){
                        $qty = $bundleOptionQty[$selection->getOptionId()];
                        $price += $selection->getFinalPrice() * $qty;
                    }
                }
            }
        } else{
            $price = $product->getFinalPrice();
        }

        $addedProduct = array(
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'qty' => $this->getRequest()->getParam('qty'),
            'description' => $product->getDescription(),
            'price' => number_format($price, 2, '.', ''),
            'product' => $product,
            'options' => isset($selectedAttributes) ? $selectedAttributes:null
        );

        return $addedProduct;
    }

    /**
     * @return array|bool
     */
    public function getRelatedItems()
    {
        $related = $this->getRequest()->getParam('related_product');
        if($related != ''){
            $related = explode(',', $related);
            $relatedProducts = $this->productCollection
                ->addAttributeToFilter('entity_id', array('in' => $related))
                ->addAttributeToSelect(
                    array('name', 'image', 'price', 'sale_price')
                );

            return $relatedProducts;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     * @return string
     */
    public function getCartUrl()
    {
        return $this->cartHelper->getCartUrl();
    }

    /**
     * @param $product
     * @param string $image
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $image = 'category_page_grid')
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($image)
            ->create();
    }
}