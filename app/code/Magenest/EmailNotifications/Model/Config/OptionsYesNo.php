<?php
/**
 * Created by PhpStorm.
 * User: hoangnew
 * Date: 11/04/2016
 * Time: 15:36
 */
namespace Magenest\EmailNotifications\Model\Config;

class OptionsYesNo implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'no', 'label' => __('No')],
            ['value' => 'yes', 'label' => __('Yes')]];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [0 => __('No'), 1 => __('Yes')];
    }
}
