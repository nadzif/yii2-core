<?php

namespace nadzif\core\rest\components;

/**
 * Class HttpException
 *
 * Special HttpException for API. There is data property.
 *
 * @package api\components
 * @author  Haqqi <me@haqqi.net>
 */
class HttpException extends \yii\web\HttpException
{
    private $_data;

    public function __construct($status, $message = null, $data = [], $code = 0, \Exception $previous = null)
    {
        parent::__construct($status, $message, $code, $previous);

        $this->_data = $data;
    }

    public function getData()
    {
        return $this->_data;
    }
}
