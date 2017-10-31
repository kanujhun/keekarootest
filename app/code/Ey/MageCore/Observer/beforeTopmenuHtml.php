<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ey\MageCore\Observer;


class beforeTopmenuHtml extends \Magento\Catalog\Observer\MenuCategoryData
{
    /**
     * replace name with short name
     *
     * @param \Magento\Framework\Data\Tree\Node $category
     * @return array
     */
    public function aroundGetMenuCategoryData
    (
        \Magento\Catalog\Observer\MenuCategoryData $subject,
        \Closure $proceed,
        $category
    )
    {
        $categoryData = $proceed($category);
        $categoryData['short_name'] = $category->getShortName() ? $category->getShortName():$category->getName();

        return $categoryData;
    }
}
