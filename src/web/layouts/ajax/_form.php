<?php

/**
 * @var \nadzif\core\web\components\FormModel $formModel
 * @var string $scenario
 * @var array|string $actionUrl
 * @var array $modalConfig
 * @var array $activeFormConfig
 */

use nadzif\core\web\components\ActiveForm;
use nadzif\core\web\widgets\AjaxSubmitButton;
use nadzif\core\web\widgets\Modal;
use rmrevin\yii\ionicon\Ion;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\JsExpression;

$time = time();

$modalTitle       = ArrayHelper::getValue($modalConfig, 'title', Yii::t('app', 'Form'));
$modalDescription = ArrayHelper::getValue($modalConfig, 'description', false);

$scenario  = $formModel->getScenario();
$modelName = StringHelper::basename(get_class($formModel));

$_modalConfig = [
    'id'    => 'modal-'.$modelName.'-'.$scenario,
    'title' => Html::tag('h6', $modalTitle, ['class' => 'tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold'])
];

$_activeFormConfig = [
    'id'     => $modelName.$scenario.'-form-'.$time,
    'action' => $actionUrl
];

$modal     = Modal::begin(ArrayHelper::merge($_modalConfig, $modalConfig));
$form      = ActiveForm::begin(ArrayHelper::merge($_activeFormConfig, $activeFormConfig));
$formRules = $formModel->formRules();
$hasUpload = ArrayHelper::getValue($activeFormConfig, 'options.enctype', false) == 'multipart/form-data';

foreach ($formModel->scenarios()[$scenario] as $attributeName) {
    $attributeOptions = ArrayHelper::getValue($formRules, $attributeName, []);

    $inputType    = ArrayHelper::getValue($attributeOptions, 'inputType', 'text');
    $inputOptions = ArrayHelper::getValue($attributeOptions, 'inputOptions', []);
    $inputScripts = ArrayHelper::getValue($attributeOptions, 'inputScripts', []);
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

    foreach ($inputScripts as $script) {
        $this->registerJs($script);
    }
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

$formId            = $form->getId();
$modalId           = $modal->getId();
$iconSuccess       = Ion::icon(Ion::_IOS_CHECKMARK_OUTLINE);
$iconWarning       = Ion::icon(Ion::_ANDROID_WARNING);
$iconDanger        = Ion::icon(Ion::_IOS_FLAME);
$iconInfo          = Ion::icon(Ion::_INFORMATION);
$buttonId          = $modelName.$scenario.'-submit-'.$time;
$buttonState       = $modelName.$scenario.'_state_'.$time;
$buttonLoadingText = Html::tag('span', null, ['class' => 'spinner-border spinner-border-sm']).' Loading...';

$submitSuccess = <<<JS
    function(html) {
        try {
            html = JSON.parse(html);
            $('#output').html(html);
            
            if($("#$gridViewId-pjax").length){
                $.pjax.reload({container:"#$gridViewId-pjax"});
            }
            
            if(html.data !== undefined && html.data.alert != undefined){
                var alertObject = html.data.alert;
                if(Array.isArray(alertObject)){
        
                    for (var i in alertObject){
                    var alertData = alertObject[i];

                        switch (alertData.type){
                            case 'warning':
                                var alertIcon = '$iconWarning';
                                break;
                            case 'danger':
                                var alertIcon = '$iconDanger';
                                break;
                            case 'info':
                                var alertIcon = '$iconInfo';
                                break;
                            default:
                                var alertIcon = '$iconSuccess';
                        }
                        
                        window.FloatAlert.alert(alertData.title, alertData.message, alertData.type, alertIcon);
                    } 
                }else{
                    window.FloatAlert.alert(alertObject.title, alertObject.message, alertObject.type, '$iconSuccess');
                }
            }

        
            $('#{$buttonId}').button('reset');

            $("#$formId")[0].reset();
            $("#$formId").find('select').each(function() {
                $(this).val(null).trigger('change');
            });
            $("#$modalId").modal('hide');
        } catch (error) {}
    }
JS;

$buttonLoading = <<<JS
    function (xhr) {
        try {
            if ({$buttonState} == false) {
                {$buttonState} = true;
                $('#{$buttonId}').button('loading');
            } else {
                {$buttonState} = false;
                xhr.abort();
            }

        } catch (error) {
            $('#{$buttonId}').button('reset');
            {$buttonState} = false;
        }
    }
JS;

$submitError = <<<JS
    function (xhr) {
        try {
            $('#{$buttonId}').button('reset');
        } catch (error) {}

        {$buttonState} = false;
    }
JS;

$formState = <<<JS
    var {$buttonState} = false;
    $("#$modalId").on('hidden.bs.modal', function (e) {
        {$buttonState} = false;
        $('#{$buttonId}').button('reset');
        $("#$formId")[0].reset();
    });
JS;

$this->registerJs($formState);

echo Html::beginTag('div');
echo AjaxSubmitButton::widget([
    'label'             => $submitLabel,
    'useWithActiveForm' => $formId,
    'formHasUpload'     => $hasUpload,
    'ajaxOptions'       => [
        'type'       => 'POST',
        'url'        => Url::to($actionUrl),
        'beforeSend' => new JsExpression($buttonLoading),
        'success'    => new JsExpression($submitSuccess),
        'error'      => new JsExpression($submitError),
    ],
    'options'           => [
        'class' => 'btn btn-info',
        'id'    => $buttonId,
        'type'  => 'submit',
        'data'  => [
            'loading-text' => $buttonLoadingText
        ]
    ],
]);

echo Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-secondary float-right']);
echo Html::endTag('div');

ActiveForm::end();
Modal::end();
