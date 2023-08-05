<?php

namespace nadzif\core\rest\components;

use yii\base\Model;

abstract class BaseForm extends Model
{
    /**
     * @return mixed
     *
     * What to do when submit. This will be called in the FormAction
     * @since 2018-05-06 23:08:20
     */
    abstract public function submit();

    /**
     * @return mixed
     *
     * How to format the data
     * @since 2018-05-06 23:08:33
     */
    abstract public function response();

    /**
     * @return array
     *
     * Format meta for this form
     * @since 2018-05-10 10:44:29
     */
    public function meta()
    {
        return [];
    }
}