<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/14/2019
 * Time: 12:25 AM
 */

namespace nadzif\core\web\components;


use kartik\grid\CheckboxColumn;
use kartik\grid\ExpandRowColumn;
use kartik\grid\SerialColumn;
use nadzif\core\db\ActiveRecord;
use nadzif\core\web\widgets\DatePicker;
use nadzif\core\web\widgets\DateRangePicker;
use nadzif\core\web\widgets\GridView;
use nadzif\core\web\widgets\Select2;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\validators\NumberValidator;
use yii\web\JsExpression;


class GridModel extends \yii\db\ActiveRecord
{
    const FILTER_LIKE       = 'like';
    const FILTER_DATE       = 'date';
    const FILTER_DATETIME   = 'datetime';
    const FILTER_DATE_RANGE = 'dateRange';
    const FILTER_RANGE      = 'range';
    const FILTER_EQUAL      = 'equal';
    const FILTER_MORE_THAN  = 'moreThan';
    const FILTER_LESS_THAN  = 'lessThan';
    const FILTER_RELATION   = 'relation';
    const FILTER_LIST       = 'list';
    const FILTER_LIST_AJAX  = 'listAjax';

    const ACTION_COLUMN_POSITION_LEFT  = 'left';
    const ACTION_COLUMN_POSITION_RIGHT = 'right';

    public $dropdownClass          = Select2::class;
    public $dropdownOptions        = [];
    public $dropdownItemKey;
    public $datePickerClass        = DatePicker::class;
    public $datePickerOptions      = [];
    public $dateRangePickerClass   = DateRangePicker::class;
    public $dateRangePickerOptions = [];

    /** @var ActiveRecord */
    public  $activeRecordModel;
    public  $dateFilterFormat         = 'yyyy-mm-dd';
    public  $dateRangeFilterSeparator = ' - ';
    public  $joinWith                 = [];
    public  $pageSize                 = 10;
    public  $sortKey                  = 'id';
    public  $serialColumn             = true;
    public  $serialColumnClass        = SerialColumn::class;
    public  $serialColumnOptions      = [];
    public  $checkboxColumn           = false;
    public  $checkboxColumnClass      = CheckboxColumn::class;
    public  $checkboxColumnOptions    = [];
    public  $actionColumn             = true;
    public  $actionColumnClass        = ActionColumn::class;
    public  $actionColumnOptions      = [];
    public  $actionColumnPosition     = 'right';
    public  $expandRowColumn          = false;
    public  $expandRowColumnClass     = ExpandRowColumn::class;
    public  $expandRowColumnOptions   = [];
    public  $defaultSortOrder         = ['createdAt' => SORT_DESC];
    private $_activeRecord;
    private $_columns;
    private $_filters;

    /**
     * @param $key
     * @param $filterOption
     *
     * @throws InvalidConfigException
     */
    protected static function hasFilterOption($key, $filterOption)
    {
        if (ArrayHelper::keyExists($key, $filterOption)) {
            throw new InvalidConfigException();
        }
    }

    public function init()
    {
        parent::init();

        if ($this->activeRecordModel) {
            $this->_activeRecord = new $this->activeRecordModel;
        }

        if ($this->dropdownClass == Select2::className()) {
            $this->dropdownOptions = ArrayHelper::merge(['pluginOptions' => ['allowClear' => true]],
                $this->dropdownOptions);

            $this->dropdownItemKey = 'data';
        } else {
            $this->dropdownItemKey = 'items';
        }

        $this->datePickerOptions = ArrayHelper::merge([
            'pickerButton'  => ['label' => '<i class="fa fa-calendar"></i>'],
            'pluginOptions' => [
                'autoclose' => true,
                'format'    => $this->dateFilterFormat,
            ],
        ],
            $this->dateRangePickerOptions
        );

        $this->dateRangePickerOptions = ArrayHelper::merge([
            'pluginOptions' => [
                'autoclose'     => true,
                'convertFormat' => true,
                'locale'        => [
                    'format'    => 'YYYY-MM-DD',
                    'separator' => $this->dateRangeFilterSeparator,
                ],
            ],
        ],
            $this->dateRangePickerOptions
        );


        $this->generateFilterRules();
    }

    private function generateFilterRules()
    {
        foreach ($this->filterRules() as $filterRule) {
            $attributes       = $filterRule[0];
            $attributesFilter = count($filterRule) == 1 ? self::FILTER_LIKE : $filterRule[1];
            $filterOptions    = [];

            if (count($filterRule) > 1) {
                foreach ($filterRule as $key => $value) {
                    if ($key != 0 || $key != 1) {
                        $filterOptions[$key] = $value;
                    }
                }
            }

            if (is_array($attributes)) {
                foreach ($attributes as $filterAttribute) {
                    $this->filterRule($filterAttribute, $attributesFilter, $filterOptions);
                }
            } else {
                $this->filterRule($attributes, $attributesFilter, $filterOptions);
            }
        }
    }

