<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 12/8/2019
 * Time: 6:39 AM
 */

namespace nadzif\core\web\actions\ajax;

use nadzif\core\web\components\Action;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class PutAction extends Action
{
    public $modelClass;
    public $attributes;

    public $successMessage;
    public $failedMessage;

    public $allow = true;

    public function run()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $formClass = $this->modelClass;

        $queryParams = \Yii::$app->request->queryParams;
        $postRequest = \Yii::$app->request->post();

        /** @var ActiveRecord $activeRecord */
        $activeRecord = new $formClass();
        $model        = $activeRecord::find()
            ->where($queryParams)
            ->one();

        if (!$model) {
            $model = (new $formClass());
            foreach ($queryParams as $modelAttribute => $attributeValue) {
                $model->{$modelAttribute} = $attributeValue;
            }
        }


        foreach ($this->attributes as $modelAttribute => $requestName) {
            $issetAttribute = ArrayHelper::getValue($postRequest, $requestName, false);
            if ($issetAttribute) {
                $model->{$modelAttribute} = $issetAttribute;
            }
        }

        if ($model->validate() && $model->save()) {
            return true;
        } else {
            return ['output' => '', 'message' => $this->failedMessage];
        }
    }
}