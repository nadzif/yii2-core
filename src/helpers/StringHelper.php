<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/17/2019
 * Time: 2:46 AM
 */

namespace nadzif\core\helpers;


use yii\helpers\StringHelper as YiiStringHelper;

class StringHelper
{
    public static function replace($string, $default, $array = [])
    {
        if ($string) {
            $placeholders = [];
            foreach ((array)$array as $name => $value) {
                if (is_array($value)) {
                    continue;
                }

                $placeholders['{'.$name.'}'] = $value;
            }

            return ($placeholders === []) ? $string : strtr($string, $placeholders);
        } else {
            return $default;
        }
    }

    public static function initial($string, $length = 2)
    {
        $initial = '';
        $string  = preg_replace('/[^\p{L}\p{N}\s]/u', '', $string);
        $words   = explode(' ', $string);

        foreach ($words as $word) {
            $initial .= substr($word, 0, 1);

            if (strlen($initial) >= $length) {
                break;
            }
        }

        return $initial;
    }

    public static function shorten($string, $length = 30)
    {
        $regex = preg_replace('/&#?[a-z0-9]+;/i', '', preg_replace('/\r|\n/', '', $string));
        $msg   = strip_tags($regex);
        return YiiStringHelper::truncate($msg, $length);
    }

    public static function generateWords($length = 1, $allowNumeric = false, $wordMin = 5, $wordMax = 10, $glue = ' ')
    {
        $chars = [
            ['a', 'i', 'u', 'e', 'o'],
            ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'z', 'y'],
        ];

        if ($allowNumeric) {
            $chars[] = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        }

        $charLength = count($chars);

        $words = [];
        for ($iC = 0; $iC < $length; $iC++) {
            $wordLength = rand($wordMin, $wordMax);
            $word       = '';

            $latestCharIndex = rand(0, $charLength - 1);
            for ($iD = 0; $iD < $wordLength; $iD++) {
                while (true) {
                    $index = rand(0, $charLength - 1);
                    if ($index != $latestCharIndex) {
                        break;
                    }
                }
                $word            .= $chars[$index][rand(0, count($chars[$index]) - 1)];
                $latestCharIndex = $index;
            }

            $words[] = $word;
        }

        return implode($glue, $words);
    }
}
