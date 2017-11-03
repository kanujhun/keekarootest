<?php
namespace Ey\Customer\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Internal variable to retain customer session object
     *
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;
    /**
     * @var $moduleManager
     */
    // protected $moduleManager;
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * getCustomer
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer(){
        return $this->customerSession->getCustomer();
    }

    /**
     * getCustomerGroup
     * @return Integer
     */
    public function getCustomerGroup(){
        return $this->customerSession->getCustomerGroupId();
    }
}