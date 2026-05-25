<?php

class MageShop_Belluno_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MS_BELLUNO_TOKEN = "payment/mageshop_belluno_custompayment/auth_token";
    const MS_BELLUNO_KEY_KONDUTO = "payment/mageshop_belluno_custompayment/public_key";
    const MS_BELLUNO_KEY_ENV = "payment/mageshop_belluno_custompayment/environment";
    const MS_BELLUNO_BASE_URL = "payment/mageshop_belluno_custompayment/base_url";
    const MS_BELLUNO_SANDBOX = "https://ws-sandbox.bellunopag.com.br";
    const MS_BELLUNO_API = "https://api.belluno.digital";
    const MS_BELLUNO_WEBSOCKET_PIX = "payment/belluno_pix/websocket";
    const MS_BELLUNO_ACTION_LOGS = "payment/mageshop_belluno_custompayment/action_logs";
    public function getToken()
    {
        return Mage::getStoreConfig(self::MS_BELLUNO_TOKEN);
    }
    public function getKeyKonduto()
    {
        return Mage::getStoreConfig(self::MS_BELLUNO_KEY_KONDUTO);
    }
    public function getEnvironment(){
        return Mage::getStoreConfig(self::MS_BELLUNO_KEY_ENV);
    }
    public function getUrlEnvironment()
    {
        if ($this->getEnvironment() == 'sandbox') {
            return self::MS_BELLUNO_SANDBOX;
        } else {
            return self::MS_BELLUNO_API;
        }
    }
    public function getBaseUrl()
    {
        return Mage::getStoreConfig(trim( self::MS_BELLUNO_BASE_URL , '/' ));
    }

    public function getWebsocket()
    {
        return Mage::getStoreConfig(self::MS_BELLUNO_WEBSOCKET_PIX);
    }

    public function getActiveLogs(){
        return Mage::getStoreConfig(self::MS_BELLUNO_ACTION_LOGS);
    }

     /**
     * Nome file log
     *
     * @param string $type
     * @param string $content
     * @param string $file
     */
    public function log($content , $file = 'belluno_log.text.log', $action = null)
    {
        if($this->getActiveLogs() == MageShop_Belluno_Model_Source_Logs::MS_BELLUNO_ACTION_SYSTEM){
            Mage::log($content, null , $file);
        }elseif($this->getActiveLogs() == MageShop_Belluno_Model_Source_Logs::MS_BELLUNO_ACTION_TRANSACTION && $this->getActiveLogs() == $action){
            Mage::log($content, null , $file , true);
        }elseif($this->getActiveLogs() == MageShop_Belluno_Model_Source_Logs::MS_BELLUNO_ACTION_ALL){
            Mage::log($content, null , $file , true);
        }
    }

    /**
     * Retrieve array of available years
     *
     * @return array
     */
    public function getYears()
    {
        $years = array();
        $first = Mage::getSingleton('core/date')->date('y');

        for ($index=0; $index <= 20; $index++) {
            $year = $first + $index;
            $years[$year] = $year;
        }

        return $years;
    }
    
}
