<?php

namespace nadzif\core\db;

class Migration extends \yii\db\Migration
{
    /**
     * @var string
     */
    protected $tableOptions;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // switch based on driver name
        switch ($this->getDb()->driverName) {
            case 'mysql':
                $this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
                break;
            default:
                $this->tableOptions = null;
        }
    }

    public function setForeignKey(
        $table,
        $columns,
        $refTable,
        $refColumns,
        $delete = 'NO ACTION',
        $update = 'NO ACTION'
    ) {
        $name = $this->formatForeignKeyName($table, $refTable);
        parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    public function formatForeignKeyName($tableNameForeign, $tableNamePrimary)
    {
        return 'FK'.self::originalTableName($tableNameForeign).self::originalTableName($tableNamePrimary);
    }

    public static function originalTableName($table)
    {
        $table = explode('.', $table);
        $table = $table[count($table) - 1];
        $table = trim($table, '{}');
        $table = str_replace('%', '', $table);
        $table = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));

        return $table;
    }

    public function setPrimaryUUID($table, $column)
    {
        $originalTableName = self::originalTableName($table);

        $this->alterColumn($table, $column, $this->string(36)->notNull());
        $this->addPrimaryKey('PK'.$originalTableName.ucwords($column), $table, $column);
    }

    public function addLogColumns($table)
    {
        $this->addColumn($table, 'createdAt', $this->dateTime());
        $this->addColumn($table, 'createdBy', $this->string());
        $this->addColumn($table, 'updatedAt', $this->dateTime());
        $this->addColumn($table, 'updatedBy', $this->string());

        $this->createColumnIndex($table, "createdAt");
        $this->createColumnIndex($table, "updatedAt");
    }

    public function createColumnIndex($table, $column = 'status')
    {
        $originalTableName = self::originalTableName($table);
        $this->createIndex('Index'.$originalTableName.ucwords($column), $table, $column);
    }

    public function createTable($table, $columns, $options = null)
    {
        parent::createTable($table, $columns, $this->tableOptions);
    }

    public function createStatusIndex($table)
    {
        $this->createColumnIndex($table, "status");
    }
}
