<?php
namespace Magenest\EmailNotifications\Model;

use Magenest\EmailNotifications\Model\ResourceModel\Main as ResourceMain;
use Magenest\EmailNotifications\Model\ResourceModel\Main\Collection as Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Main extends AbstractModel
{
    protected $_eventPrefix = 'magenest_EmailNotifications_main_';

    public function __construct(
        Context $context,
        Registry $registry,
        ResourceMain $resource,
        Collection $resourceCollection,
        $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
