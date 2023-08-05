<?php

/**
 * @var \yii\web\View $this
 * @var \nadzif\core\web\components\FormModel $formModel
 * @var array $pageOptions
 * @var string $scenario
 * @var array|string $actionUrl
 * @var array $modalConfig
 * @var array $activeFormConfig
 */

use nadzif\core\web\components\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

$time = time();

$scenario  = $formModel->getScenario();
$modelName = StringHelper::basename(get_class($formModel));

$this->title                 = ArrayHelper::getValue($pageOptions, 'title');
$this->params['breadcrumbs'] = ArrayHelper::getValue($pageOptions, 'breadcrumbs', []);

$_activeFormConfig = [
    'id'     => $modelName.$scenario.'-form-'.$time,
    'action' => $actionUrl
];

echo Html::beginTag('div', ['class' => 'row']);
echo Html::beginTag('div', ['class' => 'col-md-12']);

$form      = ActiveForm::begin(ArrayHelper::merge($_activeFormConfig, $activeFormConfig));
$formRules = $formModel->formRules();

foreach ($formModel->scenarios()[$scenario] as $attributeName) {
    $attributeOptions = ArrayHelper::getValue($formRules, $attributeName, []);

    $inputType    = ArrayHelper::getValue($attributeOptions, 'inputType', 'text');
    $inputOptions = ArrayHelper::getValue($attributeOptions, 'inputOptions', []);
    $inputLabel   = ArrayHelper::getValue($attributeOptions, 'inputLabel');
    $fieldOptions = ArrayHelper::getValue($attributeOptions, 'fieldOptions', []);

    $inputId = $formModel->scenario.'-'.Html::getInputId($formModel, $attributeName);

    if ($inputType == 'content') {
        $contentOptions = ['id' => $inputId];
        echo Html::tag(
            ArrayHelper::getValue($inputOptions, 'tag', 'div'),
            ArrayHelper::getValue($inputOptions, 'content', false),
            ArrayHelper::merge(ArrayHelper::getValue($inputOptions, 'options', []), $contentOptions)
        );
        continue;
    }

    $formField = $form->field($formModel, $attributeName, $fieldOptions);

    switch ($inputType) {
        case 'text':
            $inputOptions['id'] = $inputId;
            $formField->textInput($inputOptions);
            break;
        case 'checkbox':
            $inputOptions['id'] = $inputId;
            $formField->checkbox($inputOptions);
            break;
        case 'textarea':
            $inputOptions['id'] = $inputId;
            $formField->textarea($inputOptions);
            break;
        case 'radioList':
            $inputOptions['id'] = $inputId;
            $formField->radioList($inputOptions);
            break;
        case 'password':
            $inputOptions['id'] = $inputId;
            $formField->passwordInput($inputOptions);
            break;
        case 'hidden':
            $inputOptions['id']  = $inputId;
            $formField->template = '{input}';
            $formField->hiddenInput($inputOptions);
            break;
        case 'fileInput':
            $inputOptions['id']  = $inputId;
            $formField->template = '{input}';
            $formField->fileInput($inputOptions);
            break;
        default:
            if ($inputType instanceof \kartik\select2\Select2) {
                if ($formModel->$attributeName) {
                    $inputOptions['initValueText'] = $formModel->$attributeName;
                }
            }

            $inputOptions['id']            = $inputId;
            $inputOptions['options']['id'] = $inputId;
            $formField->widget($inputType, $inputOptions);
            break;
    }

    echo $inputLabel ? $formField->label($inputLabel) : $formField;
}

switch ($formModel->scenario) {
    case $formModel::SCENARIO_CREATE:
        $submitLabel = Yii::t('app', 'Create');
        break;
    case $formModel::SCENARIO_UPDATE:
        $submitLabel = Yii::t('app', 'Update');
        break;
    default:
        $submitLabel = Yii::t('app', 'Submit');
        break;
}

echo Html::beginTag('div');
echo Html::submitButton($submitLabel, [
    'class' => 'btn btn-info',
    'id'    => $modelName.$scenario.'-submit-'.$time,
    'type'  => 'submit',
]);

echo Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-secondary float-right']);
echo Html::endTag('div');

ActiveForm::end();
echo Html::endTag('div');
echo Html::endTag('div');
echo Html::endTag('div');
