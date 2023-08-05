<?php

namespace nadzif\core\rest\actions;

use nadzif\core\rest\components\Response;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class ViewAction extends SingleRecordAction
{
    /**
     * @param  string  $id  actually it is hashId
     *
     * @return Response
     * @throws NotFoundHttpException
     * @since 2018-02-26 13:16:39
     *
     */
    public function run($id)
    {
        $record = $this->findRecord($id);

        $response          = new Response();
        $response->name    = 'Success';
        $response->message = $this->successMessage;
        $response->code    = $this->apiCodeSuccess;
        $response->status  = 200;
        $response->data    = ArrayHelper::toArray($record, $this->toArrayProperties);

        return $response;
    }
}
