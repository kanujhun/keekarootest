<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Catalog/_files/products.php';
require __DIR__ . '/../../CatalogSearch/_files/full_reindex.php';

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$installer = $objectManager->create(\Magento\Catalog\Setup\CategorySetup::class);
/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
$attribute->setData(
    [
        'attribute_code' => 'select_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'select',
        'is_unique' => 0,
        'is_required' => 1,
        'is_searchable' => 1,
        'is_visible_in_advanced_search' => 0,
        'is_comparable' => 0,
        'is_filterable' => 0,
        'is_filterable_in_search' => 0,
        'is_used_for_promo_rules' => 0,
        'is_html_allowed_on_front' => 1,
        'is_visible_on_front' => 0,
        'used_in_product_listing' => 0,
        'used_for_sort_by' => 0,
        'frontend_label' => ['Select Attribute'],
        'backend_type' => 'int',
        'option' => [
            'value' => ['option_0' => ['Select_Option_1'], 'option_1' => ['Select_Option_2']],
            'order' => ['option_0' => 1, 'option_1' => 2],
        ],
    ]
);
$attribute->save();
/* Assign attribute to attribute set */
$installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());

/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);
$eavConfig->clear();
