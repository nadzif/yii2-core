<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace nadzif\core\web\widgets;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Description of Table
 *
 * @author Mohammad Nadzif <demihuman@live.com>
 */
class Table extends \yii\base\Widget
{
    const HEADER_MODE_MULTIPLE = 'headerMultiple';
    const HEADER_MODE_SINGLE   = 'headerSingle';

    public $wrapper;
    public $reverseBody      = false;
    public $containerOptions = [];
    public $options          = ['class' => 'table table-bordered table-hover'];
    public $headerOptions    = [];
    public $headers          = [];
    public $theadOptions     = [];
    public $tbodyOptions     = [];
    public $headerMode       = self::HEADER_MODE_SINGLE;
    public $rows             = [];
    public $fieldConfig      = [];
    public $rowOptions       = [];
    public $summaryOptions   = [];
    public $summaryFormat    = 'decimal';
    public $responsive       = true;
    public $matrix           = [];
    public $headersLabel     = [];
    public $identifyEmpty    = true;
    public $allowHtml        = false;

    private $summaryResult = [];

    public function __construct($config = array())
    {
        parent::__construct($config);
        if ($this->responsive) {
            if (isset($this->containerOptions['class'])) {
                $this->containerOptions['class'] .= ' table-responsive';
            } else {
                $this->containerOptions['class'] = 'col-sm-12 table-responsive';
            }
        }
    }

    public function addRow($data, $rowOptions = [])
    {
        $this->rows[]       = $data;
        $this->rowOptions[] = $rowOptions;
    }

    public function addEmptyRow()
    {
        $this->rows[]       = [['label' => null, 'options' => ['colspan' => count($this->headers)]]];
        $this->rowOptions[] = [];
    }

    public function run()
    {
        echo $this->wrapper ? Html::beginTag('div', ['class' => 'row']) : "";

        echo Html::beginTag('div', $this->containerOptions); // container
        echo Html::beginTag('table', $this->options);
        echo Html::beginTag('thead', $this->theadOptions);
        $this->generateHeader();
        $this->headersLabel = ArrayHelper::getColumn($this->headers, 'label');
        echo Html::endTag('thead');

        // <TABLE BODY>
        echo Html::beginTag('tbody', $this->tbodyOptions);
        if (!$this->rows) {
            $countColumn = count($this->headers);
            echo Html::beginTag('tr');
            echo Html::tag('td', \Yii::t('app', 'No Data Available'), [
                'colspan' => $countColumn,
                'class'   => 'not-set text-center'
            ]);
            echo Html::endTag('tr');
        } else {
            if ($this->reverseBody) {
                $this->rows       = array_reverse($this->rows);
                $this->rowOptions = array_reverse($this->rowOptions);
            }
            $this->generateRow();
        }
        echo Html::endTag('tbody');

        if (count($this->summaryResult)) {
            echo Html::beginTag('tfoot');
            $this->generateFooter();
            echo Html::endTag('tfoot');
        }
        // </TABLE BODY>

        echo Html::endTag('table');
        echo Html::endTag('div'); // end container

        echo $this->wrapper ? Html::endTag('div') : "";
    }

    public function generateHeader()
    {
        if ($this->headers) {
            if ($this->headerMode == self::HEADER_MODE_SINGLE) {
                echo Html::beginTag('tr', $this->headerOptions);
                foreach ($this->headers as $header) {
                    $label  = ArrayHelper::getValue($header, 'label', $header);
                    $format = ArrayHelper::getValue($header, 'format', 'text');
                    $label  = $format != 'html' ? \Yii::$app->formatter->format($label, $format) : $label;

                    if ($this->allowHtml) {
                        $label = Html::decode($label);
                    }

                    echo Html::tag('th', $label, ArrayHelper::getValue($header, 'options', []));
                }
                echo Html::endTag('tr');
            } else {
                foreach ($this->headers as $index => $headerRow) {
                    echo Html::beginTag('tr', $this->headerOptions);
                    foreach ($headerRow as $header) {
                        $label  = ArrayHelper::getValue($header, 'label', $header);
                        $format = ArrayHelper::getValue($header, 'format', 'text');
                        $label  = $format != 'html' ? \Yii::$app->formatter->format($label, $format) : $label;
                        if ($this->allowHtml) {
                            $label = Html::decode($label);
                        }

                        echo Html::tag('th', $label, ArrayHelper::getValue($header, 'options', []));
                    }
                    echo Html::endTag('tr');
                }
            }
        }
    }

    public function generateRow()
    {
        foreach ($this->rows as $index => $row) {
            echo Html::beginTag('tr', $this->rowOptions[$index]);
            foreach ($row as $columnIndex => $data) {
                $label                              =
                    is_array($data) ? ArrayHelper::getValue($data, 'label', null) : $data;
                $this->matrix[$index][$columnIndex] = $label;

                if (is_array($data)) {
                    $columnSummary = ArrayHelper::getValue($data, 'columnSummary', false);

                    if ($columnSummary) {
                        if (isset($this->summaryResult[$columnIndex])) {
                            $this->summaryResult[$columnIndex] += $data['label'];
                        } else {
                            $this->summaryResult[$columnIndex] = $data['label'];
                        }
                    }
                    $format = ArrayHelper::getValue($data, 'format', 'text');

                    if ($format != 'html') {
                        $label = \Yii::$app->formatter->format($label, $format);
                    }
                }

                if ($this->allowHtml) {
                    $label = Html::decode($label);
                }

                echo Html::beginTag('td', ArrayHelper::getValue($data, 'options', []));
                if (isset($data['labelTag'])) {
                    echo Html::tag($data['labelTag'], $label);
                } else {
                    echo $label;
                }
                echo Html::endTag('td');
            }
            echo Html::endTag('tr');
        }
    }

    public function generateFooter()
    {
        echo Html::beginTag('tr');

        $summaryLabelColumn = 0;

        if ($this->summaryOptions) {
            $summaryLabelColumn = ArrayHelper::getValue($this->summaryOptions, 'colspan', 1);
            $summaryLabelString = ArrayHelper::getValue($this->summaryOptions, 'label', null);
            echo Html::tag('td', Html::tag('b', $summaryLabelString), ['colspan' => $summaryLabelColumn]);
        }


        foreach ($this->headers as $index => $header) {
            if ($summaryLabelColumn) {
                $summaryLabelColumn--;
                continue;
            }

            $columnLabel = isset($this->summaryResult[$index]) ?
                \Yii::$app->formatter->format($this->summaryResult[$index], $this->summaryFormat)
                : null;

            echo Html::tag('td', Html::tag('b', $columnLabel), ['align' => 'right']);
        }
        echo Html::endTag('tr');
    }

    public function asArray()
    {
        $tableAsArray = $this->rows;
        array_unshift($tableAsArray, $this->headers);
    }
}