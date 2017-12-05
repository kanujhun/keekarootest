<?php

namespace Amasty\Storelocator\Model;

class Attribute extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Storelocator\Model\ResourceModel\Attribute');
        $this->setIdFieldName('attribute_id');
    }

}
