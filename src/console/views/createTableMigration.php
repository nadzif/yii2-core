<?php
/**
 *
 * The following variables are available in this view:
 */

/* @var $className string the new migration class name without namespace */
/* @var $namespace string the new migration class namespace */
/* @var $table string the name table */
/* @var $tableComment string the comment table */
/* @var $fields array the fields */
/* @var $foreignKeys array the foreign keys */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}

$originalTableName = explode('.', $table);
$originalTableName = $originalTableName[count($originalTableName) - 1];
$originalTableName = trim($originalTableName, '{}');
$originalTableName = str_replace('%', '', $originalTableName);
$originalTableName = str_replace(' ', '', ucwords(str_replace('_', ' ', $originalTableName)));

$fields[] = [
    "property"   => "status",
    "decorators" => "string(50)->notNull()->defaultValue($originalTableName::STATUS_ACTIVE)"
]
?>

use nadzif\core\db\Migration;
use <?= $originalTableName ?>;

/**
* Handles the creation of table `<?= $table ?>`.
<?= $this->render('@yii/views/_foreignTables', [
    'foreignKeys' => $foreignKeys,
]) ?>
*/
class <?= $className ?> extends Migration
{
/**
* {@inheritdoc}
*/
public function safeUp()
{
<?= $this->render('_createTable', [
    'table'       => $table,
    'fields'      => $fields,
    'foreignKeys' => $foreignKeys,
])
?>
<?php
if (!empty($tableComment)) {
    echo $this->render('_addComments', [
        'table'        => $table,
        'tableComment' => $tableComment,
    ]);
}
?>
}

/**
* {@inheritdoc}
*/
public function safeDown()
{
<?= $this->render('_dropTable', [
    'table'       => $table,
    'foreignKeys' => $foreignKeys,
])
?>
}
}
