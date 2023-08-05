<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/14/2019
 * Time: 12:20 AM
 */

namespace nadzif\core\web\actions;


use nadzif\core\web\components\Action;
use nadzif\core\web\components\GridModel;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class ListAction extends Action
{
    public $gridClass;
    public $gridViewConfig = [];
    public $query          = false;
    public $columns;
    public $title;
    public $description;
    public $breadcrumbs    = [];
    public $toolbars       = [];
    public $inlineToolbar  = true;

    ////////////
    public $showToggleData = false;
    public $view           = '@nadzif/core/web/layouts/_list';
    public $pageSizeData   = [
        10  => 10,
        25  => 25,
        50  => 50,
        100 => 100,
    ];
    public $createConfig   = [];
    /**
     * @var GridModel
     */
    private $_gridModel;

    public function run()
    {
        if ($this->title) {
            $this->controller->getView()->title = $this->title;
        }

        if ($this->breadcrumbs) {
            $this->controller->getView()->params['breadcrumbs'] = $this->breadcrumbs;
        }
        if ($this->description) {
            $this->controller->getView()->params['description'] = $this->description;
        }

        $this->_gridModel = new $this->gridClass;

        $dataProvider = $this->_gridModel->getDataProvider($this->query);
        $columns      = $this->columns ?: $this->_gridModel->getColumns();

        $_gridViewConfig = [
            'dataProvider'            => $dataProvider,
            'filterModel'             => $this->_gridModel,
            'columns'                 => $columns,
            'toggleData'              => $this->showToggleData,
            'toolbarContainerOptions' => [
                'class' => 'grid-view-toolbar mb-2'
            ]
        ];

        if ($this->createConfig) {
            /** @var Model $formModel */
            $formModel = new $this->createConfig['formClass'];
            $formModel->setScenario(ArrayHelper::getValue($this->createConfig, 'scenario', Model::SCENARIO_DEFAULT));

            $this->createConfig['formModel'] = $formModel;
        }

        if ($this->inlineToolbar) {
            Html::addCssClass($_gridViewConfig['toolbarContainerOptions'], 'w-100 d-flex flex-row');
        }

        return $this->controller->render($this->view, [
            'gridModel'      => $this->_gridModel,
            'toolbars'       => $this->toolbars,
            'inlineToolbar'  => $this->inlineToolbar,
            'gridViewConfig' => ArrayHelper::merge($_gridViewConfig, $this->gridViewConfig),
            'pageSizeData'   => $this->pageSizeData,
            'createConfig'   => $this->createConfig,
        ]);
    }
}