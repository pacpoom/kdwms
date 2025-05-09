<?php

namespace wms\truck;

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

    public static function toDataTable($so){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.pallet_no','!=','');
        $where[] = array('T1.truck_id','=','');

        return static::createQuery()
        ->select('T1.sale_order','T1.pallet_no')
        ->from('delivery_order T1')
        ->where($where)
        ->groupBy('T1.sale_order','T1.pallet_no')
        ->order('T1.pallet_no');

    }

    public static function checkSer($id){
        return static::createQuery()
        ->select('T1.id','T1.serial_number','T1.quantity','T1.actual_quantity','T1.material_id','T1.location_id','T2.material_number','T2.material_name_en','T3.location_code','T4.container')
        ->from('inventory_stock T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->join('packing_list T4','LEFT',array('T1.serial_number','T4.box_id'))
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

    public static function get_so(){

        $where = array();
        $where[] = array('pallet_no','!=','');

        return static::createQuery()
        ->select('sale_order')
        ->from('delivery_order')
        ->where($where)
        ->groupBy('sale_order')
        ->order('sale_order')
        ->toArray()
        ->execute();

    }

    public static function get_total($id){

        $where = array();
        $where[] = array('sale_order',$id);

        return static::createQuery()
        ->select(sql::COUNT('id','total'))
        ->from('delivery_order')
        ->where($where)
        ->execute();

    }

    public static function get_actual($id){

        $where = array();
        $where[] = array('sale_order',$id);
        $where[] = array('actual_id','!=',0);

        return static::createQuery()
        ->select(sql::COUNT('id','total'))
        ->from('delivery_order')
        ->where($where)
        ->execute();

    }

    public static function GetBoxID($box){

        $where = array();
        $where[] = array('T1.serial_number',$box);

        return static::createQuery()
        ->select('T1.id','T1.serial_number','T1.material_id','T4.material_number','T1.actual_quantity','T1.location_id')
        ->from('inventory_stock T1')
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->join('material T4','LEFT',array('T1.material_id','T4.id'))
        ->where($where)
        ->execute();
    }

    public static function GetDetailSo($so,$material){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.material_number',$material);

        return static::createQuery()
        ->select('T1.material_number',SQL::SUM('T1.planed_quantity','plan_qty'),'T1.ship_qty')
        ->from('sale_order T1')
        ->where($where)
        ->groupBy('T1.material_number','T1.ship_qty')
        ->execute();

    }

    public static function GetSo($so,$box){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.inventory_id',$box);
        $where[] = array('T1.actual_id',0);

        return static::createQuery()
        ->select('T1.id','T1.actual_id','T1.sale_order_id')
        ->from('delivery_order T1')
        ->where($where)
        ->execute();

    }

    public static function GetSoId($so,$mat){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.material_number',$mat);
        $where[] = array('T1.actual_id',0);

        return static::createQuery()
        ->select('T1.id','T1.actual_id','T1.inventory_id')
        ->from('delivery_order T1')
        ->where($where)
        ->order('T1.id')
        ->execute();

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

    public static function GetPallet($pallet){

        $where = array();
        $where[] = array('T1.location_code',$pallet);

        return static::createQuery()
        ->select('T1.id','T1.location_code','T1.sale_order','T1.truck_flg')
        ->from('pallet_log T1')
        ->where($where)
        ->execute();

    }

    public static function GetPallet_detail($so,$pallet){

        $where = array();
        $where[] = array('T1.pallet_no',$pallet);
        $where[] = array('T1.sale_order',$so);

        return static::createQuery()
        ->select('T1.id','T1.truck_id')
        ->from('delivery_order T1')
        ->where($where)
        ->execute();

    }

    public function submit(Request $request){

        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login)) {
                try {


                    if ($request->request('status')->toInt() == 0) {
                        if ($request->request('so')->toString() =='') {
                            $ret['serial_number']='';
                            $ret['fault'] = Language::get('Please Select Sale Order !!');
                            $request->removeToken(); 
                        } else {
                            
                            $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-truck',
                            'so' => $request->request('so')->toString(),
                            'status' => 1)); 
                            $request->removeToken();
                        }
                    } elseif ($request->request('status')->toInt() == 1) {
                        if ($request->request('truck')->toString() =='') {
                            $ret['truck']='';
                            $ret['fault'] = Language::get('Please Fill Truck ID !!');
                            $request->removeToken();
                        } else {
                            $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-truck',
                            'so' => $request->request('so')->toString(),
                            'status' => 2,
                            'truck' => str_replace(' ','',$request->request('truck')->toString()))); 
                            $request->removeToken();
                        }
                    } elseif ($request->request('status')->toInt() == 2) {
                        if ($request->request('pallet')->toString() =='') {
                            $ret['pallet']='';
                            $ret['fault'] = Language::get('Please Fill Pallet ID !!');
                            $request->removeToken();
                        } else {

                            $check_pallet = \wms\truck\Model::GetPallet($request->request('pallet')->toString());

                            if ($check_pallet == false) {
                                $ret['pallet']='';
                                $ret['fault'] = Language::get('Pallet No. Incorrect');
                                $request->removeToken(); 
                            } elseif ($check_pallet[0]->sale_order != $request->request('so')->toString()) {
                                $ret['pallet']='';
                                $ret['fault'] = Language::get('Pallet No. And SO Not Match !!');
                                $request->removeToken();
                            } elseif ($check_pallet[0]->truck_flg == 1) {
                                $ret['pallet']='';
                                $ret['fault'] = Language::get('Pallet No. Already Confirm !!');
                                $request->removeToken();
                            } else {

                                $check_pallet_detail = \wms\truck\Model::GetPallet_detail($request->post('so')->toString(),$request->request('pallet')->toString());

                                if ($check_pallet_detail == false) {
                                    $ret['pallet']='';
                                    $ret['fault'] = Language::get('Pallet No. Incorrect SO');
                                } else {

                                    $update = array(
                                        'truck_id' =>  str_replace(' ','',$request->request('truck')->toString()),
                                        'truck_date' => date('Y-m-d H:i:s')
                                    );
    
                                    $where = array();
                                    $where[] = array('sale_order',$request->post('so')->toString());
                                    $where[] = array('pallet_no',$request->request('pallet')->toString());
    
                                    $table = $model->getTableName('delivery_order');
                                    $db->update($table,$where,$update);
    
                                    $update = array(
                                        'truck_id' =>  str_replace(' ','',$request->request('truck')->toString()),
                                        'truck_date' => date('Y-m-d H:i:s'),
                                        'truck_flg' => 1
                                    );
    
                                    $where = array();
                                    $where[] = array('sale_order',$request->post('so')->toString());
                                    $where[] = array('location_code',$request->request('pallet')->toString());
    
                                    $table = $model->getTableName('pallet_log');
                                    $db->update($table,$where,$update);
    
                                    
                                    $update = array(
                                        'status' => 3
                                    );
    
                                    $where = array();
                                    $where[] = array('sale_order',$request->post('so')->toString());
    
                                    $table = $model->getTableName('sale_order_status');
                                    $db->update($table,$where,$update);
    
                                    $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-truck',
                                    'so' => $request->request('so')->toString(),
                                    'status' => 2,
                                    'truck' =>  str_replace(' ','',$request->request('truck')->toString()),
                                    'time' => date('His')));
    
                                    $ret['pallet']='';
                                    $ret['message'] = Language::get('Saved successfully');
                                    $request->removeToken();
                                    
                                }

                            }
                        }
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