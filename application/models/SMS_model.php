<?php

class SMS_model extends CI_Model{
    
    private $auth = array("key" => "authentication", "value" => array(
                        array("key" => "username", "value" => "sinema"),
                        array("key" => "password", "value" => "SinemaTv2015")
                    ));

    public $paramHeader = array(
        "sendsms" => "SMS"
        );
    
    public $home = "http://websms.telsam.com.tr/xmlapi/";
    
    private $sms_error_code_grid = array(
        "AUTH_FAILED" => array( "code" => 500, "statusText" => "Hatalı kullanıcı adı veya şifre."),
        "USER_DENIED" => array( "code" => 404, "statusText" => "Erişim engellendi."),
        "XML_ERROR" => array( "code" => 404, "statusText" => "XML Post parametresi boş veya geçersiz."),
    );

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
            //Handling CURL errors
            $response = array("status"=>"ERROR", "error_code"=> $e->getCode(), "error_description"=>$e->getMessage());
        }
        return $response;
    }
    
    function sendSMS($params){
        try{
            $result = $this->request("sendsms", $params);
            if($result->error_code){
                $error = $this->sms_error_code_grid[(string) $result->error_code];
                throw new Exception($error["statusText"], $error["code"]);
            }
        } catch(Exception $e) {
            //Handling SMS API errors
            $this->output->set_status_header($e->getCode() , $this->toUTF8("SMS API: ".$e->getMessage()));
            exit();
        }
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
    
    function toUTF8($text){
        return mb_convert_encoding($text, "HTML-ENTITIES", "UTF-8");
    }

    function prepareMessageText($secret){
        $text = "Değerli üyemiz, ".$secret." nolu cep şifresini giriş için kullanınız.";
        return $text;
    }

    function sendAuthKey($gsm){
        try {
            $query = $this->db->get_where(UYE_TABLO_ISMI, array("telefon" => $gsm));
            $result = $query->result(); 
            if(!$result){
                $queryForDev = $this->db->get_where(DEV_TABLO_ISMI, array("telefon" => $gsm));
                $result = $queryForDev->result();
                if(!$result){
                    $error = $this->db->error();
                    if($error["code"] == 0){
                        $error = array( "message" => "İlgili kayıt bulunamadı.", "code" => 404);
                    }
                    throw new Exception($error['message'], $error['code']);
                } else {
                    $secret = $result[0]->secret;
                    if($result[0]->sendSMS){
                        $receivers = array($gsm);
                        $params = array($this->auth, $this->setMessageParam($this->prepareMessageText($secret)), $this->setReceiversParam($receivers));
                        $this->sendSMS($params);
                    }
                }
            } else {
                $secret = $this->googleauthenticator->createSecret();
                $oneCode = $this->googleauthenticator->getCode($secret);
                $receivers = array($gsm);
                $params = array($this->auth, $this->setMessageParam($this->prepareMessageText($oneCode)), $this->setReceiversParam($receivers));
                $this->sendSMS($params);
            }
            $result = array("secret"=>$secret);
            return $result;
        } catch (Exception $e) {
            $this->output->set_status_header($e->getCode(), $this->toUTF8( $e->getMessage() ));
            exit();
        }
    }

    function checkOneKey($key, $secret, $gsm){
        try{
            if($secret == null){
                throw new Exception('Secret Not Found');
            }
            $query = $this->db->get_where(UYE_TABLO_ISMI, array("telefon" => $gsm));
            $result = $query->result();
            if(!$result){
                $query = $this->db->get_where(DEV_TABLO_ISMI, array("telefon" => $gsm));
                $result = $query->result();
                if(!$result){
                    throw new Exception($this->db->_error_message(), $this->db->_error_number());
                } else {
                    if($secret == $result[0]->secret) $checkResult = true;
                }
            } else {
                $checkResult = $this->googleauthenticator->verifyCode($secret, $key, 2);
            } 
            if(!$checkResult) throw new Exception("Yanlış Şifre", 500);
            return $result[0];
        } catch (Exception $e){
            $this->output->set_status_header($e->getCode()!= 0 ?: 404, 
                                                $this->toUTF8( $e->getMessage() != "" ?: "İlgili kayıt bulunamadı." ));
        }
    }

    
}

?>