<?php

namespace Ey\MageCore\Model\Config\Source;

class Block implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->_objectManager = $objectManager;
    }

    protected function _blockToArray()
    {
        $collection = $this->_objectManager->get('Magento\Cms\Model\ResourceModel\Block\Collection');
        $toOptArray = $collection->toOptionArray();
        array_unshift($toOptArray, array('value'=>'', 'label'=>"-----Select Static Block-----"));

        return $toOptArray;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_blockToArray();
    }
}
