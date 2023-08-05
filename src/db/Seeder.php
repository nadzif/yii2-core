<?php


namespace nadzif\core\db;


use Carbon\Carbon;
use nadzif\core\helpers\StringHelper;
use nadzif\core\validators\PhoneNumberValidator;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Seeder extends BaseObject
{

    public $activeRecordClass;
    public $quantity;
    public $attributes;

    public function insert()
    {
        $activeRecordClass = $this->activeRecordClass;

        for ($iC = 0; $iC < $this->quantity; $iC++) {
            /** @var ActiveRecord $activeRecord */
            $activeRecord = new $activeRecordClass;

            foreach ($this->attributes as $attribute => $configs) {
                if (is_array($configs)) {
                    $method = $configs[0];
                    unset($configs[0]);
                    if ($method == 'ownValue') {
                        $attributeName = $configs['targetAttribute'];
                        $value         = $activeRecord->$attributeName;
                    } else {
                        $value = call_user_func([self::class, $method], $configs);
                    }
                } else {
                    $value = $configs;
                }
                $activeRecord->$attribute = $value;
            }
            $activeRecord->save();
        }

        echo $this->quantity.' data created'.PHP_EOL;
    }

    public function random($configs)
    {
        if ($configs['type'] == 'phoneNumber') {
            $prefixes      = (new PhoneNumberValidator())->prefixes;
            $prefixesItems = $prefixes[rand(0, count($prefixes) - 1)]['data'];

            $value = $prefixesItems[array_rand($prefixesItems)];
            $value .= rand(1000000, 9999999);
        } elseif ($configs['type'] == 'email') {
            $value = StringHelper::generateWords(
                1,
                ArrayHelper::getValue($configs, 'allowNumeric', true),
                ArrayHelper::getValue($configs, 'minLength', 8),
                ArrayHelper::getValue($configs, 'maxLength', 16)
            );
            $value .= '@gmail.com';
        } elseif ($configs['type'] == 'integer') {
            $min   = ArrayHelper::getValue($configs, 'min', 0);
            $max   = ArrayHelper::getValue($configs, 'max', 100);
            $value = rand($min, $max);
            if (isset($configs['multiplier'])) {
                $value *= $configs['multiplier'];
            }
        } elseif ($configs['type'] == 'double') {
            $min     = ArrayHelper::getValue($configs, 'min', 0);
            $max     = ArrayHelper::getValue($configs, 'max', 100);
            $value   = rand($min, $max) * 10;
            $divider = rand(10, 20);
            $value   = $value / $divider;
        } elseif ($configs['type'] == 'datetime') {
            $value = Carbon::now();
            if ($configs['verb'] == 'past') {
                $value->subDays(rand(1, 365))->subHours(rand(0, 24))->subMinutes(rand(0, 60))->subSeconds(rand(0, 60));
            }

            if ($configs['verb'] == 'future') {
                $value->addDays(rand(1, 365))->addHours(rand(0, 24))->addMinutes(rand(0, 60))->addSeconds(rand(0, 60));
            }

            $value = $value->format('Y-m-d H:i:s');
        } else {
            $minlength = ArrayHelper::getValue($configs, 'length', 12);
            $maxLength = ArrayHelper::getValue($configs, 'length', 12);
            if (isset($configs['minLength'])) {
                $minlength = $configs['minLength'];
            }

            if (isset($configs['maxLength'])) {
                $maxLength = $configs['maxLength'];
            }

            $value = StringHelper::generateWords(
                ArrayHelper::getValue($configs, 'wordsLength', 1),
                ArrayHelper::getValue($configs, 'allowNumeric', false),
                $minlength,
                $maxLength,
                ArrayHelper::getValue($configs, 'glue', ' ')
            );

            if (isset($configs['call'])) {
                $value = call_user_func($configs['call'], $value);
            }
        }

        return $value;
    }

    public function hashValue($configs)
    {
        return \Yii::$app->security->generatePasswordHash($configs['value']);
    }

    public function findRecord($configs)
    {
        $activeRecordClass = $configs['targetClass'];
        /** @var ActiveRecord $activeRecord */
        $activeRecord = new $activeRecordClass;

        $result = $activeRecord::find()->orderBy('newid()')->one();
        return ArrayHelper::getValue($result, $configs['targetAttribute']);
    }

    public function randomArray($configs)
    {
        $items = $configs['items'];
        return $items[array_rand($items)];
    }

}