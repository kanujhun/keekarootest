<?php

namespace Amasty\Storelocator\Model\Import\Validator;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    const ERROR_INVALID_PHOTO = 'invalidPhoto';

    const ERROR_NAME_IS_EMPTY = 'emptyName';

    const ERROR_ID_IS_EMPTY = 'emptyId';

    const ERROR_COUNTRY_IS_EMPTY = 'emptyCountry';
    
    const ERROR_MEDIA_URL_NOT_ACCESSIBLE = 'cantGetPhoto';

    /**
     * Initialize validator
     *
     * @param $context
     * @return $this
     */
    public function init($context);
}
