<?php

namespace nadzif\core\filters;

use Carbon\Carbon;
use yii\base\ActionFilter;

/**
 * Class FirstRequestTimeFilter
 * @package common\filters
 *
 * @property string $firstRequestTime
 */
class FirstRequestTimeFilter extends ActionFilter
{
    public  $only     = [
        'list'
    ];
    public  $getParam = 'firstRequestTime';
    private $_firstRequestTime;

    /**
     * @return string
     */
    public function getFirstRequestTime()
    {
        if ($this->_firstRequestTime === null) {
            try {
                $time   = \Yii::$app->request->get($this->getParam, null);
                $carbon = Carbon::createFromFormat('Y-m-d H:i:s', \urldecode($time));
            } catch (\InvalidArgumentException $e) {
                // if it is invalid
                $carbon = Carbon::now();
            }

            $this->_firstRequestTime = $carbon->format('Y-m-d H:i:s');
        }

        // overwrite the query params
        $queryParams                  = \Yii::$app->request->getQueryParams();
        $queryParams[$this->getParam] = $this->_firstRequestTime;

        \Yii::$app->request->setQueryParams($queryParams);

        return $this->_firstRequestTime;
    }
}
