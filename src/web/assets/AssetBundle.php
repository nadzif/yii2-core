<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/16/2019
 * Time: 2:21 PM
 */

namespace nadzif\core\web\assets;


class AssetBundle extends \yii\web\AssetBundle
{
    public $sourcePath = "@nadzif/core/web/assets/files";
    public $css        = [
        'css/float-alert.css',
    ];
    public $js         = [
        'js/float-alert.js',
        'js/ajax-form.js',
    ];
    public $depends    = [
        'rmrevin\yii\fontawesome\AssetBundle',
        'rmrevin\yii\ionicon\AssetBundle',
        'yii\web\YiiAsset',
    ];
}