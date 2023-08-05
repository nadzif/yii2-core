<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 8/22/2018
 * Time: 6:42 PM
 */

namespace nadzif\core\db;


use yii\base\BaseObject;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

class Fetcher extends BaseObject
{

    public  $modelClass;
    public  $modelAttributes     = [];
    public  $fromLine            = 1;
    public  $csvPath;
    public  $attributeIndex;
    public  $ignoreColumnIndexes = [];
    public  $continueOnError     = false;
    public  $detachBehaviors     = [];
    private $_rows               = [];

    public function migrate($db = 'db')
    {
        $this->setRows();

        /** @var Connection $selectedDb */
        $selectedDb = \Yii::$app->$db;

        $transaction = $selectedDb->beginTransaction();
        $commit      = true;

        $totalSuccess = 0;
        $totalFailed  = 0;
        $lineError    = [];

        try {
            foreach ($this->_rows as $line => $row) {
                /** @var ActiveRecord $model */
                $model = new $this->modelClass($this->modelAttributes);
                $model->setAttributes($this->modelAttributes);

                foreach ($this->detachBehaviors as $detachBehavior) {
                    $model->detachBehavior($detachBehavior);
                }

                if (count($row) == count($this->attributeIndex)) {
                    foreach ($this->attributeIndex as $index => $attribute) {
                        if (ArrayHelper::isIn($index, $this->ignoreColumnIndexes)) {
                            continue;
                        }
                        $model->setAttribute($attribute, $row[$index]);
                    }

                    try {
                        $model->save();
                        if ($model->save(false)) {
                            $totalSuccess += 1;
                        }
                    } catch (Exception $e) {
                        if (!$this->continueOnError) {
                            $commit = false;
                            break;
                        }
                        $lineError[] = $this->fromLine + $line;
                        $totalFailed += 1;
                    } catch (\PDOException $e) {
                        if (!$this->continueOnError) {
                            $commit = false;
                            break;
                        }
                        $lineError[] = $this->fromLine + $line;
                        $totalFailed += 1;
                    }
                    echo 'inserting into table '.$model->tableName().' ('.implode(', ', $row).')';
                    echo PHP_EOL;
                }
            }

            if ($commit) {
                $transaction->commit();
                echo 'transaction committed!!';
                echo PHP_EOL;
            } else {
                $transaction->rollBack();
                echo 'transaction rolled back!!';
                echo PHP_EOL;
            }
        } catch (Exception $ex) {
            $transaction->rollBack();
            echo 'transaction rolled back!!';
            echo PHP_EOL;
        }

        echo $totalSuccess.' total success';
        echo PHP_EOL;
        echo $totalFailed.' total failed';
        echo PHP_EOL;

        if ($lineError) {
            echo 'line '.implode(', ', $lineError).' failed.';
            echo PHP_EOL;
        }
    }

    private function setRows()
    {
        $fileHandler = fopen($this->csvPath, 'r');

        $iC = 1;
        while (!feof($fileHandler)) {
            if ($iC >= $this->fromLine) {
                $this->_rows[] = fgetcsv($fileHandler, 1024);
            }
            $iC++;
        }

        fclose($fileHandler);
    }


}
