<?php

class User_model extends CI_Model{

    function __construct(){
        parent::__construct();
        $this->load->library("email");
    }
    
    function sendSikayet($data){
        try {
            // $query = $this->db->insert(SIKAYET_TABLO_ISMI, $data);
            // if($query == false){
            //     $error = $this->db->error();
            //     throw new Exception($error['message'], $error['code']);
            // }
            // return $query; 
            $this->email->from(SIKAYET_MAILI, SIKAYET_MAIL_ADI);
            $this->email->to(INFO_MAILI);
            if($data["gizli"]){
                $this->email->subject($data["tipi"]." [Mobil]");
                $this->email->message($data["icerik"]);
            } else {
                $uye = $this->db->get_where(UYE_TABLO_ISMI, array("uye_no" => $data["uye_no"]));
                $this->email->subject($uye["adi"]." ".$uye["soyadi"].": ".$data["tipi"]." [Mobil]");
                $this->email->message($this->generateKnownMemberMessage($data, $uye));
            }
            return $this->email->send();
        } catch( Exception $e ){
            $this->output->set_status_header($e->getCode(), $e->getMessage());
        }
    }

    function generateKnownMemberMessage($data, $uye){
        $text = $data["icerik"]. 
        "
        Üye Bilgileri:
        Adı Soyadı: ".$uye["adi"]." ".$uye["soyadi"]." 
        Üye Numarası: ".$data["uye_no"]."
        Telefon: ".$uye["telefon"];
        return $text;
    }
    
}

?>