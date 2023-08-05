<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 12/1/2019
 * Time: 3:36 PM
 */

namespace nadzif\core\web\widgets;


use demogorgorn\ajax\AjaxSubmitButton as DemogorgornAjaxSubmitButton;
use yii\helpers\Json;
use yii\web\JsExpression;

class AjaxSubmitButton extends DemogorgornAjaxSubmitButton
{
    public $formHasUpload = false;

    protected function registerAjaxFormScript()
    {
        $view = $this->getView();

        if (!isset($this->ajaxOptions['type'])) {
            $this->ajaxOptions['type'] = new JsExpression('$(this).attr("method")');
        }

        if (!isset($this->ajaxOptions['url'])) {
            $this->ajaxOptions['url'] = new JsExpression('$(this).attr("action")');
        }

        if (!isset($this->ajaxOptions['data']) && isset($this->ajaxOptions['type'])) {
            if ($this->formHasUpload) {
                $this->ajaxOptions['data']        = new JsExpression('new FormData( this )');
                $this->ajaxOptions['processData'] = new JsExpression('false');
                $this->ajaxOptions['contentType'] = new JsExpression('false');
            } else {
                $this->ajaxOptions['data'] = new JsExpression('$(this).serialize()');
            }
        }

        $this->ajaxOptions = Json::encode($this->ajaxOptions);

        $js = <<<SEL
        $(document).unbind('beforeSubmit.{$this->useWithActiveForm}').on('beforeSubmit.{$this->useWithActiveForm}', "#{$this->useWithActiveForm}", function () {
            if ($(this).find('.has-error').length < 1) {
                $.ajax({$this->ajaxOptions});
            }
            return false; // Cancel form submitting.
        });
SEL;

        $view->registerJs($js);
    }
}