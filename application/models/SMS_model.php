<?php

class SMS_model extends CI_Model{
    
    private $auth = array("key" => "authentication", "value" => array(
                        array("key" => "username", "value" => "ideamedia"),
                        array("key" => "password", "value" => "ugur123")
                    ));

    public $paramHeader = array(
        "sendsms" => "SMS"
        );
    
    public $home = "http://websms.telsam.com.tr/xmlapi/";
    
    function setMessageParam($text){
        return array("key" => "message", "value" => array(
                    array("key" => "originator", "value" => "IDEAMEDIA"),
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
            curl_setopt($ch, CURLOPT_URL, "http://websms.telsam.com.tr/xmlapi/sendsms"); 
            curl_setopt($ch, CURLOPT_POST, 1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->convertParams($endpoint, $params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            if($response == false){
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            return $response;  
        } catch (Exception $e) {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
        }
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
        // foreach($params as $key => $value) {
        //     if(is_array($value)) {
        //         if(!is_numeric($key)){
        //             $subnode = $xml->addChild($key);
        //             $this->arrayToXML($value, $subnode);
        //         } else {
        //             $this->arrayToXML($value, $xml);
        //         }
        //     } else {
        //         $xml->addChild($key,$value);
        //     }
        // }
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
            // $query = $this->db->get_where("userinfo", array("telefon" => $gsm));
            // $result = $query->result(); 
            // if(count($result) == 0){
            //     throw new Exception("Noone found");
            // }
            $text = rand(100000, 999999);
            $receivers = array($gsm);
            $params = array($this->auth, $this->setMessageParam($text), $this->setReceiversParam($receivers));
            $result = $this->sendSMS($params);
            var_dump($result);
            
        } catch (Exception $e) {
            die($e->getMessage());
        }
        
        return $result;
    }

    
}

?>