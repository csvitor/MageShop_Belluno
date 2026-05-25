<?php

class MageShop_Belluno_Validations_CredentialsValidator {

  /**
   * Function to validate number og cellphone
   * @param string $cellphone
   * @return bool
   */
  public function validateCellphone($cellphone) {
    if (strlen($cellphone) >= 9) {
    	return true;
    } else {
    	return false;
    }
  }

  /**
   * Function to validate date birth
   * @param string $date
   * @return bool
   */
  public function validateDateBirth($date) {
    if ($this->validateDate($date, 'd/m/Y')) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Function assistant for validateDateBirth
   * @param string $date
   * @param string $format
   * @return bool
   */
  public function validateDate($date, $format) {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
  }

  /**
   * Function to validate shipping
   * @param string $postalCode
   * @param string $street
   * @param string $number
   * @param string $city
   * @param string $state
   */
  public function validateShipping($postalCode, $street, $number, $city, $state) {
    if ($postalCode == '' || $postalCode == ' ') {
      Mage::throwException(__('Shipping postal code was not filled!'));
    } else if ($street == '' || $street == ' ') {
      Mage::throwException(__('Shipping street was not filled!'));
    } else if ($number == '' || $number == ' ') {
      Mage::throwException(__('Shipping number was not filled!'));
    } else if ($city == '' || $city == ' ') {
      Mage::throwException(__('Shipping city was not filled!'));
    } else if ($state == '' || $state == ' ') {
      Mage::throwException(__('Shipping state was not filled!'));
    }
  }

  /**
   * Function to validate billing
   * @param string $postalCode
   * @param string $street
   * @param string $number
   * @param string $city
   * @param string $state
   * @param string $district
   */
  public function validateBilling($postalCode, $street, $number, $city, $state, $district = '0') {
    if ($postalCode == '' || $postalCode == ' ') {
      Mage::throwException(__('Billing postal code was not filled!'));
    } else if ($street == '' || $street == ' ') {
      Mage::throwException(__('Billing street was not filled!'));
    } else if ($number == '' || $number == ' ') {
      Mage::throwException(__('Billing number was not filled!'));
    } else if ($city == '' || $city == ' ') {
      Mage::throwException(__('Billing city was not filled!'));
    } else if ($state == '' || $state == ' ') {
      Mage::throwException(__('Billing state was not filled!'));
    } else if ($district == '' || $district == ' ') {
      Mage::throwException(__('Billing district was not filled!'));
    }    
  }

  /**
   * Function to validate client informations
   * @param string $name
   * @param string $email
   * @param string $phone
   */
  public function validateClientData($name, $email, $phone) {
    if ($name == '' || $name == ' ') {
      Mage::throwException(__('Customer name was not filled!'));
    }
    $result = strstr($email, '@');
    if ($result == false) {
      Mage::throwException(__('Customer email not filled in or invalid!'));
    }
    if (!strlen($phone) >= 9) {
      Mage::throwException(__('Customer cellphone not filled in or invalid!'));
    }
  }
}
