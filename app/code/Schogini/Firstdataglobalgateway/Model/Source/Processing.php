<?php
namespace Schogini\Firstdataglobalgateway\Model\Source; class Processing extends \Magento\Sales\Model\Config\Source\Order\Status { protected $_stateStatuses = array(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_PROCESSING, \Magento\Sales\Model\Order::STATE_COMPLETE, \Magento\Sales\Model\Order::STATE_CLOSED); }