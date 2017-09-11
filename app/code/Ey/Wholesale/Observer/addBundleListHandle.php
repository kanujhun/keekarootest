<?php

namespace Ey\Wholesale\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\View\LayoutInterface;

class addBundleListHandle implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Ey\Wholesale\Helper\Data
     */
    protected $eyHelper;

    /**
     * addBundleListHandle constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Ey\Wholesale\Helper\Data $eyHelper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Ey\Wholesale\Helper\Data $eyHelper
    )
    {
        $this->_request = $request;
        $this->eyHelper = $eyHelper;
    }

    /**
     * Add handles to the page.
     *
     * @param Observer $observer
     * @event layout_load_before
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if(
            $observer->getFullActionName() == 'catalog_category_view' &&
            $this->eyHelper->isEnabled() === true
        ){
            $viewMode = $this->_request->getParam(
                    \Magento\Catalog\Model\Product\ProductList\Toolbar::MODE_PARAM_NAME
                );
            if($viewMode && $viewMode == 'list'){
                /** @var LayoutInterface $layout */
                $layout = $observer->getData('layout');
                $layout->getUpdate()->addHandle('catalog_category_bundle_list');
            }
        }
    }
}