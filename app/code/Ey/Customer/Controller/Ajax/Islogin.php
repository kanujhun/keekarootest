<?php
/**
 * Copyright @ 2016 Magento. All rights reserved.
 */

namespace Ey\Customer\Controller\Ajax;
use Magento\Customer\Model\Session;

/**
 * Class Islogin
 * @package Ey\Customer\Controller\Ajax
 */
class Islogin extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data $helper
     */
    protected $helper;

    /**
     * Islogin constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param Session $customerSession
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $customerSession,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ){
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
    }
    /**
     * @return string;
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            $response = [
                'login'=>true,
                'group'=>$this->customerSession->getCustomerGroupId(),
                'message'=>__('Customer already logged in.')
            ];
        } else {
            $response = [
                'login'=>false,
                'group'=>0,
                'message'=>__('Customer isn\'t logged in.')
            ];
        }
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}