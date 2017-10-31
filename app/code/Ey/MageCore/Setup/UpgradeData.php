<?php

namespace Ey\MageCore\Setup;

use \Magento\Eav\Setup\EavSetupFactory;
use \Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'short_name',
                [
                    'label'      => 'Short Name',
                    'name'      => 'Short Name',
                    'group'     => 'General Information',
                    'required'  => false,
                    'type' => 'text',
                    'input' => 'text',
                    'backend' => '',
                    'visible' => true,
                    'visible_on_front' => true
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.3') < 0) {

        }

        if(version_compare($context->getVersion(), '0.0.4') < 0){
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            /**
             * Add attributes to the eav/attribute
             */

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'upc',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'UPC',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );

            if(version_compare($context->getVersion(), '0.0.5') < 0){
                /** @var EavSetup $eavSetup */
                $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY, 'sku', 'label', '#'
                );
            }
        }
    }
}