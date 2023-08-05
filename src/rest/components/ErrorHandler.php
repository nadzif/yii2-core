<?php

namespace nadzif\core\rest\components;

/**
 * Class ErrorHandler
 * @package api\components
 *
 * Advanced error handler that include 'data' as the return value.
 */
class ErrorHandler extends \yii\web\ErrorHandler
{
    protected function convertExceptionToArray($exception)
    {
        $array = parent::convertExceptionToArray($exception);

        if ($exception instanceof HttpException) {
            $array['data'] = $exception->getData();
        }

        return $array;
    }
}

