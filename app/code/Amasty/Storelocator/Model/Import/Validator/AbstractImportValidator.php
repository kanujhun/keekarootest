<?php

namespace Amasty\Storelocator\Model\Import\Validator;

use Magento\Framework\Validator\AbstractValidator;

abstract class AbstractImportValidator extends AbstractValidator implements RowValidatorInterface
{
    protected $context;

    public function init($context)
    {
        $this->context = $context;
        return $this;
    }
}
