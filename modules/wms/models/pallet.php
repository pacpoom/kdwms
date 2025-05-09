<?php

namespace wms\pallet;

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

    public static function GetSo_detail($so){

        $where = array();
        $where[] = array('T1.sale_order',$so);

        return static::createQuery()
        ->select('T1.id','T2.customer_code','T2.customer_name')
        ->from('sale_order_status T1')
        ->join('customer_master T2','LEFT',array('T1.customer_id','T2.id'))
        ->where($where)
        ->execute();

    }

    public static function getSeq($so){

        $where = array();
        $where[] = array('T1.id',$so);

        return static::createQuery()
        ->select('T1.id','T1.seq_id')
        ->from('seq T1')
        ->where($where)
        ->execute();

    }

    public function submit(Request $request){
        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('location');

        
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            try {
                
                $detail = \wms\pallet\Model::GetSo_detail($request->post('so')->toString());
                $seq = \wms\pallet\Model::getSeq(1);
                $number = $seq[0]->seq_id;
                if ($request->post('number')->toInt() == 0) {
                    $ret['number']='';
                    $ret['fault'] = Language::get('Please Fill Number !!');
                    $request->removeToken(); 
                } else {

                    $db->query("TRUNCATE TABLE pallet_print;");

                    for ($i = 1; $i <= $request->post('number')->toInt(); $i++) {
                        $number++;
                        $insert = array(
                            'id' => NULL,
                            'sale_order' => $request->post('so')->toString(),
                            'delivery_date' => $request->post('delivery')->date(),
                            'customer' => $detail[0]->customer_name,
                            'location_code' => 'ANJI' . sprintf('%08d',$number),
                        );

                        $table = $model->getTableName('pallet_print');
                        $db->insert($table,$insert);

                        $insert = array(
                            'id' => NULL,
                            'sale_order' => $request->post('so')->toString(),
                            'delivery_date' => $request->post('delivery')->date(),
                            'customer' => $detail[0]->customer_name,
                            'location_code' => 'ANJI' . sprintf('%08d',$number),
                            'truck_id' => '',
                            'truck_flg' => 0,
                            'truck_date' => NULL
                        );

                        $table = $model->getTableName('pallet_log');
                        $db->insert($table,$insert);

                    }

                    
                    $update = array(
                        'seq_id' => $number
                    );

                    $where = array();
                    $where[] = array('id',1);

                    $table = $model->getTableName('seq');
                    $db->update($table,$where,$update);

                    $ret['alert'] = Language::get('Saved successfully');
                    $ret['location'] = 'reload';
                    $ret['open'] = 'https://sail.anjinyk.co.th/pdf/kd-pallet';
                    $request->removeToken();
                }
            } catch (\Kotchasan\InputItemException $e){
                $ret['alert'] = $e->getMessage();
            }
        }
        echo json_encode($ret);
    }
}