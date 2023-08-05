<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/14/2019
 * Time: 3:41 AM
 */

namespace nadzif\core\web\components;


use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class Controller extends \yii\web\Controller
{
    public $allowedRoles = ['@'];

    public function behaviors()
    {
        $behaviors = [];

        if (!isset($this->behaviors['access'])) {
            $behaviors['access'] = [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'roles' => $this->allowedRoles]
                ]
            ];
        }

        $behaviors['verbFilter'] = [
            'class'   => VerbFilter::className(),
            'actions' => $this->verbs(),
        ];

        return $behaviors;
    }

    public function verbs()
    {
        return [];
    }

    public function init()
    {
        parent::init();
        if (\Yii::$app->user->isGuest) {
            $lang = \Yii::$app->session->get('language', 'en');
        } else {
            $user = \Yii::$app->user->identity;
            $lang = ArrayHelper::getValue($user, 'language', 'en');
        }
        \Yii::$app->language = $lang;
    }
}