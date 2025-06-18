<?php

namespace wms\pallets;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='pallet_log';

    public static function toDataTable($params){
        // กำหนดค่าเริ่มต้นของพารามิเตอร์
        $where = array();

        if (!empty($params['from'])) {
            $where[] = array('delivery_date', '>=', $params['from']);
        } else {
            $params['from'] = date('Y-m-d', strtotime('-1 day'));
            $where[] = array('delivery_date', '>=', $params['from']);
        }

        if (!empty($params['to'])) {
            $where[] = array('delivery_date', '<=', $params['to']);
        } else {
            $params['to'] = date('Y-m-d');
            $where[] = array('delivery_date', '<=', $params['to']);
        }

        if (isset($params['sale_order']) && $params['sale_order'] != '') {
            $where[] = array('sale_order', $params['sale_order']);
        }
   
        if (isset($params['customer']) && $params['customer'] != '') {
            $where[] = array('customer', $params['customer']);
        }
   
        return static::createQuery()
        ->select('id','sale_order','delivery_date','customer','location_code','truck_id','truck_flg','truck_date')
        ->from('pallet_log')
        ->where($where)
        ->order('sale_order','customer','location_code');

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
                $db->query("TRUNCATE TABLE pallet_print;");

                // id ที่ส่งมา
                if ($action ==='addlocation'){

                    $index = \wms\location\Model::get($request->post('id')->toInt());

                    $ret['modal'] = Language::trans(\wms\locations\View::create()->render($index,$login));
                    
                } elseif ($action ==='export') {
                    $params = $request->getParsedBody();
                    $params['module'] = 'wms-export';
                    $ret['location'] = WEB_URL.'export.php?'.http_build_query($params).'&type=cystock&amp;';
                } else{
                    if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->filter('0-9,'), $match)) {
                       
                        if ($action === 'print') {

                            foreach ($match[1] As $row){
                                // ตรวจสอบรายการที่มีอยู่แล้ว
                                $index = \wms\pallets\Model::get($row);
                                if ($index) {
                                    $db->insert('pallet_print', array(
                                        'id' => Null,
                                        'sale_order' => $index->sale_order,
                                        'delivery_date' => $index->delivery_date,
                                        'customer' => $index->customer,
                                        'location_code' => $index->location_code,
                                    ));
                                }
                            }
    
                        } elseif ($action ==='export'){
    
                            $params = $request->getParsedBody();
                            $params['module'] = 'wms-export';
                            $ret['location'] = WEB_URL.'export.php?'.http_build_query($params).'&type=stock&amp;';
    
                        } 
                    }

                    $ret['alert'] = Language::get('Saved successfully');
                    $ret['location'] = 'reload';
                    $ret['open'] = 'https://sail.anjinyk.co.th/pdf/kd-pallet';
                    $request->removeToken();
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