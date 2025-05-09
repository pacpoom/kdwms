<?php

namespace wms\material;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='material';

    public static function toDataTable($params){
        $params = array();
        return static::createQuery()
        ->select('T1.id','T1.material_number','T1.material_name_en','T1.material_name_thai','T1.inspection_flg'
        ,'T2.unit','T1.net_weight','T1.unit_price','T3.currency','T1.material_type')
        ->from('material T1')
        ->join('unit T2','LEFT',array('T2.id','T1.unit'))
        ->join('currency T3','LEFT',array('T3.id','T1.currency'))
        ->order('material_number');
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

                    $index = \wms\material\Model::get($request->post('id')->toInt());

                    $ret['modal'] = Language::trans(\wms\materials\View::create()->render($index,$login));
                    
                } else{
                    if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->filter('0-9,'), $match)) {
                       
                        if ($action === 'delete') {

                            //var_dump('delete');
                            // ลบ
                            $db->delete($this->getTableName('material'), array('id', $match[1]), 0);
                            
                            $dir = ROOT_PATH.DATA_FOLDER.'material/';
                            foreach ($match[1] as $id) {
                                if (is_file($dir.$id.'.jpg')) {
                                    unlink($dir.$id.'.jpg');
                                }
                            }

                            // log
                            \Index\Log\Model::add(0, 'material', 'Delete', '{LNG_Delete} {LNG_Inventory} ID : '.implode(', ', $match[1]), $login['id']);
                            // reload
                            $ret['location'] = 'reload';
    
                        } elseif ($action ==='statusd'){
    
                            $index = \wms\material\Model::get($request->post('id')->toInt());
    
                            $ret['modal'] = Language::trans(\wms\materials\View::create()->render($index,$login));
    
                        } elseif ($action ==='Active'){
                            $update_data['active'] = 1;
                            $db->update($this->getTableName('material'), array('id', $match[1]), $update_data);
                            $ret['location'] = 'reload';

                        } elseif ($action ==='Non Active'){
                            $update_data['active'] = 0;
                            
                            $db->update($this->getTableName('material'), array('id', $match[1]), $update_data);
                            $ret['location'] = 'reload';

                        } elseif ($action ==='print'){

                            $index = \wms\material\Model::get($request->post('id')->toInt());

                            $ret['modal'] = Language::trans(\wms\label\View::create()->render($index,$login));
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