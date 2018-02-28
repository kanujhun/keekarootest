<?php
namespace Magenest\EmailNotifications\Model\ResourceModel\Main;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'product_id';

    protected function _construct()
    {
        $this->_init('Magenest\EmailNotifications\Model\Main', 'Magenest\EmailNotifications\Model\ResourceModel\Main');
    }
}
