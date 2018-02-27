<?php
/**
 * Created by PhpStorm.
 * User: hoangnew
 * Date: 11/04/2016
 * Time: 15:36
 */
namespace Magenest\EmailNotifications\Model\Config;

/**
 * Class Options
 * @package Magenest\EmailNotifications\Model\Config
 */
class OptionsStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'pending', 'label' => __('Pending')],
            ['value' => 'processing', 'label' => __('Processing')],
            ['value' => 'complete', 'label' => __('Complete')],
            ['value' => 'closed', 'label' => __('Closed')],
            ['value' => 'canceled', 'label' => __('Canceled')],
            ['value' => 'holded', 'label' => __('On Hold')],
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Pending'),
            1 => __('Processing'),
            2 => __('Complete'),
            3=> __('Closed'),
            4 => __('Canceled'),
            5 => __('On Hold')
        ];
    }
}
