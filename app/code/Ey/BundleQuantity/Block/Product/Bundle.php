<?php

namespace Ey\BundleQuantity\Block\Product;

use Magento\Checkout\Exception;

class Bundle extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $_quoteItem;

    /**
     * @var array
     */
    protected $_bundleOptions;

    /**
     * Bundle constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        array $data = []
    )
    {
        $this->_productRepository = $productRepository;
        $this->_quoteRepository = $quoteRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if(!$this->_product){
            $productId = $this->getRequest()->getParam('product_id');
            $this->_product = $this->_productRepository->getById($productId);
        }

        return $this->_product;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function getQuoteItem()
    {
        if(!$this->_quoteItem){
            $quoteId = $this->getRequest()->getParam('quote_id');
            $itemId = $this->getRequest()->getParam('item_id');
            try{
                /** @var \Magento\Quote\Model\Quote $quote */
                $quote = $this->_quoteRepository->get($quoteId);
                $itemCollection = $quote->getItemsCollection();

                foreach ($itemCollection as $item){
                    if($itemId == $item->getItemId()){
                        $this->_quoteItem = $item;
                    }
                }
            } catch(Exception $e){
                throwException($e);
            }
        }

        return $this->_quoteItem;
    }

    /**
     * @return array
     */
    public function getBundleOptions()
    {
        if(!$this->_bundleOptions){
            $item = $this->getQuoteItem();
            $options = [];
            $product = $item->getProduct();

            /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
            $typeInstance = $product->getTypeInstance();

            // get bundle options
            $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
            $bundleOptionsIds = $optionsQuoteItemOption ? unserialize($optionsQuoteItemOption->getValue()) : [];
            if ($bundleOptionsIds) {
                /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
                $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

                // get and add bundle selections collection
                $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

                $bundleSelectionIds = unserialize($selectionsQuoteItemOption->getValue());

                if (!empty($bundleSelectionIds)) {
                    $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $product);

                    $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
                    foreach ($bundleOptions as $bundleOption) {
                        if ($bundleOption->getSelections()) {
                            $option = [
                                'label' => $bundleOption->getTitle(),
                                'option_id' => $bundleOption->getOptionId(),
                                'products' => []
                            ];
                            $bundleSelections = $bundleOption->getSelections();

                            foreach ($bundleSelections as $bundleSelection) {
                                $qty = $product->getCustomOption(
                                    'selection_qty_' . $bundleSelection->getSelectionId()
                                    )->getValue() * 1;
                                $option['products'][] = array(
                                    'id' => $bundleSelection->getId(),
                                    'name' => $bundleSelection->getName(),
                                    'price' => $bundleSelection->getFinalPrice(),
                                    'qty' => $qty,
                                    'selection_id' => $bundleSelection->getSelectionId()
                                );
                            }

                            $options[] = $option;
                        }
                    }
                }
            }

            $this->_bundleOptions = $options;
        }


        return $this->_bundleOptions;
    }


}