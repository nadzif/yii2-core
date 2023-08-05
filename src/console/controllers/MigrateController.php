<?php

namespace nadzif\core\console\controllers;

class MigrateController extends \yii\console\controllers\MigrateController
{
    public $generatorTemplateFiles = [
        'create_table'    => '@nadzif/core/console/views/createTableMigration.php',
        'drop_table'      => '@yii/views/dropTableMigration.php',
        'add_column'      => '@yii/views/addColumnMigration.php',
        'drop_column'     => '@yii/views/dropColumnMigration.php',
        'create_junction' => '@yii/views/createTableMigration.php',
    ];

}