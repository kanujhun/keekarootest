<?php

namespace Ey\MageCore\Block\Page;

use \Magento\Framework\View\Element\Context;

class Html extends \Magento\Cms\Block\Block
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var string
     */
    protected $_xmlPath;

    /**
     * @var string
     */
    static $eyRegex = '/\[EY\](.*?)\[\/EY\]/s';

    /**
     * Html constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        array $data = []
    )
    {
        parent::__construct($context, $filterProvider, $storeManager, $blockFactory, $data);
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registry;
        if(array_key_exists('xml_path', $data)){
            $this->_xmlPath = $data['xml_path'];
        }
    }

    /**
     * Override getBlockId()
     *
     * @return string
     */
    public function getBlockId()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $blockId = $this->_scopeConfig->getValue($this->_xmlPath, $storeScope);
        return $blockId;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $html = parent::_toHtml();

        $matches = array();

        $success = preg_match_all(self::$eyRegex, $html, $matches);
        if ($success) {
            foreach($matches[0] as $key => $match){
                $regex = '|'.preg_quote($match).'|';
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $valueVal = $this->_scopeConfig->getValue($matches[1][$key], $storeScope);
                $html = preg_replace($regex, $valueVal, $html);
            }

            return $html;
        }

        return $html;
    }

    /**
     * Unused
     * @return array
     */
    public function eyMatchValue()
    {
        if(!$this->_coreRegistry->registry('ey_match_value')) {
            $xml_path = 'ey_general/global/match_value';
            $arrValues = array();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $values = $this->_scopeConfig->getValue($xml_path, $storeScope);
            $values = explode(',', $values);
            foreach($values as $value){
                $value = explode(':', $value);
                $arrValues[$value[0]] = $value[1];
            }

            $this->_coreRegistry->register('ey_match_value', $arrValues);
        }

        return $this->_coreRegistry->registry('ey_match_value');
    }

}