    public function filterRules()
    {
        return [[$this->getAttributesKey(), self::FILTER_LIKE]];
    }

    protected function getAttributesKey()
    {
        return array_keys($this->attributes);
    }

    private function filterRule($attribute, $filter, $filterOptions = [])
    {
        $attributeQuery = $this->queryRules()[$attribute];
        $attributeLabel = ArrayHelper::getValue($filterOptions, 'label', $this->getAttributeLabel($attribute));

        switch ($filter) {
            case self::FILTER_LIKE:
                $defaultFormat       = 'text';
                $condition           = ['like', $attributeQuery];
                $filterWidgetOptions = ['placeholder' => 'tes'];
                break;

            case self::FILTER_MORE_THAN:
                $defaultFormat = 'decimal';
                $condition     = ['>=', $attributeQuery];
                break;

            case self::FILTER_LESS_THAN:
                $defaultFormat = 'decimal';
                $condition     = ['<=', $attributeQuery];
                break;

            case self::FILTER_LIST:
                $defaultFormat       = 'text';
                $condition           = ['=', $attributeQuery];
                $filterType          = $this->dropdownClass;
                $filterWidgetOptions = ArrayHelper::merge(
                    ['options' => ['placeholder' => $attributeLabel]], $this->dropdownOptions,
                    ArrayHelper::getValue($filterOptions, 'dropdownOptions', []),
                    [$this->dropdownItemKey => $filterOptions['items']]
                );
                break;

            case self::FILTER_LIST_AJAX:
                $pluginOptions = [
                    'allowClear'         => true,
                    'minimumInputLength' => 3,
                    'language'           => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                    ],
                    'ajax'               => [
                        'url'      => Url::to(ArrayHelper::getValue($this->dropdownOptions, 'ajaxUrl')),
                        'dataType' => 'json',
                        'data'     => new JsExpression('function(params) { return {q:params.term}; }'),
                    ],
                    'escapeMarkup'       => new JsExpression('function (markup) { return markup; }'),
                    'templateResult'     => new JsExpression('function(city) { return city.text; }'),
                    'templateSelection'  => new JsExpression('function (city) { return city.text; }'),
                ];
                unset($this->dropdownOptions['ajaxUrl']);

                $defaultFormat       = 'text';
                $condition           = ['=', $attributeQuery];
                $filterType          = $this->dropdownClass;
                $filterWidgetOptions = ArrayHelper::merge(
                    ['options' => ['placeholder' => $attributeLabel]], $this->dropdownOptions,
                    ArrayHelper::getValue($filterOptions, 'dropdownOptions', []), ['pluginOptions' => $pluginOptions]
                );
                break;

            case self::FILTER_DATE_RANGE:
                $defaultFormat       = 'date';
                $filterType          = $this->dateRangePickerClass;
                $filterWidgetOptions = $this->dateRangePickerOptions;
                $condition           = ['between', $attributeQuery];
                break;

            case self::FILTER_DATE:
                $defaultFormat       = 'date';
                $filterType          = $this->datePickerClass;
                $filterWidgetOptions = $this->datePickerOptions;
                $condition           = ['between', $attributeQuery];
                break;

            case self::FILTER_DATETIME:
                $defaultFormat       = 'datetime';
                $filterType          = $this->datePickerClass;
                $filterWidgetOptions = $this->datePickerOptions;
                $condition           = ['between', $attributeQuery];
                break;

            case self::FILTER_RANGE:
                $defaultFormat = 'decimal';
//                $filterType          = MaskedInput::class;
//                $filterWidgetOptions = ['mask' => '##### - #####'];
                $condition = ['between', $attributeQuery];
                break;

            default:
                $defaultFormat = 'text';
                $condition     = ['=', $attributeQuery];
                break;
        }

        $this->_filters[$attribute][] = $condition;

        $this->_columns[$attribute]['format']    = ArrayHelper::getValue($filterOptions, 'format', $defaultFormat);
        $this->_columns[$attribute]['label']     = $attributeLabel;
        $this->_columns[$attribute]['attribute'] = $attribute;

        $this->_columns[$attribute]['value'] = ArrayHelper::getValue($filterOptions, 'value', $attribute);

        if ($this->_columns[$attribute]['format'] == 'phone') {
            $this->_columns[$attribute]['format'] = 'html';

            $this->_columns[$attribute]['value'] = function ($model) use ($attribute) {
                return Html::a($model->$attribute, 'tel:'.$model->$attribute);
            };
        }

        if (isset($filterType) && isset($filterWidgetOptions)) {
            $this->_columns[$attribute]['filterType']          = $filterType;
            $this->_columns[$attribute]['filterWidgetOptions'] = $filterWidgetOptions;
        }

