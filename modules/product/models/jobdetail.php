<?php

namespace product\jobdetail;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='bom';

    public static function toDataTable($params){

        $params = array();
        return static::createQuery()
        ->select('T1.id','T1.model_no','T2.material_number Model','T2.material_type Model_Type','T2.material_name_en Model Name','T3.material_number','T3.material_name_en','T3.material_type','T1.usage','T4.unit')
        ->from('bom T1')
        ->join('material T2','LEFT',array('T1.model_no','T2.id'))
        ->join('material T3','LEFT',array('T1.material_id','T3.id'))
        ->join('unit T4','LEFT',array('T3.unit','T4.id'))
        ->order('T2.material_number');
        
    }

    public static function getJob_d($id){
        return static::createQuery()
        ->select('T1.id','T2.material_number','T2.material_name_en','T1.quantity_req','T1.quantity_stock','T1.quantity_pick')
        ->from('job_order_d T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->where(array('T1.job_id',$id))
        ->order('T1.id');
    }

    public static function getJob_h($id){
        return static::createQuery()
        ->select('T1.id','T1.status','T1.purchase_order','T1.delivery_date','T1.job_no','T2.material_number','T2.material_name_en'
        ,'T1.production_date','T1.plan','T1.total_production','T1.total_ng','T1.finished_date')
        ->from('job_order T1')
        ->join('material T2','LEFT',array('T1.model_no','T2.id'))
        ->where(array('T1.id',$id))
        ->first();
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
                if ($action ==='add'){

                    $index = \product\bom\Model::get($request->post('id')->toInt());

                    $ret['modal'] = Language::trans(\product\boms\View::create()->render($index,$login,0));
                    
                } else{
                    if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->filter('0-9,'), $match)) {
                       
                        if ($action === 'delete') {

                            //var_dump('delete');
                            // ลบ
                            $db->delete($this->getTableName('bom'), array('id', $match[1]), 0); 
                            // log
                            \Index\Log\Model::add(0, 'material', 'Delete', '{LNG_Delete} {LNG_Inventory} ID : '.implode(', ', $match[1]), $login['id']);
                            // reload
                            $ret['location'] = 'reload';
    
                        } elseif ($action ==='statusd'){
    
                            $index = \product\bom\Model::get($request->post('id')->toInt());
    
                            $ret['modal'] = Language::trans(\product\boms\View::create()->render($index,$login,1));
    
                        } elseif ($action ==='Active'){
                            $update_data['active'] = 1;
                            $db->update($this->getTableName('material'), array('id', $match[1]), $update_data);
                            $ret['location'] = 'reload';

                        } elseif ($action ==='Non Active'){
                            $update_data['active'] = 0;
                            
                            $db->update($this->getTableName('material'), array('id', $match[1]), $update_data);
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