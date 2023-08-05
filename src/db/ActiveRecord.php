<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/13/2019
 * Time: 11:57 PM
 */

namespace nadzif\core\db;


use Ramsey\Uuid\Uuid;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class ActiveRecord extends \yii\db\ActiveRecord
{
    public static function tableColumns()
    {
        $columnNames = self::getTableSchema()->getColumnNames();

        $columnWithTableName = [];
        foreach ($columnNames as $columnName) {
            $columnWithTableName[] = self::tableName().'components'.$columnName;
        }

        return $columnWithTableName;
    }

    public static function tableDbName($tableName)
    {
        $curdb  = explode('=', self::getDb()->dsn);
        $dbName = '{{'.$curdb[2].'}}';
        $prefix = self::getDb()->driverName == 'sqlsrv' ? $dbName.'.{{dbo}}.' : $dbName.'.';

        return $prefix.$tableName;
    }

    /**
     * Default behaviors for all models in this project
     *
     * @return array
     */
    public function behaviors()
    {
        $behaviors = [];

        $behaviors['timestampBehavior'] = [
            'class'              => TimestampBehavior::class,
            'value'              => new Expression("'".date('Y-m-d H:i:s')."'"),
            'createdAtAttribute' => 'createdAt',
            'updatedAtAttribute' => 'updatedAt'
        ];

        $behaviors['uuid'] = [
            'class'      => AttributeBehavior::class,
            'value'      => function ($event) {
                return Uuid::uuid4()->toString();
            },
            'attributes' => [ActiveRecord::EVENT_BEFORE_INSERT => ['id']],
        ];

        return $behaviors;
    }
}