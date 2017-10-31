<?php

namespace Ey\BundleQuantity\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param array $params
     * @return string
     */
    public function getActionUrl($params = array())
    {
        return $this->_urlBuilder->getUrl('checkout/bundleproduct/editqty', $params);
    }

    /**
     * @param array $params
     * @return string
     */
    public function getSubmitUrl($params = array())
    {
        return $this->_urlBuilder->getUrl('checkout/bundleproduct/updateqty', $params);
    }
}