<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 12/19/2020
 * Time: 2:10 PM
 */

namespace nadzif\core\helpers;


use nadzif\core\validators\PhoneNumberValidator;

class CommonHelper
{
    public static function phoneFormat($phoneNumber = '08115347666', $countryCode = '62')
    {
        $phoneNumberValidator = new PhoneNumberValidator();

        if (!$phoneNumberValidator->validate($phoneNumber)) {
            return null;
        }

        $validPhoneNumber = $phoneNumber;

        $firstDigit = substr($phoneNumber, 0, 1);
        $nextDigit  = substr($phoneNumber, 1);

        if ($firstDigit == "0") {
            $validPhoneNumber = $countryCode.$nextDigit;
        }

        return $validPhoneNumber;
    }
}