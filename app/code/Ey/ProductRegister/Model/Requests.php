<?php

namespace Ey\ProductRegister\Model;

class Requests extends \Magento\Framework\Model\AbstractModel implements RequestsInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'ey_productregister_requests';

    protected function _construct()
    {
        $this->_init('Ey\ProductRegister\Model\ResourceModel\Requests');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}