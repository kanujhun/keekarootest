<?php

namespace Ey\MageCore\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SCOPE_STORE = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $countryHelper;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\Config\Source\Country $country
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\Config\Source\Country $country
    )
    {
        $this->countryHelper = $country;
        $this->countryFactory = $countryFactory;
        return parent::__construct($context);    
    }

    /**
     * @param $xml_path
     * @return mixed
     */
    protected function _getConfig($xml_path)
    {
        return $this->scopeConfig->getValue($xml_path, self::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getStoreName()
    {
        return $this->_getConfig('general/store_information/name');
    }

    /**
     * @return string
     */
    public function getStorePhone()
    {
        return $this->_getConfig('general/store_information/phone');
    }

    /**
     * @return string
     */
    public function getStoreHours()
    {
        return $this->_getConfig('general/store_information/hours');
    }

    /**
     * @return string
     */
    public function getOutletHours()
    {
        return $this->_getConfig('general/store_information/outlet_hours');
    }

    /**
     * @return string
     */
    public function getStoreFax()
    {
        return $this->_getConfig('general/store_information/fax');
    }

    /**
     * @return string
     */
    public function getStoreLocalPhone()
    {
        return $this->_getConfig('general/store_information/local_phone');
    }

    /**
     * @return string
     */
    public function getStoreStreetAddress()
    {
        return $this->_getConfig('general/store_information/street_line1');
    }

    /**
     * @return string
     */
    public function getStoreCountry()
    {
        return $this->_getConfig('general/store_information/country_id');
    }

    /**
     * @return string
     */
    public function getStoreState()
    {
        $states = $this->countryFactory->create()->setId(
                $this->getStoreCountry()
            )->getLoadedRegionCollection()->toOptionArray();
        $state_value = $this->_getConfig('general/store_information/region_id');

        foreach ($states as $state){
            if($state['value'] == $state_value){
                return $state['title'];
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getStoreCity()
    {
        return $this->_getConfig('general/store_information/city');
    }

    /**
     * @return string
     */
    public function getStorePostCode()
    {
        return $this->_getConfig('general/store_information/postcode');
    }

    /**
     * @return string
     */
    public function getStoreAddress()
    {
        return sprintf(
            '%s <br> %s <br> %s, %s %s',
            $this->getStoreName(),
            $this->getStoreStreetAddress() . $this->_getConfig('general/store_information/street_line2'),
            $this->getStoreCity(),
            $this->getStoreState(),
            $this->getStorePostCode()
        );
    }
}