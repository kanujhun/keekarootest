<?php
/**
 * Created by PhpStorm.
 * User: katsu
 * Date: 19/10/2016
 * Time: 14:20
 */
namespace Magenest\EmailNotifications\Observer\Wishlist;

use Magento\Framework\Event\ObserverInterface;

use Magento\Framework\Event\Observer;

use Psr\Log\LoggerInterface;

use Magento\Framework\Registry;

class WishlistAddProduct implements ObserverInterface
{
    protected $_logger;

    protected $_coreRegistry;

    protected $_scopeConfig;

    protected $_transportBuilder;

    protected $_storeManager;

    protected $_customerFactory;
    
    public function __construct(
        LoggerInterface $loggerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_logger = $loggerInterface;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registry;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
    }

    public function execute(Observer $observer)
    {
        $productName = $observer->getEvent()->getProduct()->getName();
        $customerId = $observer->getEvent()->getWishlist()->getCustomerId();
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customer = $this->_customerFactory->create()->load($customerId);
        $customerName = $customer->getName();
        $customerEmail = $customer->getEmail();
        $enable = $this->_scopeConfig->getValue(
            'emailnotifications_config/config_group_new_wishlist/config_new_wishlist_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($enable == 'yes') {
            $receiverList = $this->_scopeConfig->getValue(
                'emailnotifications_config/config_group_new_wishlist/config_new_wishlist_receiver',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $receiverEmails =explode(';', $receiverList);
            foreach ($receiverEmails as $receiverEmail) {
                try {
                    $template_id = $this->_scopeConfig->getValue(
                        'emailnotifications_config/config_group_new_wishlist/config_new_wishlist_template',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );

                    $transport = $this->_transportBuilder->setTemplateIdentifier($template_id)->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                    )->setTemplateVars(
                        [
                            'customerName' => $customerName,
                            'customerEmail' => $customerEmail,
                            'productName' => $productName,
                            'store' => $this->_storeManager->getStore()
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
