<?php

namespace nadzif\core\rest\components;

use nadzif\core\filters\FirstRequestTimeFilter;
use nadzif\core\filters\SystemAppFilter;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;

class Controller extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    /**
     * Override behaviors from rest controller
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'rateLimiter'            => [
                'class'        => RateLimiter::className(),
                'errorMessage' => \Yii::t('app', 'Too many request'),
            ],
            'contentNegotiator'      => [
                'class'   => ContentNegotiator::class,
                'formats' => [
                    'application/json' => \yii\web\Response::FORMAT_JSON
                ]
            ],
            'verbFilter'             => [
                'class'   => VerbFilter::class,
                'actions' => $this->verbs(),
            ],
            'systemAppFilter'        => [
                'class'              => SystemAppFilter::class,
                'appKeyHeaderKey'    => 'X-App-key',
                'appSecretHeaderKey' => 'X-App-secret'
            ],
            'authenticator'          => [
                'class'       => CompositeAuth::className(),
                'authMethods' => [HttpBearerAuth::className()]
            ],
            'firstRequestTimeFilter' => [
                'class' => FirstRequestTimeFilter::class
            ],

        ];
    }

    /**
     * Declares the allowed HTTP verbs.
     * Please refer to [[VerbFilter::actions]] on how to declare the allowed verbs.
     *
     * @return array the allowed HTTP verbs.
     */
    protected function verbs()
    {
        return [];
    }

    /**
     * @param $action
     * @param $result
     *
     * @return mixed
     * @throws HttpException
     * @since 2018-02-02 09:39:14
     *
     */
    public function afterAction($action, $result)
    {
        if (!$result instanceof Response) {
            throw new HttpException(
                500,
                \Yii::t('app', 'Response should be instance of nadzif\core\rest\components\Response')
            );
        }

        if (($message = $result->validate()) !== true) {
            throw new HttpException(500, $message);
        }

        return parent::afterAction($action, $result);
    }
}
