<?php

namespace wms\transaction;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='transaction';

    public static function toDataTable($params){

        $where = array();

        if (!empty($params['from'])){
            $where[] = array(sql::DATE('T1.transaction_date'),'>=',$params['from']);
        } else {

            $strStartDate = date('Y-m-d 00:00:00');
            $strNewDate = date('Y-m-d 00:00:00', strtotime('-1 day', strtotime($strStartDate)));

            $where[] = array(sql::DATE('T1.transaction_date'),'>=',$strNewDate);
        }

        if (!empty($params['to'])){
            $where[] = array(sql::DATE('T1.transaction_date'),'<=',$params['to']);
        } else {
            $where[] = array(sql::DATE('T1.transaction_date'),'<=',date('Y-m-d 23:59:59'));
        }

        return static::createQuery()
        ->select('T1.id','T1.transaction_date','T1.transaction_type','T6.container','T6.case_number'
        ,'T1.serial_number','T2.material_number','T2.material_name_en','T1.quantity','T4.unit','T7.location_code from_location'
        ,'T3.location_code','T8.sale_order','T9.location_code pallet','T9.truck_id','T9.truck_date','T5.name')
        ->from('transaction T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->join('unit T4','LEFT',array('T2.unit','T4.id'))
        ->join('user T5','LEFT',array('T1.created_by','T5.id'))
        ->join('packing_list T6','LEFT',array('T1.reference','T6.id'))
        ->join('location T7','LEFT',array('T1.from_location','T7.id'))
        ->join('sale_order_status T8','LEFT',array('T1.sale_id','T8.id'))
        ->join('pallet_log T9','LEFT',array('T1.pallet_id','T9.id'))
        ->where($where)
        ->order('T1.transaction_date');
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
                // id ที่ส่งมา
                if ($action ==='addlocation'){

                    $index = \wms\location\Model::get($request->post('id')->toInt());

                    $ret['modal'] = Language::trans(\wms\locations\View::create()->render($index,$login));
                    
                } elseif ($action == 'export') {
                    $params = $request->getParsedBody();
                    $params['module'] = 'wms-export';
                    $ret['location'] = WEB_URL.'export.php?'.http_build_query($params).'&type=transaction&amp;';
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
    
                        } elseif ($action ==='statusd'){
    
                            $index = \wms\location\Model::get($request->post('id')->toInt());
    
                            $ret['modal'] = Language::trans(\wms\locations\View::create()->render($index,$login));
    
                        } elseif ($action ==='Active'){
                            $update_data['active'] = 1;
                            $db->update($this->getTableName('location'), array('id', $match[1]), $update_data);
                            $ret['location'] = 'reload';

                        } elseif ($action ==='Non Active'){
                            $update_data['active'] = 0;
                            
                            $db->update($this->getTableName('location'), array('id', $match[1]), $update_data);
                            $ret['location'] = 'reload';
                        }
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