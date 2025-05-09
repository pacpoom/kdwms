<?php

namespace index\role;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    public static function toDataTable($params){
        $params = array();
        return static::createQuery()
        ->select('id','role_name','created_at')
        ->from('role')
        ->order('id');
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

    public static function get_location($id){

        return static::createQuery()
        ->select('location_code')
        ->from('location')
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
                // Database
                $db = $this->db();
                // id ที่ส่งมา
                if ($action ==='addlocation'){

                    $index = \wms\location\Model::get($request->post('id')->toInt());

                    $ret['modal'] = Language::trans(\wms\locations\View::create()->render($index,$login));
                    
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

                        } elseif ($action === 'print') {

                            $j = 0;

                           $db->query("TRUNCATE TABLE location_print;");

                            foreach ($match[1] as $row){

                                ++$j;

                                $location = \wms\location\Model::get_location($row);

                                foreach ($location as $item) {
                                    $location_print = array(
                                        'id' => Null,
                                        'location_code' => $item->location_code,
                                        
                                    );
                                    $db->insert($this->getTableName('location_print'),$location_print);
                                }
                      
                            }
                            $ret['alert'] = Language::get('Saved successfully');
                            $ret['open'] = 'https://sail.anjinyk.co.th/pdf/kd-location';
                            
                            
            
                        }
                    }
                }

            }
        }
      // var_dump($location);

        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }
}