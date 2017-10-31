<?php
namespace Schogini\Firstdataglobalgateway\Model; use Schogini\Firstdataglobalgateway\Model\FirstdataglobalgatewayException; use Magento\Framework\App\Filesystem\DirectoryList; use Magento\Sales\Model\Order; use Monolog\Logger; use Magento\Quote\Api\Data\CartInterface; error_reporting(E_ALL); ini_set('display_errors', '1'); class Firstdataglobalgateway extends \Magento\Payment\Model\Method\Cc { const CODE = 'firstdataglobalgateway'; protected $_code = self::CODE; protected $_isGateway = true; protected $_canAuthorize = true; protected $_canCapture = true; protected $_canCapturePartial = false; protected $_canRefund = true; protected $_canRefundInvoicePartial = false; protected $_canVoid = true; protected $_canUseInternal = true; protected $_canUseCheckout = true; protected $_canUseForMultishipping = true; protected $_canSaveCc = false; const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE'; const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY'; const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY'; const REQUEST_TYPE_CREDIT = 'REFUND'; const REQUEST_TYPE_VOID = 'VOID'; const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE'; const RESPONSE_CODE_APPROVED = 1; const RESPONSE_CODE_DECLINED = 2; const RESPONSE_CODE_ERROR = 3; const RESPONSE_CODE_HELD = 4; public function __construct(\Magento\Framework\Model\Context $spf07f39, \Magento\Framework\Registry $sp44051c, \Magento\Framework\Api\ExtensionAttributesFactory $sp2795e9, \Magento\Framework\Api\AttributeValueFactory $spd6e106, \Magento\Payment\Helper\Data $spd63c26, \Magento\Framework\App\Config\ScopeConfigInterface $sp8ba270, \Magento\Payment\Model\Method\Logger $sp8cf5dd, \Magento\Framework\Module\ModuleListInterface $spb71389, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $spb3cdee, \Schogini\Firstdataglobalgateway\Model\Request\Factory $sp7026de, \Schogini\Firstdataglobalgateway\Model\Response\Factory $sp577e53, \Magento\Backend\Model\Auth\Session $spb3b387, \Magento\Framework\Model\ResourceModel\AbstractResource $spcd441f = null, \Magento\Framework\Data\Collection\AbstractDb $sp13f653 = null, array $sp854ab2 = array()) { $this->requestFactory = $sp7026de; $this->responseFactory = $sp577e53; $this->authSession = $spb3b387; parent::__construct($spf07f39, $sp44051c, $sp2795e9, $spd6e106, $spd63c26, $sp8ba270, $sp8cf5dd, $spb71389, $spb3cdee, $spcd441f, $sp13f653, $sp854ab2); } public function isAvailable(CartInterface $spdc93c6 = null) { return true; } public function authorize(\Magento\Payment\Model\InfoInterface $sp9b03e8, $sp7e7b81) { if ($sp7e7b81 <= 0) { self::throwException(__('Invalid amount for capture.')); } $sp023ea1 = false; if ($sp7e7b81 > 0) { $sp9b03e8->setAnetTransType(self::REQUEST_TYPE_AUTH_ONLY); $sp9b03e8->setAmount($sp7e7b81); $spf00afa = $this->_buildRequest($sp9b03e8); $spde1bee = $this->_postRequest($spf00afa); $sp9b03e8->setCcApproval($spde1bee->getApprovalCode())->setLastTransId($spde1bee->getTransactionId())->setCcTransId($spde1bee->getTransactionId())->setCcAvsStatus($spde1bee->getAvsResultCode())->setCcCidStatus($spde1bee->getCardCodeResponseCode()); $spf78a49 = $spde1bee->getResponseReasonCode(); $sp5c8319 = $spde1bee->getResponseReasonText(); switch ($spde1bee->getResponseCode()) { case self::RESPONSE_CODE_APPROVED: $sp9b03e8->setStatus(self::STATUS_APPROVED); if ($spde1bee->getTransactionId() != $sp9b03e8->getParentTransactionId()) { $sp9b03e8->setTransactionId($spde1bee->getTransactionId()); } $sp9b03e8->setIsTransactionClosed(0)->setTransactionAdditionalInfo('real_transaction_id', $spde1bee->getTransactionId()); break; case self::RESPONSE_CODE_DECLINED: $sp023ea1 = __('Payment authorization transaction has been declined. ' . "\n{$sp5c8319}"); break; default: $sp023ea1 = __('Payment authorization error. ' . "\n{$sp5c8319}"); break; } } else { $sp023ea1 = __('Invalid amount for authorization.'); } if ($sp023ea1 !== false) { self::throwException($sp023ea1); } return $this; } public function capture(\Magento\Payment\Model\InfoInterface $sp9b03e8, $sp7e7b81) { $sp023ea1 = false; if ($sp9b03e8->getParentTransactionId()) { $sp9b03e8->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE); } else { $sp9b03e8->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE); } $sp9b03e8->setAmount($sp7e7b81); $spf00afa = $this->_buildRequest($sp9b03e8); $spde1bee = $this->_postRequest($spf00afa); if ($spde1bee->getResponseCode() == self::RESPONSE_CODE_APPROVED) { $sp9b03e8->setStatus(self::STATUS_APPROVED); $sp9b03e8->setCcTransId($spde1bee->getTransactionId()); $sp9b03e8->setLastTransId($spde1bee->getTransactionId()); if ($spde1bee->getTransactionId() != $sp9b03e8->getParentTransactionId()) { $sp9b03e8->setTransactionId($spde1bee->getTransactionId()); } if ($sp9b03e8->getOrder()->getTotalDue() == $sp7e7b81) { $sp24fabb = true; } else { $sp24fabb = false; } $sp9b03e8->setShouldCloseParentTransaction($sp24fabb)->setTransactionAdditionalInfo('real_transaction_id', $spde1bee->getTransactionId()); } else { if ($spde1bee->getResponseReasonText()) { $sp023ea1 = $spde1bee->getResponseReasonText(); } else { $sp023ea1 = __('Error in capturing the payment'); } } if ($sp023ea1 !== false) { self::throwException($sp023ea1); } return $this; } public function refund(\Magento\Payment\Model\InfoInterface $sp9b03e8, $sp7e7b81) { $this->logit('refund', 'Start Refund'); $sp023ea1 = false; $spb2b4b6 = $sp9b03e8->getRefundTransactionId(); if (empty($spb2b4b6)) { $spb2b4b6 = $sp9b03e8->getParentTransactionId(); } if ($spb2b4b6 && $sp7e7b81 > 0) { $this->logit('refund', 'Transaction ID and Amount correct'); $sp9b03e8->setAnetTransType(self::REQUEST_TYPE_CREDIT); $spf00afa = $this->_buildRequest($sp9b03e8); $this->logit('refund', 'Request built. Make the call.'); $spf00afa->setXAmount($sp7e7b81); $spde1bee = $this->_postRequest($spf00afa); if ($spde1bee->getResponseCode() == self::RESPONSE_CODE_APPROVED) { $this->logit('refund', 'Success Response'); $sp9b03e8->setStatus(self::STATUS_SUCCESS); if ($spde1bee->getTransactionId() != $sp9b03e8->getParentTransactionId()) { $sp9b03e8->setTransactionId($spde1bee->getTransactionId()); } $sp087536 = $sp9b03e8->getOrder()->canCreditmemo() ? 0 : 1; $sp9b03e8->setIsTransactionClosed(1)->setShouldCloseParentTransaction($sp087536)->setTransactionAdditionalInfo('real_transaction_id', $spde1bee->getTransactionId()); } else { $this->logit('refund', 'Oops failure response'); $sp704c2e = $spde1bee->getResponseReasonText(); $sp023ea1 = true; } } else { $this->logit('refund', 'Transaction ID and Amount NOT correct'); $sp704c2e = __('Error in refunding the payment'); $sp023ea1 = true; } if ($sp023ea1 !== false) { self::throwException($sp704c2e); } return $this; } public function void(\Magento\Payment\Model\InfoInterface $sp9b03e8) { $sp023ea1 = false; $spb2b4b6 = $sp9b03e8->getVoidTransactionId(); if (empty($spb2b4b6)) { $spb2b4b6 = $sp9b03e8->getParentTransactionId(); } $sp7e7b81 = $sp9b03e8->getAmount(); if ($sp7e7b81 <= 0) { $sp7e7b81 = $sp9b03e8->getAmountAuthorized(); $sp9b03e8->setAmount($sp9b03e8->getAmountAuthorized()); } if ($spb2b4b6 && $sp7e7b81 > 0) { $sp9b03e8->setAnetTransType(self::REQUEST_TYPE_VOID); $spf00afa = $this->_buildRequest($sp9b03e8); $spde1bee = $this->_postRequest($spf00afa); if ($spde1bee->getResponseCode() == self::RESPONSE_CODE_APPROVED) { $sp9b03e8->setStatus(self::STATUS_VOID); if ($spde1bee->getTransactionId() != $sp9b03e8->getParentTransactionId()) { $sp9b03e8->setTransactionId($spde1bee->getTransactionId()); } $sp9b03e8->setIsTransactionClosed(1)->setShouldCloseParentTransaction(1)->setTransactionAdditionalInfo('real_transaction_id', $spde1bee->getTransactionId()); $sp9b03e8->getOrder()->setState(Order::STATE_CLOSED)->setStatus($spd8cfc4->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED)); } else { $sp704c2e = $spde1bee->getResponseReasonText(); $sp023ea1 = true; } } else { if (!$spb2b4b6) { $sp704c2e = __('Error in voiding the payment. Transaction ID not found'); $sp023ea1 = true; } else { if ($sp7e7b81 <= 0) { $sp704c2e = __('Error in voiding the payment. Payment amount is 0'); $sp023ea1 = true; } else { $sp704c2e = __('Error in voiding the payment'); $sp023ea1 = true; } } } if ($sp023ea1 !== false) { self::throwException($sp704c2e); } return $this; } protected function _buildRequest(\Magento\Payment\Model\InfoInterface $sp9b03e8) { $spd8cfc4 = $sp9b03e8->getOrder(); $spf00afa = $this->requestFactory->create(); $spf00afa->setXVersion(3.1)->setXDelimData('True')->setXDelimChar('')->setXRelayResponse('False'); $spf00afa->setXTestRequest($this->getConfigData('test') ? 'TRUE' : 'FALSE'); $spf00afa->setXLogin($this->getConfigData('login'))->setXTranKey($this->getConfigData('trans_key'))->setXType($sp9b03e8->getAnetTransType())->setXMethod($sp9b03e8->getAnetTransMethod()); if ($sp9b03e8->getAmount()) { $spf00afa->setXAmount($sp9b03e8->getAmount(), 2); $spf00afa->setXCurrencyCode($spd8cfc4->getBaseCurrencyCode()); } $spf00afa->setXAuthCode($sp9b03e8->getCcAuthCode()); $spf00afa->setXTransId($sp9b03e8->getCcTransId()); $spf00afa->setXCardNum($sp9b03e8->getCcNumber())->setXExpDate(sprintf('%02d-%04d', $sp9b03e8->getCcExpMonth(), $sp9b03e8->getCcExpYear()))->setXCardCode($sp9b03e8->getCcCid())->setXCardName($sp9b03e8->getCcOwner()); if (!empty($spd8cfc4)) { $sp5de277 = $spd8cfc4->getShippingAmount(); $sp2e79a0 = $spd8cfc4->getTaxAmount(); $sp3d1eb2 = $spd8cfc4->getSubtotal(); $spf00afa->setXInvoiceNum($spd8cfc4->getIncrementId()); $sp2e4300 = $spd8cfc4->getBillingAddress(); if (!empty($sp2e4300)) { $spadeb61 = $sp2e4300->getEmail(); if (!$spadeb61) { $spadeb61 = $spd8cfc4->getBillingAddress()->getEmail(); } if (!$spadeb61) { $spadeb61 = $spd8cfc4->getCustomerEmail(); } $spf00afa->setXFirstName($sp2e4300->getFirstname())->setXLastName($sp2e4300->getLastname())->setXCompany($sp2e4300->getCompany())->setXAddress($sp2e4300->getStreet(1)[0])->setXCity($sp2e4300->getCity())->setXState($sp2e4300->getRegion())->setXZip($sp2e4300->getPostcode())->setXCountry($sp2e4300->getCountry())->setXPhone($sp2e4300->getTelephone())->setXFax($sp2e4300->getFax())->setXCustId($sp2e4300->getCustomerId())->setXCustomerIp($spd8cfc4->getRemoteIp())->setXCustomerTaxId($sp2e4300->getTaxId())->setXEmail($spadeb61)->setXEmailCustomer($this->getConfigData('email_customer'))->setXMerchantEmail($this->getConfigData('merchant_email')); } $sp2d8474 = $spd8cfc4->getShippingAddress(); if (!$sp2d8474) { $sp2d8474 = $sp2e4300; } if (!empty($sp2d8474)) { $spf00afa->setXShipToFirstName($sp2d8474->getFirstname())->setXShipToLastName($sp2d8474->getLastname())->setXShipToCompany($sp2d8474->getCompany())->setXShipToAddress($sp2d8474->getStreet(1)[0])->setXShipToCity($sp2d8474->getCity())->setXShipToState($sp2d8474->getRegion())->setXShipToZip($sp2d8474->getPostcode())->setXShipToCountry($sp2d8474->getCountry()); if (!isset($sp5de277) || $sp5de277 <= 0) { $sp5de277 = $sp2d8474->getShippingAmount(); } if (!isset($sp2e79a0) || $sp2e79a0 <= 0) { $sp2e79a0 = $sp2d8474->getTaxAmount(); } if (!isset($sp3d1eb2) || $sp3d1eb2 <= 0) { $sp3d1eb2 = $sp2d8474->getSubtotal(); } } $spf00afa->setXPoNum($sp9b03e8->getPoNumber())->setXTax($sp2e79a0)->setXSubtotal($sp3d1eb2)->setXFreight($sp5de277); } $spf00afa->setXLineItems($this->getLineItems($spd8cfc4)); return $spf00afa; } protected function _postRequest(\Schogini\Firstdataglobalgateway\Model\Request $spf00afa) { $spde1bee = $this->responseFactory->create(); $sp8e19da = $spf00afa->getData(); $sp70bfdc = array(0 => '1', 1 => '1', 2 => '1', 3 => '(TESTMODE) This transaction has been approved.', 4 => '000000', 5 => 'P', 6 => '0', 7 => '100000018', 8 => '', 9 => '2704.99', 10 => 'CC', 11 => 'auth_only', 12 => '', 13 => 'Sreeprakash', 14 => 'N.', 15 => 'Schogini', 16 => 'XYZ', 17 => 'City', 18 => 'Idaho', 19 => '695038', 20 => 'US', 21 => '1234567890', 22 => '', 23 => '', 24 => 'Sreeprakash', 25 => 'N.', 26 => 'Schogini', 27 => 'XYZ', 28 => 'City', 29 => 'Idaho', 30 => '695038', 31 => 'US', 32 => '', 33 => '', 34 => '', 35 => '', 36 => '', 37 => '382065EC3B4C2F5CDC424A730393D2DF', 38 => '', 39 => '', 40 => '', 41 => '', 42 => '', 43 => '', 44 => '', 45 => '', 46 => '', 47 => '', 48 => '', 49 => '', 50 => '', 51 => '', 52 => '', 53 => '', 54 => '', 55 => '', 56 => '', 57 => '', 58 => '', 59 => '', 60 => '', 61 => '', 62 => '', 63 => '', 64 => '', 65 => '', 66 => '', 67 => ''); $sp70bfdc[7] = isset($sp8e19da['x_invoice_num']) ? $sp8e19da['x_invoice_num'] : ''; $sp70bfdc[8] = ''; $sp70bfdc[9] = $sp8e19da['x_amount']; $sp70bfdc[10] = $sp8e19da['x_method']; $sp70bfdc[11] = $sp8e19da['x_type']; $sp70bfdc[12] = $sp8e19da['x_cust_id']; $sp70bfdc[13] = $sp8e19da['x_first_name']; $sp70bfdc[14] = $sp8e19da['x_last_name']; $sp70bfdc[15] = $sp8e19da['x_company']; $sp70bfdc[16] = $sp8e19da['x_address']; $sp70bfdc[17] = $sp8e19da['x_city']; $sp70bfdc[18] = $sp8e19da['x_state']; $sp70bfdc[19] = $sp8e19da['x_zip']; $sp70bfdc[20] = $sp8e19da['x_country']; $sp70bfdc[21] = $sp8e19da['x_phone']; $sp70bfdc[22] = $sp8e19da['x_fax']; $sp70bfdc[23] = ''; $sp8e19da['x_ship_to_first_name'] = !isset($sp8e19da['x_ship_to_first_name']) ? $sp8e19da['x_first_name'] : $sp8e19da['x_ship_to_first_name']; $sp8e19da['x_ship_to_first_name'] = !isset($sp8e19da['x_ship_to_first_name']) ? $sp8e19da['x_first_name'] : $sp8e19da['x_ship_to_first_name']; $sp8e19da['x_ship_to_last_name'] = !isset($sp8e19da['x_ship_to_last_name']) ? $sp8e19da['x_last_name'] : $sp8e19da['x_ship_to_last_name']; $sp8e19da['x_ship_to_company'] = !isset($sp8e19da['x_ship_to_company']) ? $sp8e19da['x_company'] : $sp8e19da['x_ship_to_company']; $sp8e19da['x_ship_to_address'] = !isset($sp8e19da['x_ship_to_address']) ? $sp8e19da['x_address'] : $sp8e19da['x_ship_to_address']; $sp8e19da['x_ship_to_city'] = !isset($sp8e19da['x_ship_to_city']) ? $sp8e19da['x_city'] : $sp8e19da['x_ship_to_city']; $sp8e19da['x_ship_to_state'] = !isset($sp8e19da['x_ship_to_state']) ? $sp8e19da['x_state'] : $sp8e19da['x_ship_to_state']; $sp8e19da['x_ship_to_zip'] = !isset($sp8e19da['x_ship_to_zip']) ? $sp8e19da['x_zip'] : $sp8e19da['x_ship_to_zip']; $sp8e19da['x_ship_to_country'] = !isset($sp8e19da['x_ship_to_country']) ? $sp8e19da['x_country'] : $sp8e19da['x_ship_to_country']; $sp70bfdc[24] = $sp8e19da['x_ship_to_first_name']; $sp70bfdc[25] = $sp8e19da['x_ship_to_last_name']; $sp70bfdc[26] = $sp8e19da['x_ship_to_company']; $sp70bfdc[27] = $sp8e19da['x_ship_to_address']; $sp70bfdc[28] = $sp8e19da['x_ship_to_city']; $sp70bfdc[29] = $sp8e19da['x_ship_to_state']; $sp70bfdc[30] = $sp8e19da['x_ship_to_zip']; $sp70bfdc[31] = $sp8e19da['x_ship_to_country']; $sp70bfdc[0] = '1'; $sp70bfdc[1] = '1'; $sp70bfdc[2] = '1'; $sp70bfdc[3] = '(TESTMODE2) This transaction has been approved.'; $sp70bfdc[4] = '000000'; $sp70bfdc[5] = 'P'; $sp70bfdc[6] = '0'; $sp70bfdc[37] = '382065EC3B4C2F5CDC424A730393D2DF'; $sp70bfdc[39] = ''; $sp5d9f0c = $this->_firstdataglobalgateway($sp8e19da); $sp70bfdc[0] = $sp5d9f0c['response_code']; $sp70bfdc[1] = $sp5d9f0c['response_subcode']; $sp70bfdc[2] = $sp5d9f0c['response_reason_code']; $sp70bfdc[3] = $sp5d9f0c['response_reason_text']; $sp70bfdc[4] = $sp5d9f0c['approval_code']; $sp70bfdc[5] = $sp5d9f0c['avs_result_code']; $sp70bfdc[6] = $sp5d9f0c['transaction_id']; $sp70bfdc[37] = $sp5d9f0c['md5_hash']; $sp70bfdc[39] = $sp5d9f0c['card_code_response']; if ($sp70bfdc) { $spde1bee->setResponseCode((int) str_replace('"', '', $sp70bfdc[0])); $spde1bee->setResponseSubcode((int) str_replace('"', '', $sp70bfdc[1])); $spde1bee->setResponseReasonCode((int) str_replace('"', '', $sp70bfdc[2])); $spde1bee->setResponseReasonText($sp70bfdc[3]); $spde1bee->setApprovalCode($sp70bfdc[4]); $spde1bee->setAvsResultCode($sp70bfdc[5]); $spde1bee->setTransactionId($sp70bfdc[6]); $spde1bee->setInvoiceNumber($sp70bfdc[7]); $spde1bee->setDescription($sp70bfdc[8]); $spde1bee->setAmount($sp70bfdc[9]); $spde1bee->setMethod($sp70bfdc[10]); $spde1bee->setTransactionType($sp70bfdc[11]); $spde1bee->setCustomerId($sp70bfdc[12]); $spde1bee->setMd5Hash($sp70bfdc[37]); $spde1bee->setCardCodeResponseCode($sp70bfdc[39]); } else { self::throwException(__('Error in payment gateway')); } return $spde1bee; } function _firstdataglobalgateway($sp8e19da) { $spcd2d3c = $this->decrypt($this->getConfigData('username')); $sp074e8a = $this->getConfigData('gatewayid'); $sp6aba50 = $this->decrypt($this->getConfigData('gatewaypass')); $spb58992 = $this->getConfigData('process_level3_data'); $spb94ad1 = $this->getConfigData('process_soft_descriptor'); $sp6c0ae0 = $this->getConfigData('test'); $sp408544 = ''; $sp4cfa8f = ''; $spb0d90e = array('Card_Number' => '', 'Expiry_Date' => '', 'CardHoldersName' => '', 'VerificationStr2' => ''); $sp7e7b81 = str_replace(',', '', number_format($sp8e19da['x_amount'], 2)); $spaafab9 = substr($sp8e19da['x_exp_date'], 0, 2) . substr($sp8e19da['x_exp_date'], -2); $sp3c3c45 = trim($sp8e19da['x_address'] . ', ' . $sp8e19da['x_city'] . ',' . $sp8e19da['x_state'] . ',' . $sp8e19da['x_country']); $sp747e7b = htmlentities(xmlentities(trim($sp8e19da['x_first_name'] . ' ' . $sp8e19da['x_last_name'])), ENT_QUOTES, 'UTF-8'); $spc7da74 = htmlentities(xmlentities($sp3c3c45), ENT_QUOTES, 'UTF-8'); $sp6f02b5 = $sp8e19da['x_zip']; $sp7a374e = trim($sp8e19da['x_first_name'] . ' ' . $sp8e19da['x_last_name']); $spf451ba = trim($sp8e19da['x_address']) . '|' . trim($sp8e19da['x_zip']) . '|' . trim($sp8e19da['x_city']) . '|' . trim($sp8e19da['x_state']) . '|' . trim($sp8e19da['x_country']); switch ($sp8e19da['x_type']) { case 'AUTH_CAPTURE': $sp3714b1 = '00'; $spb0d90e = array('Card_Number' => $sp8e19da['x_card_num'], 'Expiry_Date' => $spaafab9, 'CardHoldersName' => $sp7a374e, 'VerificationStr2' => $sp8e19da['x_card_code']); break; case 'CAPTURE_ONLY': case 'PRIOR_AUTH_CAPTURE': $spb58992 = false; $sp3714b1 = '32'; list($sp408544, $sp4cfa8f) = explode('|', $sp8e19da['x_trans_id']); break; case 'VOID': $spb58992 = false; $sp3714b1 = '33'; list($sp408544, $sp4cfa8f) = explode('|', $sp8e19da['x_trans_id']); break; case 'REFUND': $spb58992 = false; $sp3714b1 = '34'; list($sp408544, $sp4cfa8f) = explode('|', $sp8e19da['x_trans_id']); break; case 'AUTH_ONLY': default: $sp3714b1 = '01'; $spb0d90e = array('Card_Number' => $sp8e19da['x_card_num'], 'Expiry_Date' => $spaafab9, 'CardHoldersName' => $sp7a374e, 'VerificationStr2' => $sp8e19da['x_card_code']); } $spe02d30 = array(); if ($spb58992) { $sp61cfb0 = $this->getConfigData('commodity_code'); $sp570f72 = $this->getConfigData('unit_of_measure'); $sped9616 = array(); foreach ($sp8e19da['x_line_items']['items'] as $sp0496c8) { $sped9616[] = array('CommodityCode' => isset($sp0496c8['commodity_code']) && !empty($sp0496c8['commodity_code']) ? $sp0496c8['commodity_code'] : $sp61cfb0, 'Description' => $sp0496c8['item_name'], 'DiscountAmount' => $sp0496c8['individual_discount'], 'DiscountIndicator' => 1, 'GrossNetIndicator' => 1, 'LineItemTotal' => $sp0496c8['individual_total'], 'ProductCode' => $sp0496c8['item_number'], 'Quantity' => $sp0496c8['quantity'], 'TaxAmount' => $sp0496c8['tax'], 'TaxRate' => number_format($sp0496c8['tax'] * 100 / $sp0496c8['base_price'], 2, '.', ''), 'TaxType' => 0, 'UnitCost' => $sp0496c8['base_price'], 'UnitOfMeasure' => isset($sp0496c8['unit_of_measure']) && !empty($sp0496c8['unit_of_measure']) ? $sp0496c8['unit_of_measure'] : $sp570f72); } $spe02d30 = array('TaxAmount' => $sp8e19da['x_line_items']['tax_amount'], 'DiscountAmount' => $sp8e19da['x_line_items']['discount_amount'], 'DutyAmount' => '0.00', 'FreightAmount' => $sp8e19da['x_line_items']['shipping_amount'], 'ShipToAddress' => array('Address1' => $sp8e19da['x_ship_to_address'], 'City' => $sp8e19da['x_ship_to_city'], 'State' => $sp8e19da['x_ship_to_state'], 'Zip' => $sp8e19da['x_ship_to_zip'], 'Country' => $sp8e19da['x_ship_to_country'], 'CustomerNumber' => $sp8e19da['x_invoice_num'], 'Email' => $sp8e19da['x_email'], 'Name' => $sp8e19da['x_ship_to_first_name'] . ' *' . $sp8e19da['x_ship_to_last_name'], 'Phone' => $sp8e19da['x_phone']), 'LineItem' => $sped9616); } $sp860a44 = array(); if ($spb94ad1) { $sp860a44 = array('DBAName' => $this->getConfigData('dbaname'), 'Street' => $this->getConfigData('street'), 'City' => $this->getConfigData('city'), 'Region' => $this->getConfigData('region'), 'PostalCode' => $this->getConfigData('postalcode'), 'CountryCode' => $this->getConfigData('countrycode'), 'MID' => $this->getConfigData('mid'), 'MCC' => $this->getConfigData('mcc'), 'MerchantContactInfo' => $this->getConfigData('merchantcontactinfo')); } if ($sp6c0ae0) { $spcca09d = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v11/wsdl'; } else { $spcca09d = 'https://api.globalgatewaye4.firstdata.com/transaction/v11/wsdl'; } $sp31a9e1 = array_merge($spb0d90e, array('User_Name' => $spcd2d3c, 'ExactID' => $sp074e8a, 'Password' => $sp6aba50, 'Secure_AuthResult' => '', 'Ecommerce_Flag' => '0', 'XID' => '', 'CAVV' => '', 'CAVV_Algorithm' => '', 'Transaction_Type' => $sp3714b1, 'Reference_No' => $sp8e19da['x_invoice_num'], 'Customer_Ref' => '', 'Reference_3' => '', 'Client_IP' => $_SERVER['REMOTE_ADDR'], 'Client_Email' => $sp8e19da['x_email'], 'Language' => 'en', 'Track1' => '', 'Track2' => '', 'Authorization_Num' => $sp408544, 'Transaction_Tag' => $sp4cfa8f, 'DollarAmount' => $sp7e7b81, 'VerificationStr1' => $spf451ba, 'CVD_Presence_Ind' => '', 'Secure_AuthRequired' => '', 'Currency' => '', 'PartialRedemption' => '', 'ZipCode' => $sp8e19da['x_zip'], 'Tax1Amount' => '', 'Tax1Number' => '', 'Tax2Amount' => '', 'Tax2Number' => '', 'SurchargeAmount' => '', 'PAN' => '')); if ($spb58992) { $sp31a9e1['Level3'] = $spe02d30; } if ($spb94ad1) { $sp31a9e1['SoftDescriptor'] = $sp860a44; } $this->logit('----- Transaction Request -----' . '
' . print_r($sp31a9e1, 1)); $sp6dfcb3 = ''; try { $sp65a269 = array('Transaction' => $sp31a9e1); $spbfab41 = new \SoapClient($spcca09d); $sp2aef38 = $spbfab41->__soapCall('SendAndCommit', $sp65a269); } catch (\SoapFault $spc1314a) { $sp9ff2f3 = ''; if ($spc1314a->faultcode == 'HTTP') { $sp9ff2f3 = 'Invalid Credentials'; } else { $sp9ff2f3 = '(' . $spc1314a->faultcode . ')' . $spc1314a->getMessage(); } $sp6dfcb3 = 'SOAP Fault: ' . $sp9ff2f3; $this->logit('----- SOAP Fault -----' . '
' . print_r($sp9ff2f3, 1)); self::throwException(__('SOAP Fault: ' . $sp9ff2f3)); } catch (\Exception $spc1314a) { $sp9ff2f3 = $spc1314a->getMessage(); if (empty($sp9ff2f3)) { $sp9ff2f3 = 'Unknown error'; } $sp6dfcb3 = 'SOAP Exception: ' . $sp9ff2f3; $this->logit('----- SOAP Exception -----' . '
' . print_r($sp9ff2f3, 1)); self::throwException(__('SOAP Exception: ' . $sp9ff2f3)); } if (@$spbfab41->fault) { $sp704c2e = $spbfab41->faultstring . '(' . $spbfab41->faultcode . ')'; $this->logit('----- Error -----' . '
' . print_r($sp704c2e, 1)); self::throwException(__('Error: ' . $sp704c2e)); } $this->logit('----- Result -----' . '
' . print_r($sp2aef38, 1)); $sp5d9f0c = array(); $sp5d9f0c['response_code'] = '1'; $sp5d9f0c['response_subcode'] = '1'; $sp5d9f0c['response_reason_code'] = '1'; $sp5d9f0c['response_reason_text'] = '(TESTMODE2) This transaction has been approved.'; $sp5d9f0c['approval_code'] = '000000'; $sp5d9f0c['avs_result_code'] = 'P'; $sp5d9f0c['transaction_id'] = '0'; $sp5d9f0c['md5_hash'] = '382065EC3B4C2F5CDC424A730393D2DF'; $sp5d9f0c['card_code_response'] = ''; if (!isset($sp2aef38->Bank_Resp_code) && isset($sp2aef38->Bank_Resp_Code)) { $sp2aef38->Bank_Resp_code = $sp2aef38->Bank_Resp_Code; } else { if (!isset($sp2aef38->Bank_Resp_Code) && isset($sp2aef38->Bank_Resp_code)) { $sp2aef38->Bank_Resp_Code = $sp2aef38->Bank_Resp_code; } } if ($sp2aef38->Transaction_Approved == 1) { $sp5d9f0c['transaction_id'] = $sp2aef38->Authorization_Num . '|' . $sp2aef38->Transaction_Tag; $sp5d9f0c['approval_code'] = $sp2aef38->SequenceNo; $sp5d9f0c['response_reason_text'] = $sp2aef38->Bank_Message . '-' . $sp2aef38->Bank_Resp_Code; if ($sp2aef38->Bank_Resp_Code == '' || $sp2aef38->Bank_Resp_Code == '000' || $sp2aef38->Bank_Resp_Code == '00') { $sp5d9f0c['response_reason_text'] = $sp2aef38->Exact_Message . '-' . $sp2aef38->Exact_Resp_code; } $sp5d9f0c['avs_result_code'] = $sp2aef38->AVS; $sp5d9f0c['response_reason_text'] .= '
Transaction ID: ' . $sp2aef38->Authorization_Num; $sp5d9f0c['response_reason_text'] .= '
AVS Response: ' . $this->getAvsResponseText($sp2aef38->AVS) . '[' . $sp2aef38->AVS . ']'; $sp5d9f0c['response_reason_text'] .= '
CVV Response: ' . $this->getCvvResponseText($sp2aef38->CVV2) . '[' . $sp2aef38->CVV2 . ']'; $sp5d9f0c['response_code'] = '1'; $sp5d9f0c['response_subcode'] = '1'; $sp5d9f0c['response_reason_code'] = '1'; if ($sp2aef38->AVS == '8' || $sp2aef38->CVV2 == 'N') { $spdeafb0 = $this->getConfigData('merchant_email'); $spfa6aeb = 'Inv Num #' . $sp8e19da['x_invoice_num'] . ': AVS or CCV failed'; $sp9ff2f3 = $sp5d9f0c['response_reason_text'] . '
'; $sp9ff2f3 .= 'AVS Code: ' . $sp2aef38->AVS . '
'; $sp9ff2f3 .= 'CVV Code: ' . $sp2aef38->CVV2 . '
'; $sp9ff2f3 .= 'Transaction ID: ' . $sp2aef38->Authorization_Num . '
'; $sp9ff2f3 .= 'Approval Code: ' . $sp5d9f0c['approval_code'] . '
'; $sp9ff2f3 .= '
CUSTOMER INFO
'; $sp9ff2f3 .= "\nName: {$sp8e19da['x_first_name']}, {$sp8e19da['x_last_name']}\nAddress: {$sp8e19da['x_address']}\nCity: {$sp8e19da['x_city']}\nState: {$sp8e19da['x_state']}\nZip: {$sp8e19da['x_zip']}\nCountry: {$sp8e19da['x_country']}\nEmail:{$sp8e19da['x_email']}\nPhone:{$sp8e19da['x_phone']}\n"; } } else { $sp5d9f0c['response_code'] = '0'; $sp5d9f0c['response_subcode'] = '0'; $sp5d9f0c['response_reason_code'] = '0'; $sp5d9f0c['response_reason_text'] = $this->getResponseReason($sp2aef38->Bank_Resp_Code); if ($sp2aef38->Bank_Resp_Code == '' || $sp2aef38->Bank_Resp_Code == '000' || $sp2aef38->Bank_Resp_Code == '00') { $sp5d9f0c['response_reason_text'] = $this->getResponseReason($sp2aef38->EXact_Resp_Code); } $sp5d9f0c['approval_code'] = '000000'; $sp5d9f0c['avs_result_code'] = 'P'; $sp5d9f0c['transaction_id'] = '0'; } $this->logit('----- Return Array -----' . '
' . print_r($sp2aef38, 1)); return $sp5d9f0c; } function getLineItems($spd8cfc4) { $sp52f7e8 = array(); $sp4e1fe1 = array(); $spa2635a = 0; $spf06e5d = 0; $spcb6adf = 0; $sp73e82f = $spd8cfc4->getAllItems(); if ($sp73e82f) { $sp6c8a6e = 0; foreach ($sp73e82f as $sp0496c8) { if ($sp0496c8->getParentItem()) { continue; } if ($sp0496c8->getQty() == '') { $sp059732 = 0; if ($sp0496c8->getBaseTaxAmount() > 0) { $sp059732 = $sp0496c8->getBaseTaxAmount() / $sp0496c8->getQtyOrdered(); } $sp51ab70 = $sp0496c8->getBaseDiscountAmount() / $sp0496c8->getQtyOrdered(); $sp2093ce = $sp0496c8->getCommodityCode(); $sp870b4d = $sp0496c8->getUnitOfMeasure(); $sp52f7e8[$sp6c8a6e] = array('item_name' => $sp0496c8->getName(), 'item_number' => $sp0496c8->getSku(), 'quantity' => sprintf('%d', $sp0496c8->getQtyOrdered()), 'individual_discount' => sprintf('%.2f', $sp51ab70), 'amount' => sprintf('%.2f', $sp0496c8->getBasePrice() - $sp51ab70 + $sp059732), 'base_price' => sprintf('%.2f', $sp0496c8->getBasePrice() - $sp51ab70), 'individual_total' => sprintf('%.2f', ($sp0496c8->getBasePrice() - $sp51ab70 + $sp059732) * $sp0496c8->getQtyOrdered()), 'tax' => sprintf('%.2f', $sp059732), 'commodity_code' => $sp2093ce, 'unit_of_measure' => $sp870b4d); $spa2635a += ($sp0496c8->getBasePrice() - $sp51ab70 + $sp059732) * $sp0496c8->getQtyOrdered(); $spf06e5d += $sp059732 * $sp0496c8->getQtyOrdered(); $spcb6adf += $sp51ab70 * $sp0496c8->getQtyOrdered(); } else { $sp059732 = 0; $spc355eb = $sp0496c8->getBaseDiscountAmount(); if ($sp0496c8->getBaseTaxAmount() > 0) { $sp059732 = $sp0496c8->getBaseTaxAmount() / $sp0496c8->getQty(); } $sp51ab70 = $sp0496c8->getBaseDiscountAmount() / $sp0496c8->getQty(); $sp2093ce = $sp0496c8->getCommodityCode(); $sp870b4d = $sp0496c8->getUnitOfMeasure(); $sp52f7e8[$sp6c8a6e] = array('item_name' => $sp0496c8->getName(), 'item_number' => $sp0496c8->getSku(), 'quantity' => sprintf('%d', $sp0496c8->getQty()), 'individual_discount' => sprintf('%.2f', $sp51ab70), 'amount' => sprintf('%.2f', $sp0496c8->getBaseCalculationPrice() - $sp0496c8->getBaseDiscountAmount() + $sp059732), 'base_price' => sprintf('%.2f', $sp0496c8->getBaseCalculationPrice() - $sp51ab70), 'individual_total' => sprintf('%.2f', ($sp0496c8->getBaseCalculationPrice() - $sp51ab70 + $sp059732) * $sp0496c8->getQty()), 'tax' => sprintf('%.2f', $sp059732), 'commodity_code' => $sp2093ce, 'unit_of_measure' => $sp870b4d); $spa2635a += ($sp0496c8->getBaseCalculationPrice() - $sp51ab70 + $sp059732) * $sp0496c8->getQty(); $spf06e5d += $sp059732 * $sp0496c8->getQtyOrdered(); $spcb6adf += $sp51ab70 * $sp0496c8->getQtyOrdered(); } $sp6c8a6e++; } } $sp4e1fe1['items'] = $sp52f7e8; $sp4e1fe1['shipping_desc'] = $spd8cfc4->getShippingDescription(); $sp4e1fe1['shipping_amount'] = sprintf('%.2f', $spd8cfc4->getShippingAmount() + $spd8cfc4->getShippingTaxAmount()); $sp4e1fe1['subtotal_amount'] = sprintf('%.2f', $spa2635a); $sp4e1fe1['tax_amount'] = sprintf('%.2f', $spf06e5d); $sp4e1fe1['discount_amount'] = sprintf('%.2f', $spcb6adf); $sp4e1fe1['grandtotal_amount'] = sprintf('%.2f', $sp4e1fe1['shipping_amount'] + $sp4e1fe1['subtotal_amount']); return $sp4e1fe1; } function getResponseReason($spf78a49) { $spf78a49 = trim($spf78a49); $specd431 = array('00' => 'Transaction Normal', '08' => 'CVV2/CID/CVC2 Data not verified', '22' => 'Invalid Credit Card Number', '25' => 'Invalid Expiry Date', '26' => 'Invalid Amount', '27' => 'Invalid Card Holder', '28' => 'Invalid Authorization No', '31' => 'Invalid Verification String', '32' => 'Invalid Transaction Code', '57' => 'Invalid Reference No', '58' => 'Invalid AVS String, The length of the AVS String has exceeded the max. 40 characters', '60' => 'Invalid Customer Reference Number', '63' => 'Invalid Duplicate', '64' => 'Invalid Refund', '68' => 'Restricted Card Number', '72' => 'Data within the transaction is incorrect', '93' => 'Invalid authorization number entered on a pre-auth completion', '11' => 'Invalid Sequence No', '12' => 'Message Timed-out at Host', '21' => 'BCE Function Error', '23' => 'Invalid Response from First Data', '30' => 'Invalid Date From Host', '10' => 'Invalid Transaction Description', '14' => 'Invalid Gateway ID', '15' => 'Invalid Transaction Number', '16' => 'Connection Inactive', '17' => 'Unmatched Transaction', '18' => 'Invalid Reversal Response', '19' => 'Unable to Send Socket Transaction', '20' => 'Unable to Write Transaction to File', '24' => 'Unable to Void Transaction', '40' => 'Unable to Connect', '41' => 'Unable to Send Logon', '42' => 'Unable to Send Trans', '43' => 'Invalid Logon', '52' => 'Terminal not Activated', '53' => 'Terminal/Gateway Mismatch', '54' => 'Invalid Processing Center', '55' => 'No Processors Available', '56' => 'Database Unavailable', '61' => 'Socket Error', '62' => 'Host not Ready', '44' => 'Address not Verified', '70' => 'Transaction Placed in Queue', '73' => 'Transaction Received from Bank', '76' => 'Reversal Pending', '77' => 'Reversal Complete', '79' => 'Reversal Sent to Bank', 'F1' => 'Address check failed - Fraud suspected', 'F2' => 'Card/Check Number check failed - Fraud suspected', 'F3' => 'Country Check Failed - Fraud Suspected', 'F4' => 'Customer Reference Check Failed - Fraud Suspected', 'F5' => 'Email Address check failed - Fraud suspected', 'F6' => 'IP Address check failed - Fraud suspected'); if (!isset($specd431[$spf78a49]) || empty($specd431[$spf78a49])) { $sp9ff2f3 = 'Unknown reason'; } else { $sp9ff2f3 = $specd431[$spf78a49]; } return 'Unable to process order. Please check entered credit card information and try again or use a different payment method.
Processor response: (' . $spf78a49 . ')'; } function getAvsResponseText($sp5044c9) { $sp5044c9 = trim($sp5044c9); $sp9ff2f3 = 'Unrecognized response'; switch ($sp5044c9) { case 'X': $sp9ff2f3 = 'exact match, 9 digit zip'; break; case 'Y': $sp9ff2f3 = 'exact match, 5 digit zip'; break; case 'A': $sp9ff2f3 = 'address match only'; break; case 'W': $sp9ff2f3 = '9 digit zip match only'; break; case 'Z': $sp9ff2f3 = '5 digit zip match only'; break; case 'N': $sp9ff2f3 = 'no address or zip match'; break; case 'U': $sp9ff2f3 = 'address unavailable'; break; case 'G': $sp9ff2f3 = 'non-North American issuer, does not participate'; break; case 'R': $sp9ff2f3 = 'issuer system unavailable'; break; case 'E': $sp9ff2f3 = 'not a Mail/Phone order'; break; case 'S': $sp9ff2f3 = 'service not supported'; break; case 'Q': $sp9ff2f3 = 'Bill to address did not pass edit checks'; break; case 'D': $sp9ff2f3 = 'International street address and postal code match'; break; case 'B': $sp9ff2f3 = 'International street address match, postal code not verified due to incompatable formats'; break; case 'C': $sp9ff2f3 = 'International street address and postal code not verified due to incompatable formats'; break; case 'P': $sp9ff2f3 = 'International postal code match, street address not verified due to incompatable format'; break; case '1': $sp9ff2f3 = 'Cardholder name matches'; break; case '2': $sp9ff2f3 = 'Cardholder name, billing address, and postal code match'; break; case '3': $sp9ff2f3 = 'Cardholder name and billing postal code match'; break; case '4': $sp9ff2f3 = 'Cardholder name and billing address match'; break; case '5': $sp9ff2f3 = 'Cardholder name incorrect, billing address and postal code match'; break; case '6': $sp9ff2f3 = 'Cardholder name incorrect, billing postal code matches'; break; case '7': $sp9ff2f3 = 'Cardholder name incorrect, billing address matches'; break; case '8': $sp9ff2f3 = 'Cardholder name, billing address, and postal code are all incorrect'; break; } return $sp9ff2f3; } function getCvvResponseText($spb1257c) { $spb1257c = trim($spb1257c); $sp9ff2f3 = 'Unrecognized response'; switch ($spb1257c) { case 'M': $sp9ff2f3 = 'CVV2 / CVC2/CVD Match.'; break; case 'N': $sp9ff2f3 = 'CVV2 / CVC2/CVD No Match.'; break; case 'P': $sp9ff2f3 = 'Not Processed.'; break; case 'S': $sp9ff2f3 = 'Merchant has indicated that CVV2 / CVC2/CVD is not present on the card.'; break; case 'U': $sp9ff2f3 = 'Issuer is not certified and / or has not provided Visa encryption keys.'; break; } return $sp9ff2f3; } public static function throwException($sp9ff2f3 = null) { if (is_null($sp9ff2f3)) { $sp9ff2f3 = __('Payment error occurred.'); } throw new FirstdataglobalgatewayException($sp9ff2f3); } public static function getResourceModel($sp7b79de) { $sp1f1960 = \Magento\Framework\App\ObjectManager::getInstance(); return $sp1f1960->get($sp7b79de); } public function decrypt($sp216a53) { return self::getResourceModel('\\Magento\\Framework\\Encryption\\EncryptorInterface')->decrypt($sp216a53); } public function encrypt($sp216a53) { return self::getResourceModel('\\Magento\\Framework\\Encryption\\EncryptorInterface')->encrypt($sp216a53); } function logit($sp9dbf3b, $sp291e01 = array()) { if (!$this->getConfigData('debug') || !$this->getConfigData('test')) { return; } $sp4a7294 = ''; if (count($sp291e01) > 0) { $sp4a7294 = var_export($sp291e01, true); } $sp8cf5dd = self::getResourceModel('\\Magento\\Framework\\Logger\\Monolog'); $sp8cf5dd->addRecord(LOGGER::INFO, '----- Inside ' . $sp9dbf3b . ': ' . date('d/M/Y H:i:s') . ' -----' . '
' . $sp4a7294); } } if (!function_exists('xmlentities')) { function xmlentities($sp216a53) { $sp6c047c = 'A-Z0-9a-z\\s_-'; return preg_replace_callback("/[^{$sp6c047c}]/", function ($spf09658) { if (!is_string($spf09658[0]) || strlen($spf09658[0]) > 1) { die("function: 'get_xml_entity_at_index_0' requires data type: 'char' (single character). '{$spf09658[0]}' does not match this type."); } switch ($spf09658[0]) { case '\'': case '"': case '&': case '<': case '>': return htmlspecialchars($spf09658[0], ENT_QUOTES); break; default: return '&#' . str_pad(ord($spf09658[0]), 3, '0', STR_PAD_LEFT) . ';'; break; } }, $sp216a53); } }