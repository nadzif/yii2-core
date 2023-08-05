<?php
/**
 * Created by PhpStorm.
 * User: haruk
 * Date: 5/27/2018
 * Time: 10:03 PM
 */

namespace nadzif\core\web\widgets;


use yii\base\Widget;
use yii\bootstrap4\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * echo FloatAlert::widget([
 *     'messages' => [
 *         [
 *             'title' => 'abc',
 *             'options' => [
 *                 'message' => 'content',
 *                 'class' => [FloatAlert::ALERT_SUCCESS, FloatAlert::COLOR_SOLID]
 *             ]
 *         ]
 *     ]
 * ]);
 */
class FloatAlert extends Widget
{

    const TYPE_SUCCESS = 'success';
    const TYPE_DANGER  = 'danger';
    const TYPE_INFO    = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_SOLID   = 'solid';

    const ALERT_SUCCESS = 'alert-success';
    const ALERT_DANGER  = 'alert-danger';
    const ALERT_INFO    = 'alert-info';
    const ALERT_WARNING = 'alert-warning';
    const COLOR_SOLID   = 'alert-solid';

    public $time             = 3000;
    public $messages         = [];
    public $containerOptions = [];

    public function init()
    {
        parent::init();

        $this->containerOptions['data'] = [
            'time' => $this->time,
        ];
    }

    public function run()
    {
        $container_options = $this->containerOptions;
        Html::addCssClass($container_options, 'alert-box box-side');


        $this->render_template();
        echo Html::beginTag('div', $container_options);
        foreach ($this->messages as $message) {
            $this->render_layout($message);
        }
        echo Html::endTag('div'); // alert-box box-side
        // echo Html::tag('div', null, ['class' => 'alert-backdrop']);
    }

    public function render_template()
    {
        echo Html::beginTag('template', ['class' => 'alert-template']);
        $this->render_layout([
            'title'   => '_TITLE_',
            'message' => '_MESSAGE_',
            'options' => [
                'class' => 'alert-solid'
            ],
            'icon'    => '_ICON_'
        ]);
        echo Html::endTag('template');
    }

    public function render_layout($options = [])
    {
        $title   = ArrayHelper::remove($options, 'title');
        $message = ArrayHelper::remove($options, 'message');
        $icon    = ArrayHelper::remove($options, 'icon');

        Html::addCssClass($options['options'], 'shadow');

        Alert::begin($options);
        echo Html::beginTag('div', ['class' => 'd-sm-flex align-items-center justify-content-start alert-content']);
        if (!is_null($icon)) {
            echo Html::beginTag('div', ['class' => 'alert-icon']);
            echo $icon;
            echo Html::endTag('div');
        }
        echo Html::beginTag('div', ['class' => 'mg-sm-l-15 mg-t-15 mg-sm-t-0']);
        if ($title) {
            echo Html::tag('h5', $title, ['class' => 'mg-b-2 pd-t-2 text-white']);
        }
        echo Html::tag('p', $message, ['class' => 'mg-b-0 tx-xs op-8']);
        echo Html::endTag('div');
        echo Html::endTag('div');
        echo Html::tag('div', '', ['class' => 'loading']);
        Alert::end();
    }
}
