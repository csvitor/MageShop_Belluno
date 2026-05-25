<?php

class MageShop_Belluno_Service_ApiBelluno
{
    private $resBelluno = [];
    public $helper;

    public function __construct()
    {
        $this->helper = Mage::helper("belluno");
    }

    /**
     * Function to do request
     * @param string $dataRequest
     * @param string $method
     * @param string $uri
     * @return string
     */
    public function doRequest($dataRequest, $method, $uri)
    {
        $token = $this->helper->getToken();
        $url = $this->helper->getUrlEnvironment() . $uri;
        $this->setLogBeelluno("URL", $url,'mageshop_belluno.log', true);
        $this->setLogBeelluno("REQUEST BELLUNO", $dataRequest, 'mageshop_belluno.log', true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "$dataRequest");
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer $token"
        ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->setLogBeelluno("RESPONSE BELLUNO", $response, 'mageshop_belluno.log', true);
        $this->setMessege($response);
        if (($httpCode > 200 || $httpCode < 200) &&  $httpCode != 422) {
            Mage::throwException("Algo não ocorreu bem. Por favor verifique suas informações.");
        }
        curl_close($ch);
        json_encode($response);
        return $response;
    }
    public function setLogBeelluno($title, $body, $file){
        $this->helper->log("[-- {$title} --] {$body}", $file, MageShop_Belluno_Model_Source_Logs::MS_BELLUNO_ACTION_TRANSACTION);
    }
    private function setMessege($res){
        $this->resBelluno = json_decode($res, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    public function getMessege(){
        return $this->resBelluno;
    }
}
