<?php
namespace Magenest\EmailNotifications\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Main extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('review_details', 'details_id');
    }
}
