<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerCustomAttributes\Test\Block\Adminhtml\Customer\Address\Attribute;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Backend customer address attribute grid.
 */
class CustomerAddressAttributesGrid extends Grid
{
    /**
     * An element locator which allows to select first entity in grid.
     *
     * @var string
     */
    protected $editLink = 'td[class*=col-attribute_code]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'attribute_code' => [
            'selector' => 'input[name="attribute_code"]',
        ],
        'frontend_label' => [
            'selector' => 'input[name="frontend_label"]',
        ],
        'is_required' => [
            'selector' => 'select[name="is_required"]',
            'input' => 'select',
        ],
        'is_user_defined' => [
            'selector' => 'select[name="is_user_defined"]',
            'input' => 'select',
        ],
        'is_visible' => [
            'selector' => 'select[name="is_visible"]',
            'input' => 'select',
        ],
        'sort_order' => [
            'selector' => 'input[name="sort_order"]',
        ],
    ];
}
