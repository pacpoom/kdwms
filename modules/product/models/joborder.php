<?php

namespace product\joborder;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='bom';

    public static function toDataTable($params){

        $params = array();

        return static::createQuery()
        ->select('T1.id','T1.status','T1.purchase_order','T1.delivery_date','T1.job_no','T2.material_number','T2.material_name_en','T2.material_type','T1.production_date','T1.plan',
        'T3.unit','T1.total_production','T1.total_ng','T1.finished_date','T1.created_at','T4.username')
        ->from('job_order T1')
        ->join('material T2','LEFT',array('T1.model_no','T2.id'))
        ->join('unit T3','LEFT',array('T2.unit','T3.id'))
        ->join('user T4','LEFT',array('T1.created_by','T4.id'))
        ->order('T2.material_number');
    
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

                    $ret['modal'] = Language::trans(\product\joborders\View::create()->render($index,$login,0));
                    
                } else{
                    if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->filter('0-9,'), $match)) {
                       
                        if ($action === 'cancel') {

                            $save = array(
                                'status' => 4
                            );

                            $db->update($this->getTableName('job_order'), array('id', $match[1]), $save);

                            // log
                            \Index\Log\Model::add(0, 'material', 'Delete', '{LNG_Delete} {LNG_Inventory} ID : '.implode(', ', $match[1]), $login['id']);
                            // reload
                            $ret['location'] = 'reload';
    
                        } elseif ($action ==='statusd'){
    
                            $index = \product\bom\Model::get($request->post('id')->toInt());
    
                            $ret['modal'] = Language::trans(\product\joborders\View::create()->render($index,$login,1));
    
                        } elseif ($action ==='finished'){
                            $save = array(
                                'status' => 3
                            );

                            $db->update($this->getTableName('job_order'), array('id', $match[1]), $save);

                            $ret['location'] = 'reload';

                        } elseif ($action ==='production'){
                            $save = array(
                                'status' => 2
                            );

                            $db->update($this->getTableName('job_order'), array('id', $match[1]), $save);

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