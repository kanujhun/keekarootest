<?php

namespace Ey\Product\Model;

class Product extends \Magento\Catalog\Model\Product
{
    /**
     * @return bool
     */
    public function isNewProduct()
    {
        $newsFromDate = $this->getNewsFromDate();
        $newsToDate = $this->getNewsToDate();

        if(!$newsFromDate || !$newsToDate){
            return false;
        }

        $today = date("Y-m-d");
        $today_time = strtotime($today);
        $newsFromDate = strtotime($newsFromDate);
        $newsToDate = strtotime($newsToDate);

        if(
            $newsFromDate > $today_time ||
            $newsToDate < $today_time
        ){
            return false;
        }

        return true;
    }
}