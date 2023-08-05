<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 12/4/2019
 * Time: 10:32 PM
 */

namespace nadzif\core\parsers;

use yii\web\BadRequestHttpException;
use yii\web\RequestParserInterface;

class XmlParser implements RequestParserInterface
{
    /**
     * @var bool whether to return objects in terms of associative arrays.
     */
    public $asArray = true;
    /**
     * @var bool whether to throw a [[BadRequestHttpException]] if the body is invalid json
     */
    public $throwException = true;


    public function parse($rawBody, $contentType)
    {
        try {
            $parameters = (array)(simplexml_load_string($rawBody));

            return $parameters === null ? [] : $parameters;
        } catch (\InvalidArgumentException $e) {
            if ($this->throwException) {
                throw new BadRequestHttpException('Invalid XML data in request body: '.$e->getMessage());
            }

            return [];
        }
    }
}
