<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rma\Test\TestStep;

use Magento\Rma\Test\Fixture\RmaAttribute;
use Magento\Rma\Test\Page\Adminhtml\RmaAttributeIndex;
use Magento\Rma\Test\Page\Adminhtml\RmaAttributeEdit;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Delete Rma attribute.
 */
class DeleteAttributeStep implements TestStepInterface
{
    /**
     * Rma Attribute Index page.
     *
     * @var RmaAttributeIndex
     */
    private $rmaAttributeIndex;

    /**
     * Rma Attribute New page.
     *
     * @var RmaAttributeEdit
     */
    private $rmaAttributeEdit;

    /**
     * RmaAttribute fixture.
     *
     * @var RmaAttribute
     */
    private $attribute;

    /**
     * @param RmaAttributeIndex $rmaAttributeIndex
     * @param RmaAttributeEdit $rmaAttributeEdit
     * @param RmaAttribute $attribute
     */
    public function __construct(
        RmaAttributeIndex $rmaAttributeIndex,
        RmaAttributeEdit $rmaAttributeEdit,
        RmaAttribute $attribute
    ) {
        $this->rmaAttributeIndex = $rmaAttributeIndex;
        $this->rmaAttributeEdit = $rmaAttributeEdit;
        $this->attribute = $attribute;
    }

    /**
     * Delete Rma attribute step.
     *
     * @return void
     */
    public function run()
    {
        $filter = ['attribute_code' => $this->attribute->getAttributeCode()];
        $this->rmaAttributeIndex->open();
        if ($this->rmaAttributeIndex->getGrid()->isRowVisible($filter)) {
            $this->rmaAttributeEdit->open(['attribute_id' => $this->attribute->getAttributeId()]);
            $this->rmaAttributeEdit->getPageActions()->delete();
            $this->rmaAttributeEdit->getModalBlock()->acceptAlert();
        }
    }
}
