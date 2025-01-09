<?php
namespace SENSEPAYMENT\Sensebank\Setup;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        /**
         * Creating table sensebank_gateway_order
         */
        $tableName = 'sensebank_gateway_order';
        if (!$installer->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable($tableName))
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity'  => true,
                        'unsigned'  => true,
                        'nullable'  => false,
                        'primary'   => true,
                    ],
                    'Entity Id'
                )
                ->addColumn(
                    'gateway_order_reference',
                    Table::TYPE_TEXT,
                    64,
                    [
                        'nullable' => false,
                        'default'   => '',
                    ],
                    'Sensebank Order Reference'
                )
                ->addColumn(
                    'increment_id',
                    Table::TYPE_TEXT,
                    64,
                    [],
                    'Increment Id'
                )
                ->addColumn(
                    'order_amount',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Order amount'
                )
                ->addColumn(
                    'order_amount_deposited',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Order amount deposited'
                )
                ->addColumn(
                    'order_amount_refunded',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Order amount refunded'
                )
                ->addColumn(
                    'status_deposited',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'default'  => 0,
                    ],
                    'Status deposited'
                )
                ->addColumn(
                    'status_reversed',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'default'  => 0,
                    ],
                    'Status reversed'
                )
                ->addColumn(
                    'status_refunded',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'default'  => 0,
                    ],
                    'Status refunded'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'default'  => 0,
                    ],
                    'Status'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'default'  => 'CURRENT_TIMESTAMP',
                    ],
                    'Date Created'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Date Updated'
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['increment_id']),
                    ['increment_id']
                )
                ->setComment('Sensebank Orders Table');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
