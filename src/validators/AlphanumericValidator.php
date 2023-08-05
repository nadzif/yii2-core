<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/28/2020
 * Time: 11:12 PM
 */

namespace nadzif\core\validators;

use yii\validators\RegularExpressionValidator;

class AlphanumericValidator extends RegularExpressionValidator
{
    public $pattern = '/^[a-zA-Z0-9]+(\s{0,1}[a-zA-Z0-9 ])*$/';
}