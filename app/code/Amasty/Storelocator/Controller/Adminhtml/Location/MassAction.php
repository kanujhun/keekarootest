<?php

namespace Amasty\Storelocator\Controller\Adminhtml\Location;


class MassAction extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    public function execute()
    {

        $ids = $this->getRequest()->getParam('location');
        $action = $this->getRequest()->getParam('action');
        if ($ids && in_array($action, ['activate', 'inactivate', 'delete'])) {
            try {
                /**
                 * @var $collection \Amasty\Storelocator\Model\ResourceModel\Location\Collection
                 */
                $collection = $this->_objectManager->create('Amasty\Storelocator\Model\ResourceModel\Location\Collection');

                $collection->addFieldToFilter('id', array('in'=>$ids));
                $collection->walk($action);
                $this->messageManager->addSuccess(__('You deleted the location(s).'));
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete location(s) right now. Please review the log and try again.').$e->getMessage()
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('*/*/');
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a location(s) to delete.'));
        $this->_redirect('*/*/');
    }
}