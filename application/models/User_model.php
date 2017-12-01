<?php

class User_model extends CI_Model{

    function __construct(){
        parent::__construct();
        $this->load->library("email");
        $config = array("mailtype" => "html");
        $this->email->initialize($config);
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
            // var_dump($data);
            if($data["gizli"] == "true"){
                $subject = $data["tipi"]." [Mobil]";
                $message = $this->load->view("mail_view", array("icerik" => $data["icerik"], "subject" => $subject), true);
            } else {
                if($data["uye_no"] < 0){
                    $uye = $this->db->get_where(DEV_TABLO_ISMI, array("uye_no" => $data["uye_no"]));
                } else {
                    $uye = $this->db->get_where(UYE_TABLO_ISMI, array("uye_no" => $data["uye_no"]));
                }
                $uye = (array) $uye->result()[0];
                $subject = $uye["adi"]." ".$uye["soyadi"].": ".$data["tipi"]." [Mobil App]";
                $message = $this->load->view("mail_view", array("icerik" => $data["icerik"], "subject" => $subject, "uye" => $uye), true);
            }
            $this->email->subject($subject);
            $this->email->message($message);
            return $this->email->send();
        } catch( Exception $e ){
            $this->output->set_status_header($e->getCode(), $e->getMessage());
        }
    }

    function generateKnownMemberMessage($data, $uye){
        $text = $data["icerik"]. 
        "Üye Bilgileri:
        Adı Soyadı: ".$uye["adi"]." ".$uye["soyadi"]." 
        Üye Numarası: ".$data["uye_no"]."
        Telefon: ".$uye["telefon"];
        return $text;
    }
    
}

?>