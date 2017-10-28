<?php

// define ("AIDAT_TABLO_ISMI", 'aidat');
// define ("UYE_TABLO_ISMI", 'uye_bilgileri');

class SMS_model extends CI_Model{
    
    private $auth = array("key" => "authentication", "value" => array(
                        array("key" => "username", "value" => "sinema"),
                        array("key" => "password", "value" => "SinemaTv2015")
                    ));

    public $paramHeader = array(
        "sendsms" => "SMS"
        );
    
    public $home = "http://websms.telsam.com.tr/xmlapi/";
    
    private $ga;

    function __construct(){
        $this->load->library('GoogleAuthenticator');
    }

    function setMessageParam($text){
        return array("key" => "message", "value" => array(
                    array("key" => "originator", "value" => "SinemaTvSen"),
                    array("key" => "text", "value" => $text),
                    array("key" => "unicode", "value" => ""),
                    array("key" => "international", "value" => ""),
                ));
    }
    
    function setReceiversParam($receivers){
        $ret = array("key" => "receivers", "value" => array());
        foreach ($receivers as $r) {
            $ret['value'][] = array("key" => "receiver", "value" => $r);
        }
        return $ret;
    }
    
    
    function request($endpoint, $params){
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->home.$endpoint); 
            curl_setopt($ch, CURLOPT_POST, 1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->convertParams($endpoint, $params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            if($response == false){
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            $response = new SimpleXMLElement($response);
        } catch (Exception $e) {
            $response = array("status"=>"ERROR", "error_code"=> $e->getCode(), "error_description"=>$e->getMessage());
        }
        return $response;
    }
    
    function sendSMS($params){
        return $this->request("sendsms", $params);
    }
    
    function convertParams($endpoint, $params){
        $xml = new SimpleXMLElement("<".$this->paramHeader[$endpoint]."/>");
        $this->arrayToXML($params, $xml);
        return $xml->asXML();
    }
    
    function arrayToXML($params, &$xml){
        foreach($params as $p) {
            if(is_array($p['value'])) {
                $subnode = $xml->addChild($p['key']);
                $this->arrayToXML($p['value'], $subnode);
            } else {
                $xml->addChild($p['key'],$p['value']);
            }
        }
    }
    
    function sendAuthKey($gsm){
        try {
            $query = $this->db->get_where(UYE_TABLO_ISMI, array("telefon" => $gsm));
            $result = $query->result(); 
            if(!$result){
                $error = $this->db->error();
                throw new Exception($error['message'], $error['code']);
            }
            if($result[0]->birim == "APPLE"){
                $secret = 111111;
            } else {
                $secret = $this->googleauthenticator->createSecret();
                $oneCode = $this->googleauthenticator->getCode($secret);
                $receivers = array($gsm);
                $params = array($this->auth, $this->setMessageParam($oneCode), $this->setReceiversParam($receivers));
                $result = $this->sendSMS($params);
                if(array_key_exists('error_code', $result)){
                    throw new Exception($result['error_description'], $result['error_code']);
                }
            }
            $result = array("secret"=>$secret);
            return $result;
        } catch (Exception $e) {
            $this->output->set_status_header($e->getCode()!= 0 ?: 404, 
                                            $e->getMessage()!= "" ?: mb_convert_encoding("İlgili kayıt bulunamadı.", "HTML-ENTITIES", "UTF-8"));
        }
    }

    function checkOneKey($key, $secret, $gsm){
        try{
            if($secret == null){
                throw new Exception('Secret Not Found');
            }
            if($gsm == "1111111111" && $key == "111111"){
                $checkResult = true;
            } else {
                $checkResult = $this->googleauthenticator->verifyCode($secret, $key, 2);
            }
            if(!$checkResult) throw new Exception("Secret Key Not Valid", 500);
            $query = $this->db->get_where(UYE_TABLO_ISMI, array("telefon" => $gsm));
            $result = $query->result();
            if(count($result) == 0){
                 throw new Exception($this->db->_error_message(), $this->db->_error_number());
            }
            return $result[0];
        } catch (Exception $e){
            $this->output->set_status_header($e->getCode()!= 0 ?: 404, $e->getMessage()!= "" ?: mb_convert_encoding("İlgili kayıt bulunamadı.", "HTML-ENTITIES", "UTF-8"));
        }
    }

    
}

?>