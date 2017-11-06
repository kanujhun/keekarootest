<?php

namespace Ey\ProductRegister\Model\ResourceModel\Requests;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ey\ProductRegister\Model\Requests','Ey\ProductRegister\Model\ResourceModel\Requests');
    }
}