<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/14/2019
 * Time: 1:52 AM
 */

namespace nadzif\core\web\widgets;


use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

class Select2 extends \kartik\select2\Select2
{
    public $theme         = self::THEME_BOOTSTRAP;
    public $pluginLoading = false;
    public $pluginOptions = [
        'closeOnSelect' => true,
        'allowClear'    => true
    ];
    public $ajaxUrl;

    public function __construct($config = array())
    {
        if ($theme = ArrayHelper::getValue(\Yii::$app->params, 'widget.select2.theme')) {
            $this->theme = $theme;
        }

        $config['toggleAllSettings'] = [
            'selectLabel'   => Html::tag('i', false, ['class' => 'fa fa-check']).\Yii::t('app', 'Select all'),
            'unselectLabel' => Html::tag('i', false, ['class' => 'fa fa-close']).\Yii::t('app', 'Deselect all'),
            'options'       => ['class' => 's2-togall-button']
        ];

        parent::__construct($config);

        $multiple = isset($this->options['multiple']) && $this->options['multiple'];


        if (!$multiple) {
            if (!$this->initValueText) {
                $this->initValueText = \Yii::t('app', 'Choose ...');
            }

            if (!isset($this->options['placeholder'])) {
                $this->options['placeholder'] = \Yii::t('app', 'Choose ...');
            }
        }
    }

    public function init()
    {
        parent::init();

        if ($this->ajaxUrl) {
            $this->pluginOptions = ArrayHelper::merge($this->pluginOptions, [
                'allowClear'         => true,
                'minimumInputLength' => 3,
                'language'           => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                ],
                'ajax'               => [
                    'url'      => Url::to($this->ajaxUrl),
                    'dataType' => 'json',
                    'data'     => new JsExpression('function(params) { return {search:params.term}; }')
                ],
                'escapeMarkup'       => new JsExpression('function (markup) { return markup; }'),
                'templateResult'     => new JsExpression('function (data) { return data.text; }'),
                'templateSelection'  => new JsExpression('function (data) { return data.text; }'),
            ]);
        }
    }
}