        if (isset($filterOptions['columnOptions'])) {
            foreach ($filterOptions['columnOptions'] as $optionsKey => $columnOption) {
                $this->_columns[$attribute][$optionsKey] = $columnOption;
            }
        }
    }

    /**
     * @return array
     */
    public function queryRules()
    {
        $queryRules = [];
        foreach ($this->getAttributesKey() as $attributeKey) {
            $queryRules[$attributeKey] = $this->tableName().'.'.$attributeKey;
        }

        return $queryRules;
    }

    public function attributeLabels()
    {
        return $this->activeRecordModel->attributeLabels();
    }

    public function rules()
    {
        return [
            ['pageSize', 'integer'],
            [$this->getAttributesKey(), 'safe'],
            [$this->getAttributesKey(), 'string'],
        ];
    }

    public function getDataProvider($searchQuery = false)
    {
        $query = $searchQuery ?: self::find();

        if ($this->joinWith) {
            $query->joinWith($this->joinWith);
        }

        $requestParams = \Yii::$app->request->queryParams;

        $this->load($requestParams);

        $activeDataProviderConfig = [
            'query'      => $query,
            'pagination' => ['pageSize' => $this->pageSize,],
            'key'        => $this->sortKey,
        ];

        if ($this->defaultSortOrder) {
            $activeDataProviderConfig['sort'] = ['defaultOrder' => $this->defaultSortOrder];
        }

        $dataProvider = new ActiveDataProvider($activeDataProviderConfig);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $gridColumns = $this->getColumns();
        $queryRules  = $this->queryRules();
        foreach ($gridColumns as $attributeKey => $data) {
            if (isset($data['class'])) {
                continue;
            }

            foreach ($this->_filters[$attributeKey] as $filter) {
                $dataProvider->sort->attributes[$attributeKey] = [
                    'asc'  => [$queryRules[$attributeKey] => SORT_ASC],
                    'desc' => [$queryRules[$attributeKey] => SORT_DESC],
                ];

                if ($filter[0] != 'between') {
                    $filter[] = $this->$attributeKey;
                } else {
                    $explodedDate = explode($this->dateRangeFilterSeparator, $this->$attributeKey);
                    if (count($explodedDate) == 2) {
                        $filter[] = str_replace('_', '', $explodedDate[0]);
                        $filter[] = str_replace('_', '', $explodedDate[1]);;
                    } else {
                        if (count(explode('-', $this->$attributeKey)) == 3) {
                            $filter[] = $this->$attributeKey.' 00:00:00';
                            $filter[] = $this->$attributeKey.' 23:59:59';
                        } else {
                            $filter = [$attributeKey => $this->$attributeKey];
                        }
                    }
                }

                $query->andFilterWhere($filter);
            }
        }

        return $dataProvider;
    }

    public function getColumns()
    {
        $columns = [];

        if ($this->serialColumn) {
            $columns[] = ArrayHelper::merge(['class' => $this->serialColumnClass], $this->serialColumnOptions);
        }

        if ($this->checkboxColumn) {
            $columns[] = ArrayHelper::merge(['class' => $this->checkboxColumnClass], $this->checkboxColumnOptions);
        }

        if ($this->actionColumn && $this->actionColumnPosition == self::ACTION_COLUMN_POSITION_LEFT) {
            $columns[] = ArrayHelper::merge(
                ['class' => $this->actionColumnClass, 'noWrap' => true],
                $this->actionColumnOptions
            );
        }

        if ($this->_columns) {
            $columns += $this->_columns;
        } else {
            $columns += $this->getAttributesKey();
        }

        if ($this->actionColumn && $this->actionColumnPosition == self::ACTION_COLUMN_POSITION_RIGHT) {
            $columns[] = ArrayHelper::merge(
                ['class' => $this->actionColumnClass, 'noWrap' => true],
                $this->actionColumnOptions
            );
        }

        if ($this->expandRowColumn) {
            $defaultExpandRowColumnOptions = [
                'expandIcon'              => '<span class="fas fa-expand text-info"></span>',
                'collapseIcon'            => '<span class="fas fa-compress text-info"></span>',
                'detailAnimationDuration' => 0,
                'value'                   => function ($model, $key, $index, $column) {
                    return GridView::ROW_COLLAPSED;
                },
            ];

            $columns[] = ArrayHelper::merge(
                ['class' => $this->expandRowColumnClass],
                $defaultExpandRowColumnOptions,
                $this->expandRowColumnOptions
            );
        }

        return $columns;
    }

    public function getFilters()
    {
        return $this->_filters;
    }

    public function validateRange($attribute, $params = [])
    {
        $explodedValues = explode(' - ', $this->$attribute);
        if (count($explodedValues) != 2) {
            $this->addError($attribute, \Yii::t('app', 'Use valid format. ex:0 - 999'));
        } else {
            $numberValidator = new NumberValidator();

            $numberValidator->validate($explodedValues[0]);
            $numberValidator->validate($explodedValues[1]);
        }
    }
}