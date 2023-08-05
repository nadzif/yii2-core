<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 1/23/2020
 * Time: 12:45 AM
 */

namespace nadzif\core\web\widgets;


use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class DetailView extends \yii\widgets\DetailView
{
    public $title;
    public $options = ['class' => 'table table-hover table-bordered detail-view'];

    public function run()
    {
        $rows = [];

        if ($this->title) {
            $rows[] = Html::tag('tr', Html::tag('td', Html::tag('strong', strtoupper($this->title)), ['colspan' => 2]));
        }

        $i = 0;

        foreach ($this->attributes as $attribute) {
            $rows[] = $this->renderAttribute($attribute, $i++);
        }

        $options = $this->options;
        $tag     = ArrayHelper::remove($options, 'tag', 'table');
        echo Html::tag($tag, implode("\n", $rows), $options);
    }
}