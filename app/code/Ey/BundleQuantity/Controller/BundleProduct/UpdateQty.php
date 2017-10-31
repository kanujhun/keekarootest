<?php

namespace Ey\BundleQuantity\Controller\BundleProduct;

class UpdateQty extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * UpdateQty constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_cart = $cart;
        $this->_product = $product;
        $this->_messageManager = $messageManager;
    }

    /**
     * return void
     */
    public function execute()
    {
        $requestInfo = $this->getRequest()->getParams();

        try{
            $product = $this->_product->load($requestInfo['product']);

            /**
             * remove the existing item
             */
            if(isset($requestInfo['item_id'])){
                $this->_cart->removeItem($requestInfo['item_id']);
            }

            /**
             * add new item
             */
            $this->_cart->addProduct($product, $requestInfo);
            $this->_cart->save();
            $this->_messageManager->addSuccess(__('You updated '.$product->getName().' bundle items\' quantity'));
        } catch(\Magento\Framework\Exception\LocalizedException $e){
            $this->_messageManager->addError($e->getMessage());
        }

        $this->_redirect('checkout/cart/index');

        return;
    }

}