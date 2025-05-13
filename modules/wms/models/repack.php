<?php

namespace wms\repack;

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
        $where[] = array('T1.truck_confirm',0);

        return static::createQuery()
        ->select('T1.id','T2.serial_number','T4.material_number')
        ->from('delivery_order T1')
        ->join('inventory_stock T2','LEFT',array('T1.actual_id','T2.id'))
        ->join('location T3','LEFT',array('T2.location_id','T3.id'))
        ->join('material T4','LEFT',array('T2.material_id','T4.id'))
        ->where($where)
        ->order('T4.material_number');

    }

    public static function checkSer($id){

        $where = array();
        $where[] = array('T1.serial_number',$id);

        return static::createQuery()
        ->select('T1.id','T1.serial_number','T1.quantity','T1.actual_quantity','T1.material_id','T1.location_id','T2.material_number','T2.material_name_en','T3.location_code')
        ->from('inventory_stock T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->where($where)
        ->execute();
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

    public static function check_packing($id){

        $where = array();
        $where[] = array('box_id',$id);

        return static::createQuery()
        ->select('id','box_id','material_id','quantity')
        ->from('packing_list')
        ->where($where)
        ->execute();
    }

    public static function getBox($id,$location_code){

        $where = array();
        $where[] = array('T2.serial_number',$id);
        $where[] = array('T1.pallet_no',$location_code);

        return static::createQuery()
        ->select('T1.id','T2.serial_number','T4.material_number','T1.pallet_no',
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

                        if ($request->post('serial_number')->toString() == '') {

                            $ret['serial_number']='';
                            $ret['fault'] = Language::get('Please Scan Box ID !!');
                            $request->removeToken(); 

                        } else {

                            $scan_qr = explode("_",$request->post('serial_number')->toString());

                            if (count($scan_qr) <= 5) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Please Scan QR Code');
                                $request->removeToken();
                            } else {

                                $serial_number = $scan_qr[4];

                                $check_box = static::checkSer($serial_number);
    
                                // ตรวจสอบว่ามีการบันทึกแล้วหรือไม่
                                if ($check_box == false) {
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Box ID No Data !!');
                                    $request->removeToken(); 
                                } elseif ($check_box[0]->actual_quantity == 0) {
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Quantity Is Zero !!');
                                    $request->removeToken();
                                } else {
                                    
                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-repack',
                                'material' => $check_box[0]->material_number,
                                'quantity' => $check_box[0]->actual_quantity,
                                'box' => $check_box[0]->serial_number,
                                'status' => 1)); 
                                
                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken();
    
                                }

                            }

                        }

                    } elseif ($request->post('status')->toInt() == 1) {

                        if ($request->post('destination')->toString() == '') {
                            $ret['destination']='';
                            $ret['fault'] = Language::get('Please Scan Destination Box ID!!');
                            $request->removeToken(); 
                        } else {

                            $scan_qr = explode("_",$request->post('destination')->toString());
                            if (count($scan_qr) <= 5) {
                                $ret['destination']='';
                                $ret['fault'] = Language::get('Please Scan Destination QR Code');
                                $request->removeToken(); 
                            } else {
                                $destination = $scan_qr[4];
                                $check_destination = static::checkSer($destination);
                                if ($check_destination == true) {
                                    $ret['destination']='';
                                    $ret['fault'] = Language::get('Destination Box ID Already Exist !!');
                                    $request->removeToken(); 
                                } else {
                                    if ($request->post('material_number')->toString() != $scan_qr[1]) {
                                        $ret['destination']='';
                                        $ret['fault'] = Language::get('Material Number Not Macth Label !!');
                                        $request->removeToken(); 
                                    } elseif ($scan_qr[3] > $request->post('qty')->toInt()) {
                                        $ret['destination']='';
                                        $ret['fault'] = Language::get('New Labe Over Qty !!');
                                        $request->removeToken(); 
                                    } else {

                                        $check_box = static::checkSer($request->post('serial_number')->toString());

                                        $insert = array(
                                            'id' => Null,
                                            'storage_location' => '1097',
                                            'item_number' => '0000000000',
                                            'delivery_order' => '',
                                            'delivery_item_number' => '000000',
                                            'delivery_date' => date('Y-m-d'),
                                            'container' => date('Ymd'),
                                            'material_id' => $check_box[0]->material_id,
                                            'case_number' => '',
                                            'box_id' => $destination,
                                            'quantity' => $scan_qr[3],
                                            'check_flg' => 1,
                                            'check_mat' => 1,
                                            'receive_flg' => 1,
                                            'temp_container' => date('Ymd'),
                                            'temp_material' => $request->post('material_number')->toString(),
                                            'gr_flg' => 1,
                                            'cy_flg' => 0,
                                            'container_received' => date('Y-m-d H:i:s'),
                                            'file_name' => '-',
                                            'tr_flg' => 1,
                                            'tr_name' => 'Repack',
                                            'sap_wms' => '-',
                                            'created_at' => date('Y-m-d H:i:s'),
                                            'created_by' => $login['id'],
                                        );

                                        $table = $model->getTableName('packing_list'); 
                                        $db->insert($table,$insert);

                                        $checkBox = static::check_packing($destination);

                                        $save_stock = array(
                                            'id' => NULL,
                                            'reference' => $checkBox[0]->id,
                                            'job_id' => 0,
                                            'serial_number' => $destination,
                                            'material_id' => $checkBox[0]->material_id,
                                            'quantity' => (int)$checkBox[0]->quantity,
                                            'actual_quantity' => (int)$checkBox[0]->quantity,
                                            'location_id' => 2284,
                                            'inbound_date' => date('Y-m-d H:i:s'),
                                            'allocate_flg' => 0,
                                            'created_at' => date('Y-m-d'),
                                            'created_by' => $login['id']
                                        );

                                        $table = $model->getTableName('inventory_stock');
                                        $db->insert($table,$save_stock);

                                        $total_qty = $request->post('qty')->toInt() - $checkBox[0]->quantity;

                                        $update = array(
                                            'actual_quantity' => $total_qty,
                                        );
                                        $where = array();
                                        $where[] = array('serial_number',$request->post('serial_number')->toString());
                                        
                                        $table = $model->getTableName('inventory_stock');
                                        $db->update($table,$where,$update);

                                        $check_box = static::checkSer($request->post('serial_number')->toString());
                                                                               
                                        $save_tran = array(
                                            'id' => NULL,
                                            'transaction_date' => date("Y-m-d H:i:s"),
                                            'transaction_type' => 'Repackage-Out',
                                            'reference' =>  $check_box[0]->id,
                                            'serial_number' => $request->post('serial_number')->toString(),
                                            'material_id' => $check_box[0]->material_id,
                                            'quantity' => -(int)$checkBox[0]->quantity,
                                            'from_location' => 0,
                                            'location_id' => $check_box[0]->location_id,
                                            'sale_id' => 0,
                                            'pallet_id' => 0,
                                            'created_at' => date('Y-m-d'),
                                            'created_by' => $login['id']
                                        );

                                        $table = $model->getTableName('transaction');

                                        $db->insert($table,$save_tran);

                                        $save_tran = array(
                                            'id' => NULL,
                                            'transaction_date' => date("Y-m-d H:i:s"),
                                            'transaction_type' => 'Repackage-In',
                                            'reference' =>  $checkBox[0]->id,
                                            'serial_number' => $destination,
                                            'material_id' => $checkBox[0]->material_id,
                                            'quantity' => (int)$checkBox[0]->quantity,
                                            'from_location' => 0,
                                            'location_id' => 2284,
                                            'created_at' => date('Y-m-d'),
                                            'created_by' => $login['id']
                                        );

                                        $table = $model->getTableName('transaction');

                                        $db->insert($table,$save_tran);
                              
                                        $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-repack',
                                        'material' => '',
                                        'quantity' => 0,
                                        'box' => '',
                                        'destination' => '',
                                        'status' => 0)); 
                                        
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