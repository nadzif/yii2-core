<?php

namespace nadzif\core\rest\actions;


use nadzif\core\rest\components\HttpException;
use nadzif\core\rest\components\Response;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class CreateAction extends Action
{
    /** @var string */
    public $scenario;

    /** @var string */
    public $modelClass;

    public $toArrayProperties = [];

    /** @var bool Whether the user can access this action or not */
    public $canAccess = true;

    /** @var int */
    public $apiCodeSuccess = 0;
    public $apiCodeFailed  = 0;

    public $successMessage;
    public $failedMessage;

    /**
     * @throws InvalidConfigException
     * @since 2018-05-04 00:05:20
     */
    public function init()
    {
        if ($this->modelClass === null) {
            throw new InvalidConfigException(get_class($this).'::$modelClass must be set.');
        }
    }

    /**
     * @return Response
     * @throws HttpException
     * @throws InvalidConfigException
     * @since 2018-02-28 13:11:32
     */
    public function run()
    {
        $modelClass = $this->modelClass;

        /** @var ActiveRecord $record */
        $record             = new $modelClass();
        $record->scenario   = $this->scenario;
        $record->attributes = \Yii::$app->request->getBodyParams();

        if ($record->validate()) {
            $record->save();
            $record->refresh();

            $response          = new Response();
            $response->name    = 'Success';
            $response->message = $this->successMessage;
            $response->code    = $this->apiCodeSuccess;
            $response->status  = 201;
            $response->data    = ArrayHelper::toArray($record, $this->toArrayProperties);;

            return $response;
        }

        if (!$this->failedMessage) {
            $this->failedMessage = \Yii::t('app', 'Create Failed');
        }

        throw new HttpException(400, $this->failedMessage, $record->errors, $this->apiCodeFailed);
    }

    /**
     * @return bool
     * @throws NotFoundHttpException
     * @since 2018-05-04 12:39:29
     */
    protected function beforeRun()
    {
        if ($this->canAccess instanceof \Closure) {
            $this->canAccess = \call_user_func($this->canAccess);
        }

        if (!$this->canAccess) {
            throw new NotFoundHttpException(null, $this->apiCodeFailed);
        }

        return true;
    }
}
