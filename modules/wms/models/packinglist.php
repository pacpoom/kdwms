<?php

namespace wms\packinglist;

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
            $where[] = array(sql::DATE('T1.created_at'),'>=',$params['from']);
        } else {

            $strStartDate =date('Y-m-d');
            $strNewDate = date('Y-m-d', strtotime('-7 day', strtotime($strStartDate)));

            $where[] = array(sql::DATE('T1.created_at'),'>=',$strNewDate);

        }

        if ($params['to'] != ''){
            $where[] = array(sql::DATE('T1.created_at'),'<=',$params['to']);
        } else {
            $where[] = array(sql::DATE('T1.created_at'),'<=',date('Y-m-d'));
        }

        if ($params['status'] == 1) {
            $where[] = array('receive_flg',1);
        } elseif ($params['status'] == 2) {
            $where[] = array('receive_flg',0);
        } elseif ($params['status'] == 3) {
            $where[] = array('receive_flg',2);
        }

        if ($params['container'] != ''){
            $where[] = array('container',$params['container']);
        }


        return static::createQuery()
        ->select('T1.id','T1.container','T1.storage_location','T1.container_received','T1.case_number','T1.box_id',
        'T2.material_number','T2.material_name_en','T1.quantity','T3.unit','T1.receive_flg')
        ->from('packing_list T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('unit T3','LEFT',array('T3.id','T2.unit'))
        ->where($where)
        ->order('T1.container');
    }

    public static function sumMaterial($id){
        return static::createQuery()
        ->select(sql::COUNT('material_id','qty'))
        ->from('packing_list')
        ->where(array('container',$id))
        ->groupBy('material_id')
        ->execute();
    }

    public static function SumCase($id){
        return static::createQuery()
        ->select(sql::COUNT('case_number','qty'))
        ->from('packing_list')
        ->where(array('container',$id))
        ->groupBy('case_number')
        ->execute();
    }

    public static function SumBox($id){
        return static::createQuery()
        ->select(sql::COUNT('box_id','qty'))
        ->from('packing_list')
        ->where(array('container',$id))
        ->execute();
    }

    public static function SumQty($id){
        return static::createQuery()
        ->select(sql::SUM('quantity','qty'))
        ->from('packing_list')
        ->where(array('container',$id))
        ->execute();
    }

    public static function checkContainerSum(){
        return static::createQuery()
        ->select('container')
        ->from('packing_list')
        ->where(array('check_flg',0))
        ->groupBy('container')
        ->execute();
    }

    public static function checkContainer($id){
        return static::createQuery()
        ->select('container')
        ->from('container')
        ->where(array('container',$id))
        ->execute();
    }

    public static function get_ccl($id){
        return static::createQuery()
        ->select('id','declaration_no','date_transmit','item_no','invoice_no','T2.material_number','T2.material_name_en')
        ->from('declaration T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->where(array('T1.id',$id))
        ->first();
    }

    public static function getMat($id){

        return static::createQuery()
        ->select('id','material_number')
        ->from('material')
        ->where(array('material_number',$id))
        ->execute();
    }

    public static function getPL($id){
        return static::createQuery()
        ->select('id','container')
        ->from('packing_list')
        ->where(array('box_id',$id))
        ->execute();
    }

    public static function getdetail($id){
        return static::createQuery()
        ->select('T1.id','T1.container','T1.case_number','T1.box_id','T1.temp_material','T2.material_name_en','T1.quantity')
        ->from('packing_list T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->where(array('T1.id',$id))
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
                     
                            $this->header = \wms\csv\Model::packing_list();

                            \Kotchasan\Csv::read(
                                $file->getTempFileName(),
                                array($this, 'importPL'),
                                $this->header,
                                self::$cfg->csv_language
                            );

                            $Get_container = \wms\packinglist\Model::checkContainerSum();

                            foreach ($Get_container as $item){

                                $check_container = \wms\packinglist\Model::checkContainer($item->container);

                                if ($check_container == false) {

                                    $get_material = \wms\packinglist\Model::sumMaterial($item->container);
                                    $get_case = \wms\packinglist\Model::SumCase($item->container);
                                    $get_box = \wms\packinglist\Model::SumBox($item->container);
                                    $get_Qty = \wms\packinglist\Model::SumQty($item->container);

                                    $insert_container = array(
                                        'id' => NULL,
                                        'container' => $item->container,
                                        'status' => 0,
                                        'total_material' => isset($get_material[0]->qty) ? $get_material[0]->qty : 0,
                                        'total_case' => isset($get_case[0]->qty) ? $get_case[0]->qty : 0,
                                        'total_box' => isset($get_box[0]->qty) ? $get_box[0]->qty : 0,
                                        'total_quantity' => isset($get_Qty[0]->qty) ? $get_Qty[0]->qty : 0,
                                        'receive_material' => 0,
                                        'receive_case' => 0,
                                        'receive_box' => 0,
                                        'receive_quantity' => 0,
                                        'total_short' => 0,
                                        'created_at' => date('Y-m-d'),
                                        'created_by' => $this->login['id']
                                    );

                                    $this->db()->insert($this->getTableName('container'),$insert_container);
                                    
                                    $update_container = array(
                                        'check_flg' => 1
                                    );

                                    $this->db()->update($table,array('container',$item->container),$update_container);

                                }
                            }

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

        $getId = \wms\packinglist\Model::getMat($data[$this->header[3]]);

        if ($getId == true) {

            $check = \wms\packinglist\Model::getPL($data[$this->header[2]]);

            if ($check == false) {

                $insert_pl = array(
                    'id' => NULL,
                    'container' => $data[$this->header[0]],
                    'material_id' => $getId[0]->id,
                    'case_number' => $data[$this->header[1]],
                    'box_id' => $data[$this->header[2]],
                    'quantity' => $data[$this->header[5]],
                    'check_flg' => 0,
                    'receive_flg' => 0,
                    'created_at' => date('Y-m-d'),
                    'created_by' => $this->login['id']
                );

                $this->db()->insert($this->getTableName('packing_list'),$insert_pl);
                ++$this->row;
            }
           
        } else {

            $insert_mat = array(
                'id' => NULL,
                'material_number' => $data[$this->header[3]],
                'material_name_en' => $data[$this->header[4]],
                'material_name_thai' => '',
                'unit' => 1,
                'net_weight' => 0,
                'material_type' => 1,
                'unit_price' => 0,
                'currency' => 1,
                'active' => 1,
                'created_at' => date('Y-m-d'),
                'created_by' => $this->login['id']
            );

            $this->db()->insert($this->getTableName('material'),$insert_mat);

            $material_id = \wms\packinglist\Model::getMat($data[$this->header[3]]);

            $check = \wms\packinglist\Model::getPL($data[$this->header[2]]);

            if ($check == false) {

                $insert_pl = array(
                    'id' => NULL,
                    'container' => $data[$this->header[0]],
                    'material_id' => $material_id[0]->id,
                    'case_number' => $data[$this->header[1]],
                    'box_id' => $data[$this->header[2]],
                    'quantity' => $data[$this->header[5]],
                    'check_flg' => 0,
                    'receive_flg' => 0,
                    'created_at' => date('Y-m-d'),
                    'created_by' => $this->login['id']
                );

                $this->db()->insert($this->getTableName('packing_list'),$insert_pl);
                ++$this->row;
            }
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