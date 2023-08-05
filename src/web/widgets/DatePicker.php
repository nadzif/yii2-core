<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/14/2019
 * Time: 1:58 AM
 */

namespace nadzif\core\web\widgets;


class DatePicker extends \kartik\date\DatePicker
{
    public $pluginOptions = [
        'autoclose'      => true,
        'format'         => 'yyyy-mm-dd',
        'todayHighlight' => true,
    ];
}