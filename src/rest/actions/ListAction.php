<?php

namespace nadzif\core\rest\actions;

use nadzif\core\rest\components\Response;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ListAction extends QueryAction
{

    public $filters        = [];
    public $searchExploder = '_';

    /**
     * @return Response
     * @since 2018-05-04 00:41:53
     */
    public function run()
    {
        $modelClass = new $this->query->modelClass;
        $tableName  = $modelClass::tableName();

        $this->query->andWhere(['<=', $tableName.'.createdAt', $this->controller->firstRequestTime]);

        $queryParams = \Yii::$app->request->queryParams;

        $query = $this->query;
        // setup data provider
        $dataProvider = new ActiveDataProvider();

        foreach ($this->filters as $attribute => $filter) {
            if (is_int($attribute)) {
                $attribute = $filter;
                $filter    = ["like", $attribute];
            }

            $searchAttribute = ArrayHelper::getValue($queryParams, $attribute);

            if (!$searchAttribute) {
                continue;
            }

            if (ArrayHelper::isIn(ArrayHelper::getValue($filter, 0), ['between'])) {
                $exploder = explode($this->searchExploder, $searchAttribute);

                foreach ($exploder as $index => $searchAttribute) {
                    array_push($filter, $index == 0 ? $searchAttribute.' 00:00:00' : $searchAttribute.' 23:59:59');
                }
            } else {
                array_push($filter, $searchAttribute);
            }

            $query->andFilterWhere($filter);
        }

        $dataProvider->query = $query;

        $getAll = (\Yii::$app->request->get('page') == 'all');

        if ($getAll) {
            $dataProvider->setPagination(false);
        }

        // get the result and pagination
        $result     = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        $meta = [
            'record' => [
                'current' => $dataProvider->getCount(),
                'total'   => $dataProvider->getTotalCount()
            ],
        ];

        if ($pagination instanceof Pagination) {
            $meta['page']  = [
                'current' => $pagination->getPage() + 1,
                'total'   => $pagination->getPageCount()
            ];
            $meta['links'] = $pagination->getLinks();
        }


        $response          = new Response();
        $response->name    = 'Success';
        $response->status  = 200;
        $response->message = $this->successMessage;
        $response->code    = 0;
        $response->data    = ArrayHelper::toArray($result, $this->toArrayProperties);
        $response->meta    = $meta;

        return $response;
    }
}