<?php

namespace Ey\MageCore\Block\Catalog\Product;

class Popup extends \Magento\Framework\View\Element\Template
{
    const SUCCESS_PAGE_URL = 'eystudios/catalog/popup';

    public function _construct()
    {
        parent::_construct();
    }

    public function getActionUrl()
    {
        return $this->getBaseUrl() . self::SUCCESS_PAGE_URL;
    }
}