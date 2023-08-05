<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 12/3/2019
 * Time: 7:25 PM
 */

namespace nadzif\core\web\actions\json\dependant;


use nadzif\core\web\components\Action;
use yii\db\ActiveRecord;

class ListAction extends Action
{
    public $limit = false;

    public $activeRecordClass;
    public $idAttribute;
    public $textAttribute;
    public $orderBy = [];

    public $parentAttribute;

    public $condition = [];

    public function init()
    {
        parent::init();
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    public function run($selected = '')
    {
        $out = ['output' => '', 'selected' => ''];

        if (isset($_POST['depdrop_parents'])) {
            /** @var ActiveRecord $activeRecord */
            $activeRecord  = new $this->activeRecordClass;
            $textAttribute = $this->textAttribute;

            $parents = $_POST['depdrop_parents'];
            if ($parents != null && $parents[0]) {
                $parentId = $parents[0];
                $query    = $activeRecord::find()
                    ->select([$this->idAttribute, $textAttribute.' AS name'])
                    ->where([$this->parentAttribute => $parentId])
                    ->asArray();

                if ($this->condition) {
                    $query->andWhere($this->condition);
                }

                if ($this->orderBy) {
                    $query->addOrderBy($this->orderBy);
                }

                if ($this->limit) {
                    $query->limit($this->limit);
                }

                return ['output' => $query->all(), 'selected' => $selected];
            }
        }

        return $out;
    }
}