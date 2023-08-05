<?php

namespace nadzif\core\filters;

use nadzif\core\parsers\XmlParser;
use nadzif\core\rest\components\HttpException;
use yii\base\ActionFilter;
use yii\web\JsonParser;
use yii\web\MultipartFormDataParser;

/**
 * Class ContentTypeFilter
 *
 * @package api\filters
 */
class ContentTypeFilter extends ActionFilter
{
    const TYPE_APPLICATION_JSON             = 'application/json';
    const TYPE_APPLICATION_XML              = 'application/xml';
    const TYPE_APPLICATION_FORM_URL_ENCODED = 'application/x-www-form-urlencoded';
    const TYPE_APPLICATION_MULTIPART        = 'multipart/form-data';

    public $parsers = [
        self::TYPE_APPLICATION_JSON             => JsonParser::class,
        self::TYPE_APPLICATION_XML              => XmlParser::class,
        self::TYPE_APPLICATION_MULTIPART        => MultipartFormDataParser::class,
        self::TYPE_APPLICATION_FORM_URL_ENCODED => MultipartFormDataParser::class
    ];

    public $contentType;

    /**
     * @param  \yii\base\Action  $action
     *
     * @return bool
     * @throws HttpException
     * @since 2018-02-23 14:54:21
     *
     */
    public function beforeAction($action)
    {
        $contentType = \Yii::$app->request->contentType;
        if (\strpos($contentType, ';')) {
            $contentType = \strstr(\Yii::$app->request->contentType, ';', true);
        }

        if ($contentType != $this->contentType) {
            throw new HttpException(
                400, \Yii::t('app', 'Can only consume: {content}. Your request was {content}', [
                'content' => $this->contentType
            ])
            );
        }

        if (\array_key_exists($this->contentType, $this->parsers)) {
            \Yii::$app->request->parsers = $this->parsers;
        }

        return parent::beforeAction($action);
    }
}
