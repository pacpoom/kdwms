<?php

namespace wms\inventory;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='location';

    public static function toDataTable($params){
        $params = array();
        return static::createQuery()
        ->select('T1.id','T3.container','T3.case_number','T1.serial_number','T2.material_number','T2.material_name_en',
        'T2.material_type','T1.actual_quantity','T5.unit','T1.inbound_date','T4.location_code')
        ->from('inventory_stock T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('packing_list T3','LEFT',array('T1.reference','T3.id'))
        ->join('location T4','LEFT',array('T1.location_id','T4.id'))
        ->join('unit T5','LEFT',array('T2.unit','T5.id'))
        ->where(array('T1.actual_quantity','!=',0))
        ->order('id');
    }

    public static function getdetail($id){
        return static::createQuery()
        ->select('T1.id','T3.container','T3.case_number','T3.box_id','T3.temp_material','T2.material_name_en','T1.actual_quantity')
        ->from('inventory_stock T1')
        ->join('packing_list T3','LEFT',array('T1.reference','T3.id'))
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->where(array('T1.id',$id))
        ->execute();
    }

    public static function get($id){
        
        $id = trim($id);
        if ($id == '') {
            return 0;
        } else {
            $obj = new static();
            // Model
            $model = new \Kotchasan\Model;
            // Database
            $db = $model->db();
            // table
            $table = $model->getTableName($obj->table);
            // ตรวจสอบรายการที่มีอยู่แล้ว
            $search = $db->first($table, array(
                array('id', $id)
            ));

            return $search;
        }

    }

    
    public function action(Request $request)
    {
        $ret = array();
        // session, referer, can_manage_inventory, ไม่ใช่สมาชิกตัวอย่าง
        
        if ($request->initSession() && $request->isReferer() &&  $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_manage_inventory')) {
                // รับค่าจากการ POST
                $action = $request->post('action')->toString(); 
                // Database
                $db = $this->db();
                $db->query("TRUNCATE TABLE label_id;");
                // id ที่ส่งมา
                if ($action ==='addlocation'){

                    $index = \wms\location\Model::get($request->post('id')->toInt());

                    $ret['modal'] = Language::trans(\wms\locations\View::create()->render($index,$login));
                    
                } elseif ($action ==='export') {
                    $params = $request->getParsedBody();
                    $params['module'] = 'wms-export';
                    $ret['location'] = WEB_URL.'export.php?'.http_build_query($params).'&type=stock&amp;';
                } else{
                    if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->filter('0-9,'), $match)) {
                       
                        if ($action === 'delete') {

                            //var_dump('delete');
                            // ลบ
                            $db->delete($this->getTableName('location'), array('id', $match[1]), 0); 
                            // log
                            \Index\Log\Model::add(0, 'location', 'Delete', '{LNG_Delete} {LNG_Inventory} ID : '.implode(', ', $match[1]), $login['id']);
                            // reload
                            $ret['location'] = 'reload';
    
                        } elseif ($action ==='export'){
    
                            $params = $request->getParsedBody();
                            $params['module'] = 'wms-export';
                            $ret['location'] = WEB_URL.'export.php?'.http_build_query($params).'&type=stock&amp;';
    
                        } elseif ($action ==='print'){
    
                            foreach ($match[1] As $row){

                                $detail = \wms\inventory\Model::getdetail($row);                 
            
                                if ($detail == true) {

                                    $container = isset($detail[0]->container) ? $detail[0]->container : '';
                                    $case_number = isset($detail[0]->case_number) ? $detail[0]->case_number : '';
                                    $box_id = isset($detail[0]->box_id) ? $detail[0]->box_id : '';
                                    $temp_material = isset($detail[0]->temp_material) ? $detail[0]->temp_material : '';
                                    $material_name_en = isset($detail[0]->material_name_en) ? $detail[0]->material_name_en : '';
                                    $actual_quantity = isset($detail[0]->actual_quantity) ? $detail[0]->actual_quantity : 0;

                                    $insert = array(
                                        'id' => NULL,
                                        'container' => $container,
                                        'case_no' => $case_number,
                                        'box_id' => $box_id,
                                        'material' => $temp_material,
                                        'material_name' => $material_name_en,
                                        'qty' => $actual_quantity,
                                        'qr_code' => '0010000475_'.$temp_material.'_B060501_'.$actual_quantity.'_'.$box_id.'_A100',
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