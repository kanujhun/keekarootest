<?php
/**
 * Created by PhpStorm.
 * User: hoangnew
 * Date: 19/04/2016
 * Time: 21:29
 */
namespace Magenest\EmailNotifications\Observer\NewOrder;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Magento\Framework\Registry;

class NewOrder implements ObserverInterface
{
    protected $_logger;
    
    protected $_coreRegistry;

    protected $_scopeConfig;

    protected $_transportBuilder;

    protected $_storeManager;

    protected $_orderFactory;
    
    public function __construct(
        LoggerInterface $loggerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {

        $this->_logger = $loggerInterface;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registry;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_orderFactory = $orderFactory;
    }

    public function execute(Observer $observer)
    {
        $orderId=$observer->getEvent()->getOrder()->getId();
        /** @var \Magento\Sales\Model\Order $orderModel */
        $orderModel = $this->_orderFactory->create();
        $order = $orderModel->load($orderId);
        $createdAt = $order->getCreatedAt();
        $couponCode = $order->getCouponCode();
        $enable = $this->_scopeConfig->getValue(
            'emailnotifications_config/config_group_new_order/config_new_order_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $enableCoupon = $this->_scopeConfig->getValue(
            'emailnotifications_config/config_group_new_coupon/config_new_coupon_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($enableCoupon == 'yes' && $couponCode) {
            $receiverList = $this->_scopeConfig->getValue(
                'emailnotifications_config/config_group_new_coupon/config_new_coupon_receiver',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $receiverEmails =explode(';', $receiverList);
            foreach ($receiverEmails as $receiverEmail) {
                try {
                    $template_id = $this->_scopeConfig->getValue(
                        'emailnotifications_config/config_group_new_coupon/config_new_coupon_template',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );

                    $transport = $this->_transportBuilder->setTemplateIdentifier($template_id)->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                    )->setTemplateVars(
                        [
                            'orderId' => $orderModel->load($orderId)->getIncrementId(),
                            'created_at' => $createdAt,
                            'coupon_code' => $couponCode
                        ]
                    )->setFrom(
                        $this->_scopeConfig->getValue(
                            'emailnotifications_config/config_group_email_sender/config_email_sender',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->addTo(
                        $receiverEmail
                    )->getTransport();
                    $transport->sendMessage();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_logger->critical($e);
                }
            }
        }

        if ($enable == 'yes') {
            $receiverList = $this->_scopeConfig->getValue(
                'emailnotifications_config/config_group_new_order/config_new_order_receiver',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $receiverEmails = explode(';', $receiverList);
            foreach ($receiverEmails as $receiverEmail) {
                try {
                    $template_id = $this->_scopeConfig->getValue(
                        'emailnotifications_config/config_group_new_order/config_new_order_template',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );

                    $transport = $this->_transportBuilder->setTemplateIdentifier($template_id)->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                    )->setTemplateVars(
                        [
                            'orderId' => $orderModel->load($orderId)->getIncrementId(),
                            'created_at' => $createdAt,
                        ]
                    )->setFrom(
                        $this->_scopeConfig->getValue(
                            'emailnotifications_config/config_group_email_sender/config_email_sender',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->addTo(
                        $receiverEmail
                    )->getTransport();
                    $transport->sendMessage();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_logger->critical($e);
                }
            }
        }
    }
}
