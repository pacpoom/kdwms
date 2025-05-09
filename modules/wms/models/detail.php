<?php

namespace wms\detail;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='location';

    public static function toDataTable($params){
        
        $where = array();
        
        if (!empty($params['material_number'])) {
            $where[] = array('material_number',$params['material_number']);
        }
        if (!empty($params['sale_order'])){
            $where[] = array('sale_order',$params['sale_order']);
        }

        return static::createQuery()
        ->select('T1.id','T1.sale_order','T1.material_number','T3.serial_number','T1.quantity'
        ,'T4.location_code','T2.serial_number pick','T1.ship_date','T1.pallet_no','T1.truck_id','T1.confirm_flg','T1.confirm_date','T1.file_name')
        ->from('delivery_order T1')
        ->join('inventory_stock T2','LEFT',array('T1.actual_id','T2.id'))
        ->join('inventory_stock T3','LEFT',array('T1.inventory_id','T3.id'))
        ->join('location T4','LEFT',array('T3.location_id','T4.id'))
        ->where($where);
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
                    
                } elseif ($action ==='export') {
                    $params = $request->getParsedBody();
                    $params['module'] = 'wms-export';
                    $ret['location'] = WEB_URL.'export.php?'.http_build_query($params).'&type=detail&amp;';
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