<?php

namespace Amasty\Storelocator\Model;

class Options extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Storelocator\Model\ResourceModel\Options');
        $this->setIdFieldName('value_id');
    }

    public function getOptions()
    {
        return parent::getData('options');
    }
}
