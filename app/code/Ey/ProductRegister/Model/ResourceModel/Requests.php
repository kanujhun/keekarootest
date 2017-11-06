<?php

namespace Ey\ProductRegister\Model\ResourceModel;

class Requests extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('ey_productregister','autoId');
    }
}