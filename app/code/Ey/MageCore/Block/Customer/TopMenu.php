<?php

namespace Ey\MageCore\Block\Customer;

use \Magento\Framework\View\Element\Template;
use \Magento\Customer\Model\Session;
use \Magento\Customer\Model\Url;

class TopMenu extends Template
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Url
     */
    protected $_customerUrl;

    /**
     * TopMenu constructor.
     * @param Template\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Customer\Model\Url $url
     */
    public function __construct(
        Template\Context $context,
        Session $session,
        Url $url
    )
    {
        parent::__construct($context);
        $this->_customerSession = $session;
        $this->_customerUrl = $url;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_customerUrl->getLoginUrl();
    }

    /**
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->_customerUrl->getLogoutUrl();
    }

    /**
     * Wholesale Login
     * @return string
     */
    public function getWholesaleLoginUrl()
    {
        $stores = $this->_storeManager->getStores();
        foreach ($stores as $store){
            if(strpos(strtolower($store->getCode()), 'wholesale') !== false){
                return $store->getBaseUrl() . \Magento\Customer\Model\Url::ROUTE_ACCOUNT_LOGIN;
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getBaseUrl().$this->getAjaxPath();
    }

    /**
     * @return string
     */
    public function getAjaxPath()
    {
        return 'customer_ajax/ajax/islogin/';
    }
}