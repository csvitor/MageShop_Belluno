<?php

class MageShop_Belluno_Block_Form_Creditcard extends Mage_Payment_Block_Form
{
    protected $_helper;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mageshop/belluno/form/creditcard.phtml');
        $this->_helper = Mage::Helper("belluno/data");
    }

    protected function _getConfig()
    {
      return Mage::getSingleton('payment/config');
    }
  
    /**
     * Function to get cc valid months
     */
    public function getCcMonths()
    {
      $months = $this->getData('cc_months');
      if (is_null($months)) {
        $months[0] = "Month";
        $months = array_merge($months, $this->_getConfig()->getMonths());
        $this->setData('cc_months', $months);
      }
      return $months;
    }
  
    /**
     * Function to get cc valid years
     */
    public function getCcYears()
    {
      $years = $this->getData('cc_years');
      if ($years == null) {
        $years[0] = "Year";
        $years = $this->_helper->getYears();
        $years = array(0=>$this->__('Year'))+$years;
        $this->setData('cc_years', $years);
      }
      return $years;
    }
      /**
     * Function to get token
     */
    public function getFullToken()
    {
      return $this->_helper->getToken();
    }
      /**
     * Function to get token
     */
    public function getServiceUrl()
    {
        return $this->_helper->getUrlEnvironment();
    }
  
    /**
     * Function to get installments
     * @param mixed $total
     */
    public function getInstallments($total)
    {
      $maxInstallments = Mage::getStoreConfig('payment/belluno_creditcard/installments');
      $minValueInstalment = Mage::getStoreConfig('payment/belluno_creditcard/min_installment');
      $dataInterest = Mage::getStoreConfig('payment/belluno_creditcard/installment_interest');
      $dataInterest = unserialize($dataInterest);
      foreach ($dataInterest as $key => $value) {
        $installmentInterest[] = $value['from_qty'];
      }
      
      $arrayInstallments[1] = $this->_helper->__("R$ %0.2f à vista", $total);
      if($minValueInstalment > $total){
        return $arrayInstallments;
      }
  
      for ($i = 0; $i < $maxInstallments; $i++) {
        $valuePortion = ($total / ($i + 1));
        $valuePortion2f = number_format($valuePortion, 2, ",", ".");
        $times = $i + 1;
        if (($i + 1) == 1) {
          if ($installmentInterest[0] == 0 || $installmentInterest[0] == null) {
            $arrayInstallments[$times] = "1x de R$$valuePortion2f sem juros";
          } else {
            $interest = $valuePortion * ($installmentInterest[0] / 100);
            $interest = number_format($interest, 2);
            $valuePortion = $valuePortion + $interest;
            $valuePortion2f = number_format($valuePortion, 2, ",", ".");
            $totalInterest = $interest * ($i + 1);
            $totalInterest = number_format($totalInterest, 2);
            $arrayInstallments[$times] = "1x de R$$valuePortion2f com juros total de R$$totalInterest";
          }
        } else if (isset($installmentInterest[$i]) && $installmentInterest[$i] != 0 && $installmentInterest[$i] != null) {
          $valuePortion = ($total / ($i + 1));
          if ($valuePortion < $minValueInstalment) {
            break;
          }
          $interest = $valuePortion * ($installmentInterest[$i] / 100);
          $interest = number_format($interest, 2);
          $valuePortion = $valuePortion + $interest;
          $valuePortion2f = number_format($valuePortion, 2, ",", ".");
          $totalInterest = $interest * ($i + 1);
          $totalInterest = number_format($totalInterest, 2);
          $arrayInstallments[$times] = "$times" . "x de R$$valuePortion2f com juros total de R$$totalInterest";
          
        } else {
          if ($valuePortion < $minValueInstalment) {
            break;
          }
          $arrayInstallments[$times] = "$times" . "x de R$$valuePortion2f sem juros";
        }
      }
      return $arrayInstallments;
    }
  
    /**
     * Function to get image type brands
     */
    public function getBrandImages()
    {
      $array = [
        'mastercard' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/mastercard.png',
        'visa' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/visa.png',
        'elo' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/elo.png',
        'hipercard' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/hipercard.png',
        'cabal' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/cabal.png',
        'hiper' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/hiper.png',
        'amex' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/amex.png',
        'bancodobrasil' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/bancodobrasil.png',
        'itau' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/itau.png',
        'hipercard1' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/hipercard1.png',
        'diners' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/diners.png',
        'bradesco' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/bradesco.png',
        'banrisul' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mageshop/belluno/images/banrisul.png',
      ];

      return $array;
    }
  
    /**
     * Function to get pub key konduto
     */
    public function getPubKeyKonduto()
    {
      return $this->_helper->getKeyKonduto();
    }
  
    public function getFieldCaptureTax()
    {
      $captureTax = Mage::getStoreConfig('payment/belluno_creditcard/capture_tax');
      if ($captureTax == true) {
        return true;
      } else {
        return false;
      }
    }
    
}
