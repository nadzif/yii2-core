<?php

/**
 * Creates a call for the method `yii\db\Migration::createTable()`.
 */

/* @var $table string the name table */
/* @var $fields array the fields */
/* @var $foreignKeys array the foreign keys */

$originalTableName = explode('.', $table);
$originalTableName = $originalTableName[count($originalTableName) - 1];
$originalTableName = trim($originalTableName, '{}');
$originalTableName = str_replace('%', '', $originalTableName);
$originalTableName = str_replace(' ', '', ucwords(str_replace('_', ' ', $originalTableName)));
?>
    $tableName = <?= $originalTableName ?>::tableName();

    $this->createTable($tableName, [
<?php
foreach ($fields as $field):
    if (empty($field['decorators'])): ?>
        '<?= $field['property'] ?>',
    <?php
    else: ?>
        <?= "'{$field['property']}' => \$this->{$field['decorators']}" ?>,
    <?php
    endif;
endforeach; ?>
    ]);

    $this->setPrimaryUUID($tableName, 'id');
    $this->addLogColumns($tableName);
    $this->createStatusIndex($tableName);

<?= $this->render('@yii/views/_addForeignKeys', [
    'table'       => $table,
    'foreignKeys' => $foreignKeys,
]);
