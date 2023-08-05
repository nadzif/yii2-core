<?php

namespace nadzif\core\filters;

use nadzif\core\rest\components\HttpException;
use yii\base\ActionFilter;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class SystemAppFilter extends ActionFilter
{
    public $appKeyHeaderKey;
    public $appSecretHeaderKey;

    public function init()
    {
        parent::init();
    }

    /**
     * @param  \yii\base\Action  $action
     *
     * @return bool
     * @throws HttpException
     * @throws InvalidConfigException
     * @since 2018-02-13 14:07:03
     *
     */
    public function beforeAction($action)
    {
        if (empty($this->appKeyHeaderKey) || empty($this->appSecretHeaderKey)) {
            throw new InvalidConfigException(
                \Yii::t(
                    'app',
                    'Please setup $appKeyHeaderKey and $appSecretHeaderKey when using this class as filter.'
                )
            );
        }

        $appKey         = \Yii::$app->request->headers->get($this->appKeyHeaderKey);
        $appSecret      = \Yii::$app->request->headers->get($this->appSecretHeaderKey);
        $systemAppClass = ArrayHelper::getValue(\Yii::$app->params, 'systemApp.class');


        if (empty($appKey) || empty($appSecret)) {
            throw new HttpException(400, \Yii::t('app', 'Please provide the security of app key and app secret'));
        }

        if (!$systemAppClass) {
            throw new InvalidConfigException(\Yii::t('app', 'Please config params for systemApp.class'));
        }

        $systemApp = (new $systemAppClass)->find()
            ->where([
                'appKey'    => $appKey,
                'appSecret' => $appSecret
            ])
            ->one();

        if (empty($systemApp)) {
            throw new HttpException(
                400,
                \Yii::t('app', 'Either one or both of your app key and app secret is invalid.')
            );
        }

        if ($systemApp->status !== 'active') {
            throw new HttpException(
                400,
                \Yii::t('app', 'Your app status is '.$systemApp->status.'. Please contact help desk.')
            );
        }

        if (!empty($systemApp->ip) && $systemApp->ip != \Yii::$app->request->getUserIP()) {
            throw new HttpException(400, \Yii::t('app', 'Your app can only be accessed from specific IP.'));
        }

        // set it in the container
        \Yii::$app->set('systemApp', $systemApp);

        return true;
    }
}
