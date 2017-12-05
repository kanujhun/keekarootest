<?php

namespace Amasty\Storelocator\Controller\Adminhtml\Location;

use Magento\Framework\App\ResponseInterface;

class NewAction extends \Amasty\Storelocator\Controller\Adminhtml\Location
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}