<?php

namespace nadzif\core\web\widgets;


class Modal extends \yii\bootstrap4\Modal
{
    const SIZE_LARGE   = "modal-lg w-100";
    const SIZE_SMALL   = "modal-sm";
    const SIZE_DEFAULT = "";

    public $options       = ['tabindex' => false];
    public $headerOptions = ['class' => 'pd-y-20 pd-x-25'];
    public $toggleButton  = [
        'label' => '<i class="ion-plus"></i>',
        'class' => 'btn btn-success'
    ];

    public $size = self::SIZE_LARGE;
}