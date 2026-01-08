<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Library;

class Utils
{
   public function getRandomString($str, $length)
   {
      return substr(str_shuffle($str), 0, $length);
   }

   public function getRandomPassword($minLength = 12): string
   {
      $upperCaseChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      $lowerCaseChars = "abcefghijklmnopqrstuvwxyz";
      $numbers = "1234567890";
      $specialChars = "~*!@$#%_+.?:,{})";

      $thirtyPercent = ceil(0.3 * $minLength);
      $twentyPercent = ceil(0.2 * $minLength);

      return str_shuffle($this->getRandomString($upperCaseChars, $thirtyPercent) . $this->getRandomString($lowerCaseChars, $thirtyPercent) . $this->getRandomString($numbers, $twentyPercent) . $this->getRandomString($specialChars, $twentyPercent));
   }

   public function getISOLanguageCode($language)
   {
      $language = strtolower($language);
      return array_key_exists($language, LANGUAGE_ISO_CODES) ? LANGUAGE_ISO_CODES[$language] : DEFAULT_LANGUAGE_CODE;
   }

   public function sanitizeAndValidateNumber($number)
   {
      $sanitized = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
      if ($sanitized !== null && filter_var($sanitized, FILTER_VALIDATE_INT) !== false) {
         return $number;
      }
      return null;
   }

   public function sanitizeAndValidateString($string)
   {
      return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
   }
}
