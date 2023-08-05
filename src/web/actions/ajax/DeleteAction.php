<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/14/2019
 * Time: 12:52 AM
 */

namespace nadzif\core\web\actions\ajax;


use nadzif\core\helpers\StringHelper;
use nadzif\core\web\components\Action;
use nadzif\core\web\widgets\FloatAlert;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii\helpers\Json;

class DeleteAction extends Action
{
    public $activeRecordClass;
    public $condition                  = true;
    public $attributeCondition         = [];
    public $attributeConditionJunction = 'and';

    public $key = 'id';

    public $successTitle;
    public $successMessage;
    public $failedTitle;
    public $failedMessage;

    public function run()
    {
        $alerts       = [];
        $requestParam = \Yii::$app->request->get($this->key);
        /** @var ActiveRecord $newModel */
        $newModel = new $this->activeRecordClass;

        /** @var ActiveRecord $model */
        $model = $newModel::find()->where([$this->key => $requestParam])->one();

        if ($model) {
            $modelAttributes = $model->attributes;
            $condition       = true;
            foreach ($this->attributeCondition as $attribute => $value) {
                $booleanCondition = $model->$attribute == $value;
                if ($this->attributeConditionJunction == 'and') {
                    $condition &= $booleanCondition;
                } else {
                    $condition |= $booleanCondition;
                }

                if (!$booleanCondition) {
                    $alerts[] = [
                        'type'    => FloatAlert::TYPE_WARNING,
                        'title'   => \Yii::t('app', 'Invalid Attribute Condition'),
                        'message' => \Yii::t('app', '{attributeLabel} must be set to {value} for further action', [
                            'attributeLabel' => $model->getAttributeLabel($attribute),
                            'value'          => $value
                        ])
                    ];
                }
            }

            if ($condition && $this->condition) {
                try {
                    if ($model->delete()) {
                        $alerts[] = [
                            'type'    => FloatAlert::TYPE_SUCCESS,
                            'title'   => StringHelper::replace(
                                $this->successTitle,
                                \Yii::t('app', 'Delete Success'),
                                $modelAttributes
                            ),
                            'message' => StringHelper::replace(
                                $this->successTitle,
                                \Yii::t('app', 'Record deleted successfully.'),
                                $modelAttributes
                            )
                        ];
                    } else {
                        $alerts[] = [
                            'type'    => FloatAlert::TYPE_DANGER,
                            'title'   => StringHelper::replace(
                                $this->successTitle,
                                \Yii::t('app', 'Delete Failed'),
                                $modelAttributes
                            ),
                            'message' => StringHelper::replace(
                                $this->successTitle,
                                \Yii::t('app', 'Failed while deleting record.'),
                                $modelAttributes
                            )
                        ];
                    }
                } catch (IntegrityException $e) {
                    $alerts[] = [
                        'type'    => FloatAlert::TYPE_WARNING,
                        'title'   => StringHelper::replace(
                            $this->successTitle,
                            \Yii::t('app', 'Delete Failed'),
                            $modelAttributes
                        ),
                        'message' => StringHelper::replace(
                            $this->successTitle,
                            \Yii::t('app', 'Data relation exist, could not delete this record'),
                            $modelAttributes
                        )
                    ];
                }
            } else {
                $alerts[] = [
                    'type'    => FloatAlert::TYPE_DANGER,
                    'title'   => StringHelper::replace(
                        $this->failedTitle,
                        \Yii::t('app', 'Failed while deleting record.'),
                        $modelAttributes
                    ),
                    'message' => StringHelper::replace(
                        $this->failedMessage,
                        \Yii::t('app', 'Failed while deleting record.'),
                        $modelAttributes
                    )
                ];
            }
        } else {
            $alerts[] = [
                'type'    => FloatAlert::TYPE_WARNING,
                'title'   => $this->failedTitle ?: \Yii::t('app', 'Data Not Found'),
                'message' => $this->failedMessage ?: \Yii::t('app', 'Cannot find selected item for delete.')
            ];
        }


        return Json::encode([
            'data' => [
                'alert' => $alerts
            ]
        ]);
    }


}