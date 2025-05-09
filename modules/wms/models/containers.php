<?php

namespace wms\containers;

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

    protected $table ='material';
    private $category;

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
                array('material_number', $id),
            ));
            return $search;
        }
    }

    public static function get_currency(){

        return static::createQuery()
        ->select('id','currency')
        ->from('currency')
        ->order('currency')
        ->execute();
    }

    public function submit(Request $request){
        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('container');

        $strNewDate = date('Y-m-d', strtotime($request->post('receive_date')->date('Y-m-d'))) .' '. date('H:i:s');

        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            try {
            
                if ($request->post('size')->toInt() == 1) {
                    $size = 20;
                } else {
                    $size = 40;
                }
                $save = array(
                    'status' => 1,
                    'gr_flg' => 1,
                    'receive_date' => $strNewDate,
                    'year_lot' => $request->post('year')->toInt(),
                    'week_lot' => $request->post('week')->toInt(),
                    'lot_no' => $request->post('lot')->topic(),
                    'container_size' => $size,
                    'model' => $request->post('model')->topic(),
                    'eta_date' => $request->post('eta')->date(),
                    'ata_date' => $request->post('ata')->date(),
                    'container_bl' => $request->post('bl')->topic(),
                    'created_at' => date('Y-m-d'),
                    'created_by' => $login['id']
                );

                $db->update($table,array('id', $request->post('container_id')->toInt()),$save);

                $update_gr = array(
                    'gr_flg' => 1,
                    'cy_flg' => 1,
                    'container_received' => $strNewDate,
                );

                $table = $model->getTableName('packing_list');
                $db->update($table,array('container', $request->post('container')->topic()),$update_gr);

                $ret['alert'] = Language::get('Saved successfully');
                $ret['location'] = 'reload';
                $request->removeToken();

            } catch (\Kotchasan\InputItemException $e){
                $ret['alert'] = $e->getMessage();
            }
        }
        echo json_encode($ret);
    }

}