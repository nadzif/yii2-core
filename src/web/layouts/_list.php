<?php

/**
 * @var \yii\base\View $this
 *
 * @var \nadzif\core\web\components\GridModel $gridModel
 * @var array $gridViewConfig
 * @var array $pageSizeData
 * @var array $showCreateButton
 * @var array $createConfig
 * @var array $toolbars
 * @var boolean $inlineToolbar
 */

use nadzif\core\web\components\ActionColumn;
use nadzif\core\web\widgets\GridView;
use nadzif\core\web\widgets\Select2;
use rmrevin\yii\ionicon\Ion;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/** @var GridView $gridView */
$gridView   = new GridView($gridViewConfig);
$gridViewId = $gridView->getId();

echo Html::beginTag('div', ['class' => 'col-sm-12']);
echo Html::beginTag('div', ['class' => 'grid-container dataTables_wrapper']);
echo Html::beginTag('div', ['class' => 'datatables-tools clearfix mb-2']);

echo Html::beginTag('div', ['class' => 'd-flex justify-content-end float-right mb-2 mb-sm-0']);
if ($createConfig) {
    $createConfigDef = [
        'modalConfig'      => [
            'toggleButton' => [
                'label' => Ion::icon(Ion::_PLUS),
                'class' => 'btn btn-success ml-1'
            ]
        ],
        'activeFormConfig' => [],
        'gridViewId'       => $gridViewId,
    ];
    $formLayout      = '@nadzif/core/web/layouts/ajax/_form';

    echo $this->render($formLayout, ArrayHelper::merge($createConfigDef, $createConfig));
}

foreach ($toolbars as $toolbar) {
    echo $toolbar;
}

echo Html::button(Ion::icon(Ion::_ANDROID_OPTIONS), [
    'class'   => 'btn btn-info ml-1',
    'onclick' => "(function () { $('tr#{$gridViewId}-filters').toggleClass('d-none'); })()",
]);

echo Html::button(Ion::icon(Ion::_ANDROID_SYNC), [
    'class'   => 'btn btn-info ml-1',
    'onclick' => "(function (e) { $.pjax.reload({container:'#{$gridViewId}-pjax'}); })()",
]);
echo Html::endTag('div');

echo Html::beginTag('div', ['id' => "{$gridViewId}-filters", 'class' => 'select2-wrap dataTables_length']);
echo Select2::widget([
    'model'        => $gridModel,
    'attribute'    => 'pageSize',
    'theme'        => ArrayHelper::getValue(Yii::$app->params, 'widget.select2.theme', Select2::THEME_BOOTSTRAP),
    'hideSearch'   => true,
    'data'         => $pageSizeData,
    'options'      => ['class' => 'grid-size-filter'],
    'pluginEvents' => [
        'change' => "function(e){ $.pjax({container: '#{$gridViewId}-pjax'}) }"
    ]
]);
echo Html::endTag('div'); //dataTables_length

echo Html::endTag('div'); //datatables-tools

$gridView->run();

echo Html::endTag('div'); //dataTables_wrapper
echo Html::endTag('div'); //col-sm-12

if ($gridModel->actionColumn && $gridModel->actionColumnClass == ActionColumn::className()) {
    echo Html::tag('div', false, ['id' => 'grid-update-section', 'style' => 'width:0; height:0; overflow: hidden;']);
}

$inlineToolbar = isset($inlineToolbar) ? $inlineToolbar : true;
if ($inlineToolbar) {
    $inlineToolbarsJS = <<<JS
    (function() {
        function inlineDTToolbars() {
            var parent = $("#{$gridViewId}-pjax").length > 0 ? $("#{$gridViewId}-pjax") : $("#{$gridViewId}");
                parent.prev('.datatables-tools').prependTo('#{$gridViewId}-pjax .grid-view-toolbar');

            parent.find('.datatables-tools').addClass('flex-fill mr-2').removeClass('mb-2');
            parent.find('.grid-view-toolbar .btn-group').addClass('btn-group-sm');

        }

        inlineDTToolbars();
        $(document).on('pjax:beforeSend', function (event) {
            if ($(event.target).attr('id') == "{$gridViewId}-pjax") {
                $("#{$gridViewId} .datatables-tools").addClass('mb-2');
                $("#{$gridViewId} .datatables-tools").prependTo($("#{$gridViewId}-pjax").parent());
                $("#{$gridViewId} .grid-view-toolbar").addClass('d-none').removeClass('d-flex');
            }
        });

        $(document).on('pjax:end', function (event) {
            if ($(event.target).attr('id') == "{$gridViewId}-pjax") {
                inlineDTToolbars();
                $("#{$gridViewId} .grid-view-toolbar").removeClass('d-none').addClass('d-flex');
            }
        });
    })();
JS;
    $this->registerJs(
        $inlineToolbarsJS,
        View::POS_READY
    );
}
