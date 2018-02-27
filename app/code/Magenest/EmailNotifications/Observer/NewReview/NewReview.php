<?php
/**
 * Created by PhpStorm.
 * User: katsu
 * Date: 19/04/2016
 * Time: 14:22
 */
namespace Magenest\EmailNotifications\Observer\NewReview;

use Magento\Framework\Event\ObserverInterface;

use Magento\Framework\Event\Observer;

use Psr\Log\LoggerInterface;

use Magento\Framework\Registry;

class NewReview implements ObserverInterface
{
    protected $_logger;

    protected $_coreRegistry;

    protected $_scopeConfig;

    protected $_transportBuilder;

    protected $_storeManager;

    protected $_reviewFactory;

    public function __construct(
        LoggerInterface $loggerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Registry $registry,
        \Magento\Review\Model\ReviewFactory $reviewFactory
    ) {
        $this->_logger = $loggerInterface;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registry;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_reviewFactory = $reviewFactory;
    }

    public function execute(Observer $observer)
    {
        $reviewId = $observer->getObject()->getId();

        /** @var \Magento\Review\Model\Review $reviewModel */
        $reviewModel = $this->_reviewFactory->create();

        $detail =  $reviewModel->load($reviewId)->getDetail();
        $title = $reviewModel->load($reviewId)->getTitle();
        $productId = $reviewModel->load($reviewId)->getEntityPkValue();
        $nickname = $reviewModel->load($reviewId)->getNickname();

        $enable = $this->_scopeConfig->getValue(
            'emailnotifications_config/config_group_new_review/config_new_review_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($enable == 'yes') {
            $receiverList = $this->_scopeConfig->getValue(
                'emailnotifications_config/config_group_new_review/config_new_review_receiver',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $receiverEmails =explode(';', $receiverList);
            foreach ($receiverEmails as $receiverEmail) {
                try {
                    $template_id = $this->_scopeConfig->getValue(
                        'emailnotifications_config/config_group_new_review/config_new_review_template',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );

                    $transport = $this->_transportBuilder->setTemplateIdentifier($template_id)->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                    )->setTemplateVars(
                        [
                            'nickname' => $nickname,
                            'productId' => $productId,
                            'store' => $this->_storeManager->getStore(),
                            'title' => $title,
                            'detail' => $detail
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
