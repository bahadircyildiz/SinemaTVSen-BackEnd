<?php


class ExcelHandler extends CI_Controller{
    function index($params = null){
        if($params == null) $this->load->view("options_view");
        else $this->load->view("options_view", $params);
    }

    function parse_excel(){
        $params = $_FILES['spreadsheet'];
        $this->load->library('PHPExcelHelper', $params);
        $obj = $this->phpexcelhelper->objectify();
        $userinfo = array(); $payments = array();
        // $this->ExcelHandler_model->add_alias_list($query);
        $cnt = array('userinfo' => 0, 'payments' => 0);
        $response = array('status' => 200, 'message' => 'OK');
        try {
            foreach ($obj->response['userinfo'] as $e) {
                $sql = $this->db->insert_string('userinfo', $e) . ' ON DUPLICATE KEY UPDATE uye_no=uye_no, odeme_tipi=VALUES(odeme_tipi), birim=VALUES(birim), adi=VALUES(adi), soyadi=VALUES(soyadi), telefon=VALUES(telefon), email=VALUES(email), tutar=VALUES(tutar)';
                $result = $this->db->query($sql);
                if($result) {
                    // array_push($userinfo, $this->db->insert_id() + $cnt['userinfo']);
                    array_push($userinfo, $e);
                    $cnt['userinfo']++;
                } else throw new Exception($this->db->_error_message);
            }
            foreach ($obj->response['payments'] as $e) {
                $sql = $this->db->insert_string('payments', $e) . ' ON DUPLICATE KEY UPDATE id=id, uye_no=uye_no, odeme_tipi=VALUES(odeme_tipi), odendigi_tarih=VALUES(odendigi_tarih)';
                $result = $this->db->query($sql);
                if($result) {
                    // array_push($payments, $this->db->insert_id() + $cnt['payments']);
                    if(array_key_exists("aidat_tarihi", $e)){
                        $aidat_tarihi = $this->ExcelHandler_model->stringDateParser($e['aidat_tarihi']);
                        unset($e['aidat_tarihi']);
                        $e['aidat_yili'] = $aidat_tarihi['year'];
                        $e['aidat_ayi'] = $aidat_tarihi['month'];
                    }
                    if(array_key_exists("odendigi_tarih", $e)){
                        $odendigi_tarih = $this->ExcelHandler_model->stringDateParser($e['odendigi_tarih']);
                        unset($e['odendigi_tarih']);
                        $e['odendigi_ay'] = $odendigi_tarih['month'];
                    }
                    array_push($payments, $e);
                    $cnt['payments']++;
                } else throw new Exception($this->db->_error_message);
            }
            $response = array('InsertedUsers' => $userinfo, 'InsertedPayments' => $payments);

        } catch (Exception $e ) {
            $response = $e->getMessage();
        }

        // $users = $this->db->insert_batch('userinfo', $obj->response['content']['userinfo']);
        // // $payments = $this->db->insert_batch('payments', $obj->response['content']['payments']);

        $this->index(array( 'data' => $response));
    }

    function get_payments(){
        $param = $this->input->post('uye_no');
        $data = $this->ExcelHandler_model->getPaymentsByUyeNo($param);
        $this->index(array( 'data' => $data));
    }

    function get_user_data(){
        $param = $this->input->post('uye_no');
        $data = $this->ExcelHandler_model->getUserByUyeNo($param);
        $this->index(array( 'data' => $data));
    }

    function get_debt(){
        $param = $this->input->post('uye_no');
        $data = $this->ExcelHandler_model->getDebtsTillToday($param);
        $this->index(array( 'data' => $data));
    }



}


?>
