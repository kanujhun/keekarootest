<?php
/**
 * Created by PhpStorm.
 * User: hoangnew
 * Date: 18/04/2016
 * Time: 11:38
 */
namespace Magenest\EmailNotifications\Observer\NewStatus;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Magento\Framework\Registry;

class OrderSaveAfter implements ObserverInterface
{
    protected $_logger;
    
    protected $_coreRegistry;

    protected $_scopeConfig;

    protected $_transportBuilder;

    protected $_storeManager;

    public function __construct(
        LoggerInterface $loggerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Registry $registry
    ) {
        $this->_logger = $loggerInterface;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registry;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $statusBefore =  $order->getOrigData('status');
        $statusAfter = $order->getStatus();

        $enable1 = $this->_scopeConfig->getValue(
            'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_enable1',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($enable1 == 'yes') {
            $from1 = $this->_scopeConfig->getValue(
                'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_from1',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $to1 = $this->_scopeConfig->getValue(
                'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_to1',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ((strpos($from1, $statusBefore) !== false) && (strpos($to1, $statusAfter) !== false)) {
                $receiverList = $this->_scopeConfig->getValue(
                    'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_receiver1',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $receiverEmails = explode(';', $receiverList);
                foreach ($receiverEmails as $receiverEmail) {
                    $template_id1 = $this->_scopeConfig->getValue(
                        'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_template1',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                    $transport1 = $this->_transportBuilder->setTemplateIdentifier($template_id1)->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                    )->setTemplateVars(
                        [
                            'orderId' => $order->getIncrementId(),
                            'updated_at' => $order->getUpdatedAt(),
                            'statebefore' => $from1,
                            'stateafter' => $to1
                        ]
                    )->setFrom(
                        $this->_scopeConfig->getValue(
                            'emailnotifications_config/config_group_email_sender/config_email_sender',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->addTo(
                        $receiverEmail
                    )->getTransport();
                    $transport1->sendMessage();
                }
            }
        }
        $enable2 = $this->_scopeConfig->getValue(
            'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_enable2',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($enable2 == 'yes') {
            $from2 = $this->_scopeConfig->getValue(
                'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_from2',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $to2 = $this->_scopeConfig->getValue(
                'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_to2',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ((strpos($from2, $statusBefore) !== false) && (strpos($to2, $statusAfter) !== false)) {
                $receiverList = $this->_scopeConfig->getValue(
                    'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_receiver2',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $receiverEmails = explode(';', $receiverList);
                foreach ($receiverEmails as $receiverEmail) {
                    $template_id2 = $this->_scopeConfig->getValue(
                        'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_template2',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                    $transport1 = $this->_transportBuilder->setTemplateIdentifier($template_id2)->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                    )->setTemplateVars(
                        [
                            'orderId' => $order->getIncrementId(),
                            'updated_at' => $order->getUpdatedAt(),
                            'statebefore' => $from2,
                            'stateafter' => $to2
                        ]
                    )->setFrom(
                        $this->_scopeConfig->getValue(
                            'emailnotifications_config/config_group_email_sender/config_email_sender',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->addTo(
                        $receiverEmail
                    )->getTransport();
                    $transport1->sendMessage();
                }
            }
        }
        $enable3 = $this->_scopeConfig->getValue(
            'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_enable3',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($enable3 == 'yes') {
            $from3 = $this->_scopeConfig->getValue(
                'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_from3',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $to3 = $this->_scopeConfig->getValue(
                'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_to3',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ((strpos($from3, $statusBefore) !== false ) && (strpos($to3, $statusAfter) !== false )) {
                $receiverList = $this->_scopeConfig->getValue(
                    'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_receiver3',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $receiverEmails = explode(';', $receiverList);
                foreach ($receiverEmails as $receiverEmail) {
                    $template_id3 = $this->_scopeConfig->getValue(
                        'emailnotifications_config/config_group_new_orderstatus/config_new_orderstatus_template3',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                    $transport1 = $this->_transportBuilder->setTemplateIdentifier($template_id3)->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                    )->setTemplateVars(
                        [
                            'orderId' => $order->getIncrementId(),
                            'updated_at' => $order->getUpdatedAt(),
                            'statebefore' => $from3,
                            'stateafter' => $to3
                        ]
                    )->setFrom(
                        $this->_scopeConfig->getValue(
                            'emailnotifications_config/config_group_email_sender/config_email_sender',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->addTo(
                        $receiverEmail
                    )->getTransport();
                    $transport1->sendMessage();
                }
            }
        }
    }
}
