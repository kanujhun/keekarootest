<?php

namespace  Ey\ProductRegister\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
        $installer = $setup;
        $installer->startSetup();

		$table = $installer->getConnection()->newTable($installer->getTable('ey_productregister'))
			->addColumn(
				'autoId',
				Table::TYPE_INTEGER,
				null,
				['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true],
				'Auto ID'
			)->addColumn(
				'name_prefix',
				Table::TYPE_TEXT,
				null,
				[],
				'Name Prefix'
			)->addColumn(
				'name_first',
				Table::TYPE_TEXT,
				null,
				[],
				'Name First'
			)->addColumn(
				'name_last',
				Table::TYPE_TEXT,
				null,
				[],
				'Name Last'
			)->addColumn(
				'address_1',
				Table::TYPE_TEXT,
				null,
				[],
				'Address Line 1'
			)->addColumn(
				'address_2',
				Table::TYPE_TEXT,
				null,
				[],
				'Address Line 2'
			)->addColumn(
				'city',
				Table::TYPE_TEXT,
				null,
				[],
				'City'
			)->addColumn(
				'state',
				Table::TYPE_TEXT,
				null,
				[],
				'State'
			)->addColumn(
				'zip',
				Table::TYPE_TEXT,
				null,
				[],
				'Zip Code'
			)->addColumn(
				'phone_number',
				Table::TYPE_TEXT,
				null,
				[],
				'Phone Number'
			)->addColumn(
				'email_address',
				Table::TYPE_TEXT,
				null,
				[],
				'Email Address'
			)->addColumn(
				'product_purchased',
				Table::TYPE_TEXT,
				null,
				[],
				'Name of Product Purchased'
			)->addColumn(
				'model_number',
				Table::TYPE_TEXT,
				null,
				[],
				'Model # of Product Purchased'
			)->addColumn(
				'date_of_manufacture',
				Table::TYPE_TEXT,
				null,
				[],
				'Date of Manufacture (located on product)'
			)->addColumn(
				'where_purchased',
				Table::TYPE_TEXT,
				null,
				[],
				'Purchaed At'
			)->addColumn(
				'registration_date',
				Table::TYPE_TEXT,
				null,
				[],
				'Registration Date'
			)->addColumn(
				'product_sku',
				Table::TYPE_TEXT,
				null,
				[],
				'Product SKU'
			);
		
		$installer->getConnection()->createTable($table);       

		$installer->endSetup();
	}
}