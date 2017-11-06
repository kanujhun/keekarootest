<?php
namespace Ey\ProductRegister\Block\Adminhtml;

class ProductRegister extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        parent::_construct();
        $this->removeButton('add');
    }
}