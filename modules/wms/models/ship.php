<?php

namespace wms\ship;

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

    public static function toDataTable($pallet_no){

        $where = array();
        $where[] = array('T1.pallet_no',$pallet_no);

        return static::createQuery()
        ->select('T1.id','T2.serial_number','T4.material_number','T1.truck_confirm')
        ->from('delivery_order T1')
        ->join('inventory_stock T2','LEFT',array('T1.actual_id','T2.id'))
        ->join('location T3','LEFT',array('T2.location_id','T3.id'))
        ->join('material T4','LEFT',array('T2.material_id','T4.id'))
        ->where($where)
        ->order('T4.material_number');

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

        $where = array();
        $where[] = array('pallet_no',$id);
        $where[] = array('truck_confirm',0);

        return static::createQuery()
        ->select('pallet_no')
        ->from('delivery_order')
        ->where($where)
        ->groupBy('pallet_no')
        ->execute();
    }

    public static function getBox($id,$location_code){

        $where = array();
        $where[] = array('T2.serial_number',$id);
        $where[] = array('T1.pallet_no',$location_code);

        return static::createQuery()
        ->select('T1.id','T1.sale_order','T2.serial_number','T4.material_number','T1.pallet_no',
        'T1.truck_confirm','T2.material_id','T2.reference','T2.location_id')
        ->from('delivery_order T1')
        ->join('inventory_stock T2','LEFT',array('T1.actual_id','T2.id'))
        ->join('location T3','LEFT',array('T2.location_id','T3.id'))
        ->join('material T4','LEFT',array('T2.material_id','T4.id'))
        ->where($where)
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

                    if ($request->post('status')->toInt() == 0) {

                        if ($request->post('location_code')->toString() == '') {

                            $ret['location_code']='';
                            $ret['fault'] = Language::get('Please Scan Pallet !!');
                            $request->removeToken(); 

                        } else {
                            $location_code = $request->post('location_code')->toString();

                            $check_pallet = static::checkSerid($location_code);
                            // ตรวจสอบว่ามีการบันทึกแล้วหรือไม่
                            if ($check_pallet == false) {
                                $ret['location_code']='';
                                $ret['fault'] = Language::get('Pallet Incorrect !!');
                                $request->removeToken(); 
                            } else {
                                
                            $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-ship',
                            'pallet' => $location_code,
                            'status' => 1)); 
                            
                            $ret['message'] = Language::get('Saved successfully');
                            $ret['serial_number']='';
                            $request->removeToken();

                            }
                        }

                    } else {

                        $location_code = $request->post('location_code')->toString();
                        $login_user = $request->post('login_user')->toInt();

                        if ($location_code == '') {
                            $ret['location_code']='';
                            $ret['fault'] = Language::get('Please Scan Pallet !!');
                            $request->removeToken(); 
                        } else {

                            $scan_qr = explode("_",$request->post('serial_number')->toString());

                            if (count($scan_qr) <= 5) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Please Scan QR Code');
                                $request->removeToken();
                            } else {
                                $serial_number = $scan_qr[4];
                                $check_serial = static::getBox($serial_number,$location_code);

                                if ($check_serial == false) {
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Box ID Incorrect !!');
                                    $request->removeToken(); 
                                } else {
                                    if ($check_serial[0]->truck_confirm == 1) {
                                        $ret['serial_number']='';
                                        $ret['fault'] = Language::get('Box ID Already Confirm !!');
                                    } else {

                                        $update = array(
                                            'truck_confirm' => 1,
                                            'truck_confirm_date' => date('Y-m-d H:i:s'),
                                        );

                                        $where = array();
                                        $where[] = array('id',$check_serial[0]->id);

                                        $table = $model->getTableName('delivery_order');
                                        $db->update($table,$where,$update);

                                        $check_so = \wms\picking\Model::GetSo_detail($check_serial[0]->sale_order);
                                        $pallet = \wms\picking\Model::GetPallet($location_code);

                                        $save_tran = array(
                                        'id' => NULL,
                                        'transaction_date' => date("Y-m-d H:i:s"),
                                        'transaction_type' => 'Confirm Picking',
                                        'reference' => $check_serial[0]->reference,
                                        'serial_number' => $scan_qr[4],
                                        'material_id' => $check_serial[0]->material_id,
                                        'quantity' => 0,
                                        'from_location' => 0,
                                        'location_id' => $check_serial[0]->location_id,
                                        'sale_id' => $check_so[0]->id,
                                        'pallet_id' => $pallet[0]->id,
                                        'created_at' => date('Y-m-d'),
                                        'created_by' => $login['id']
                                        );

                                    $table = $model->getTableName('transaction');

                                    $db->insert($table,$save_tran);

                                    $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-ship',
                                    'pallet' => $location_code,
                                    'status' => 1,'time' => date('His'))); 

                                    $ret['message'] = Language::get('Saved successfully');
                                    $ret['serial_number']='';
                                    $request->removeToken();
                                    
                                    }
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