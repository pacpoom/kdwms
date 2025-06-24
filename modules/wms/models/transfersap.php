<?php

namespace wms\transfersap;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='declaration';
    private $header = array();
    private $row = 0;
    private $login;

    public static function toDataTable($params){

        $where = array();

        if ($params['from'] != ''){
            $where[] = array(sql::DATE('created_at'),'>=',$params['from']);
        } else {

            $strStartDate =date('Y-m-d');
            $strNewDate = date('Y-m-d', strtotime('-7 day', strtotime($strStartDate)));

            $where[] = array(sql::DATE('created_at'),'>=',$strNewDate);

        }

        if ($params['to'] != ''){
            $where[] = array(sql::DATE('created_at'),'<=',$params['to']);
        } else {
            $where[] = array(sql::DATE('created_at'),'<=',date('Y-m-d'));
        }

        if ($params['material_number'] != ''){
            $where[] = array('material_number',$params['material_number']);
        }


        return static::createQuery()
        ->select('id','material_number','source_location','receive_location','qty','tr_flg','file_name','user_name','created_at')
        ->from('transfer_311')
        ->where($where)
        ->order('material_number');
    }

    public static function getMat($id){

        return static::createQuery()
        ->select('id','material_number')
        ->from('material')
        ->where(array('material_number',$id))
        ->execute();
    }

    public function submit(Request $request){

        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('packing_list');
        $table_container = $model->getTableName('container');
        $header = array();
        if ($request->initSession() && $request->isSafe() && $this->login = Login::isMember()) {
            foreach ($request->getUploadedFiles() as $item => $file){
                if ($file->hasUploadFile()){
                    if (!$file->validFileExt(array('csv'))) {
                    // ชนิดของไฟล์ไม่ถูกต้อง
                    $ret['ret_'.$item] = Language::get('The type of file is invalid');
                    } else {
                        try {
                     
                            $this->header = \wms\csv\Model::transfer_sap();

                            \Kotchasan\Csv::read(
                                $file->getTempFileName(),
                                array($this, 'importPL'),
                                $this->header,
                                self::$cfg->csv_language
                            );

                            $ret['location'] = 'reload';
                            $ret['alert'] = Language::replace('Successfully imported :count items', array(':count' => $this->row));
                        } catch (\Exception $ex) {
                            $ret['ret_'.$item] = $ex->getMessage();
                        }
                    }
                } elseif ($file->hasError()) {
                    // upload Error
                    $ret['ret_'.$item] = $file->getErrorMessage();
                } else {
                    // ไม่ได้เลือกไฟล์
                    $ret['ret_'.$item] = 'Please browse file';
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
    
    public function importPL($data){

        $getId = \wms\transfersap\Model::getMat($data[$this->header['material_number']]);

        if ($getId == true) {

            if ($data[$this->header['source_location']] != '' && $data[$this->header['receive_location']] != '' && $data[$this->header['qty']] != '') {

                $insert = array(
                    'id' => NULL,
                    'material_number' => $data[$this->header['material_number']],
                    'qty' => $data[$this->header['qty']],
                    'source_location' => $data[$this->header['source_location']],
                    'receive_location' => $data[$this->header['receive_location']],
                    'tr_flg' => 0,
                    'file_name' => '',
                    'user_name' => 'KD',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $this->login['id']
                );

                // insert
                $this->db()->insert($this->getTableName('transfer_311'),$insert);
                // เพิ่มแถว
                $this->row++;
            } else {
                // ข้อมูลไม่ครบ
                    $insert = array(
                    'id' => NULL,
                    'material_number' => $data[$this->header['material_number']],
                    'qty' => 0,
                    'source_location' => 0,
                    'receive_location' => 0,
                    'tr_flg' => 2,
                    'file_name' => 'Error Data Incomplete',
                    'user_name' => 'KD',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $this->login['id']
                    );

                $this->db()->insert($this->getTableName('transfer_311'),$insert);
                // เพิ่มแถว
                $this->row++;
            }
        } else {
            // ไม่พบรหัสวัสดุ
                // ข้อมูลไม่ครบ
                    $insert = array(
                    'id' => NULL,
                    'material_number' => $data[$this->header['material_number']],
                    'qty' => 0,
                    'source_location' => 0,
                    'receive_location' => 0,
                    'tr_flg' => 2,
                    'file_name' => 'Error Material Number Not Found',
                    'user_name' => 'KD',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $this->login['id']
                    );

                $this->db()->insert($this->getTableName('transfer_311'),$insert);
                // เพิ่มแถว
                $this->row++;
        }
    }

    public static function CheckPackinglist($id){
        return static::createQuery()
        ->select('id','receive_flg')
        ->from('packing_list')
        ->where(array('id',$id))
        ->execute();
    }

    public function action(Request $request)
    {
        $ret = array();
        // session, referer, can_manage_inventory, ไม่ใช่สมาชิกตัวอย่าง
        
        if ($request->initSession() && $request->isReferer() &&  $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_manage_inventory')) {
                // รับค่าจากการ POST
                $action = $request->post('action')->toString(); 
                $db = $this->db();
                $db->query("TRUNCATE TABLE label_id;");
                // Database
             
                // id ที่ส่งมา
                if ($action ==='add'){

                    $index = \wms\declaration\Model::get_ccl($request->post('id')->toInt());
                    
                    $ret['modal'] = Language::trans(\wms\declarations\View::create()->render($index,$login));
                    
                } elseif ($action ==='export') {
                    $params = $request->getParsedBody();
                    $params['module'] = 'wms-export';
                    $ret['location'] = WEB_URL.'export.php?'.http_build_query($params).'&type=packing&amp;';
                } else {

                    if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->filter('0-9,'), $match)) {
                       
                        if ($action === 'delete') {
                            // ลบ
                            $db->delete($this->getTableName('packing_list'), array(array('id', $match[1]),array('receive_flg',0)), 0); 
                            // log
                            \Index\Log\Model::add(0, 'packing_list', 'Delete', '{LNG_Delete} {LNG_Packing List Report} ID : '.implode(', ', $match[1]), $login['id']);
                            // reload
                            $ret['location'] = 'reload';
    
                        } elseif ($action ==='short'){
                            // update short ship                        
                            $update_short = array(
                                'receive_flg' => 2
                            );

                            $db->update($this->getTableName('packing_list'), array(array('id', $match[1]),array('receive_flg',0)), $update_short); 
                            // log
                            \Index\Log\Model::add(0, 'packing_list', 'Update', '{LNG_Update} {LNG_Packing List Report} ID : '.implode(', ', $match[1]), $login['id']);
                            // reload
                            $ret['location'] = 'reload';

                        } elseif ($action ==='Non Active'){

                            $update_data['active'] = 0;
                            
                            $db->update($this->getTableName('material'), array('id', $match[1]), $update_data);
                            $ret['location'] = 'reload';

                        } elseif ($action ==='print') {

                            foreach ($match[1] As $row){

                                $detail = \wms\packinglist\Model::getdetail($row);

                                if ($detail == true) {

                                    $insert = array(
                                        'id' => NULL,
                                        'container' => $detail[0]->container,
                                        'case_no' => $detail[0]->case_number,
                                        'box_id' => $detail[0]->box_id,
                                        'material' => $detail[0]->temp_material,
                                        'material_name' => $detail[0]->material_name_en,
                                        'qty' => $detail[0]->quantity,
                                        'qr_code' => '0010000475_'.$detail[0]->temp_material.'_B060501_'.$detail[0]->quantity.'_'.$detail[0]->box_id.'_A100',
                                        'delivery_date' => date('Y-m-d')
                                    );
    
                                    $db->insert($this->getTableName('label_id'),$insert);
                                  
                                }
                            }
                        }
                    }

                    if ($action ==='print') {
                        $ret['alert'] = Language::get('Saved successfully');
                        $ret['open'] = 'https://sail.anjinyk.co.th/pdf/kd-label';
                    }

                }

            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }
}