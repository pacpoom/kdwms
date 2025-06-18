<?php

namespace wms\querymaterial;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

/**
 * module=inventory-customer
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model{

    protected $table_pdi ='pdi_in';

    protected $table ='gaoff';

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
                array('vin_number', $id)
            ));
            return $search;
        }
    }

    public static function toDataTable(){

        return static::createQuery()
        ->select('T1.id','T2.vin_number','T3.model','T3.color','T4.location_code')
        ->from('sale_order T1')
        ->join('gaoff T2','LEFT',array('T1.gaoff_id','T2.id'))
        ->join('vehicle_code T3','LEFT',array('T2.vc_code','T3.vehicle_code'))
        ->join('vehiclein T5','LEFT',array('T1.gaoff_id','T5.gaoff_id'))
        ->join('location T4','LEFT',array('T5.location_id','T4.id'))
        ->where(array('T1.status','=',0))
        ->order('T4.location_code');


    }

    public static function getContainer($raw){
        return static::createQuery()
        ->select('T1.id','T1.inbound_date','T1.serial_number','T1.actual_quantity','T4.location_code')
        ->from('inventory_stock T1')
        ->join('material T3','LEFT',array('T1.material_id','T3.id'))
        ->join('location T4','LEFT',array('T1.location_id','T4.id'))
        ->where(array(array('T3.material_number',$raw),array('T1.actual_quantity','!=',0)))
        ->order('T1.inbound_date');

    }

    public static function get_pdi($id){
        if (empty($id)) {
            return (object) array(
                'id' => 0
            );
        } else {
            $query = static::createQuery()
                ->select('S1.id','S2.vin_number','S2.vc_code','S1.driver_id')
                ->from('vehiclein S1')
                ->join('gaoff S2','LEFT',array('S1.gaoff_id','S2.id'))
                ->where(array('S2.vin_number',$id));
            return $query->first();
        }
    }

    public static function get_location($id){
        if (empty($id)) {
            return (object) array(
                'location_code' => 0
            );
        } else {
            // แก้ไข อ่านรายการที่เลือก
            $query = static::createQuery()
                ->select('S1.id','S1.location_code','S1.zone','S1.area','S1.bin','S2.vehicle_model')
                ->from('location S1')
                ->where(array(array('S1.description','Generalzone'),array('S1.vin_id', 0),array('S1.pdiout_flg',1)))
                ->order('S1.zone','S1.area','S1.bin');
            return $query->first();
        }
    }

    public static function get_material(){

        return static::createQuery()
        ->select('T1.material_id','T3.material_number')
        ->from('inventory_stock T1')
        ->join('material T3','LEFT',array('T1.material_id','T3.id'))
        ->where(array(array('T1.actual_quantity','!=',0)))
        ->order('T3.material_number')
        ->groupBy('T1.material_id','T3.material_number')
        ->execute();
    }

    public static function declaration($id,$mat){

        return static::createQuery()
        ->select('id','declaration_no','material_id')
        ->from('declaration')
        ->where(array(
            array('declaration_no',$id),
            array('material_id',$mat)
        ))
        ->first();
    }

    public static function checkSer($id){
        return static::createQuery()
        ->select('T1.id','T1.reference','T2.container','T1.serial_number','T1.actual_quantity','T1.location_id','T1.material_id')
        ->from('inventory_stock T1')
        ->join('packing_list T2','LEFT',array('T1.serial_number','T2.box_id'))
        ->where(array('T1.serial_number',$id))
        ->first();
    }

    public static function checkSerid($id){
        return static::createQuery()
        ->select('id')
        ->from('inventory_stock')
        ->where(array('serial_number',$id))
        ->first();
    }


    public static function sumMat($id){
        return static::createQuery()
        ->select('T1.declaration_no',sql::COUNT('T1.id','count_id'),sql::SUM('T2.quantity','qty'))
        ->from('declaration T1')
        ->join('transfer T2','LEFT',array('T1.id','T2.declaration_no'))
        ->where(array('T1.declaration_no',$id))
        ->groupBy('T1.declaration_no')
        ->execute();
    }

    public static function sumstock($id){
        return static::createQuery()
        ->select('T1.material_id',sql::COUNT('T1.id','count_id'),sql::SUM('T1.actual_quantity','qty'))
        ->from('inventory_stock T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->where(array(array('T2.material_number',$id),array('actual_quantity','!=',0)))
        ->groupBy('T1.material_id')
        ->execute();
    }

    public function submit(Request $request){

        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('inventory_stock'); 
        $table_tran = $model->getTableName('transaction');
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login)) {
                try {

                    if ($request->post('raw')->toString() == '') {
                        $ret['raw']='';
                        $ret['ret_raw'] = 'Please fill in';
                    } else {

                        $total_sum = \wms\querymaterial\Model::sumstock($request->post('raw')->toString());

                        $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-querymaterial', 
                        'total_box' => isset($total_sum[0]->count_id) ? $total_sum[0]->count_id : 0,
                        'total_qty' => isset($total_sum[0]->qty) ? $total_sum[0]->qty : 0,
                        'raw' => $request->post('raw')->toString(),
                        'status' => 0,'time' => date('H-i-s')));

                        $ret['message'] = Language::get('Saved successfully');
                        $request->removeToken();

                    }


                } catch (\Kotchasan\InputItemException $e){
                    $ret['alert'] = $e->getMessage();
                }
            }
        } else {
            $ret['fault'] = Language::get('Scan Error');
        }
        echo json_encode($ret);
    }
}