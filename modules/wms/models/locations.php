<?php

namespace wms\locations;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Number;

/**
 * module=inventory-customer
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model{

    protected $table ='location';
    private $category;
    public static function get($zone,$area,$bin){

        $zone = trim($zone);
        if ($zone == '') {
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
                array('zone', $zone),
                array('area', $area),
                array('bin',$bin)
            ));
            return $search;
        }
    }

    public function submit(Request $request){
        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('location');

        //var_dump($request->post('textarea_checkbox')->topic());
        
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            try {
                
                $location_code = $request->post('location_code')->topic();

                if ($location_code == '') {
                    $ret['ret_location_code'] = 'Please fill in';
                } elseif (self::get($request->post('zone_text')->topic(), $request->post('area_text')->topic(), $request->post('bin_text')->topic())) {
                    throw new \Kotchasan\InputItemException('This location already exists');
                } else {

                    $save = array(
                    'id' => NULL,
                    'location_code' => $location_code,
                    'zone' => \index\Category\Model::save('zone',$request->post('zone_text')->topic()),
                    'area' => \index\Category\Model::save('area',$request->post('area_text')->topic()),
                    'bin' => \index\Category\Model::save('bin',$request->post('bin_text')->topic()),
                    'location_type' => \index\Category\Model::save('location_type',$request->post('location_type_text')->topic()),
                    'warehouse' => \index\Category\Model::save('warehouse',$request->post('warehouse_text')->topic()),
                    'description' => $request->post('description')->topic(),
                    'type' => 'location',
                    'active' => 1,
                    'created_at' => date('Y-m-d'),
                    'created_by' => $login['id']
                );

                $db->insert($table, $save);

                $ret['alert'] = Language::get('Saved successfully');
                $ret['modal'] = 'close';
                $ret['location'] = 'reload';
                $request->removeToken();

                }

            } catch (\Kotchasan\InputItemException $e){
                $ret['alert'] = $e->getMessage();
            }
        }
        echo json_encode($ret);
    }
}