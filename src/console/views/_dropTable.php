<?php

/**
 * Creates a call for the method `yii\db\Migration::dropTable()`.
 */

/* @var $table string the name table */
/* @var $foreignKeys array the foreign keys */

$originalTableName = explode('.', $table);
$originalTableName = $originalTableName[count($originalTableName) - 1];
$originalTableName = trim($originalTableName, '{}');
$originalTableName = str_replace('%', '', $originalTableName);
$originalTableName = str_replace(' ', '', ucwords(str_replace('_', ' ', $originalTableName)));


echo $this->render('@yii/views/_dropForeignKeys', [
    'table'       => $table,
    'foreignKeys' => $foreignKeys,
]) ?>
$this->dropTable(<?= $originalTableName ?>::tableName());
