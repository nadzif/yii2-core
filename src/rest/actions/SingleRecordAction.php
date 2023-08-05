<?php

namespace nadzif\core\rest\actions;

use yii\base\InvalidArgumentException;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class SingleRecordAction extends QueryAction
{

    /**
     * @param $id
     *
     * @return array|null|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     * @since 2018-05-04 12:22:44
     *
     */
    protected function findRecord($id)
    {
        try {
            /** @var ActiveRecord $modelClass */
            $modelClass = new $this->query->modelClass;
            $record     = $this->query->andWhere([$modelClass::tableName().'.id' => $id])->one();

            if (empty($record)) {
                throw new InvalidArgumentException();
            }
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException(null, $this->apiCodeFailed);
        }

        return $record;
    }
}
