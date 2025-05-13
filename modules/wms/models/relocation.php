<?php

namespace wms\relocation;

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

    public static function getContainer($location_id){
        return static::createQuery()
        ->select('T1.id','T1.serial_number','T3.material_number','T1.quantity','T4.location_code')
        ->from('inventory_stock T1')
        ->join('material T3','LEFT',array('T1.material_id','T3.id'))
        ->join('location T4','LEFT',array('T1.location_id','T4.id'))
        ->where(array(array('T4.location_code',$location_id),array('T1.actual_quantity','!=',0)))
        ->order('T3.material_number');

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

    public static function get_material($id){

        return static::createQuery()
        ->select('id','material_number','material_name_en')
        ->from('material')
        ->where(array('material_number',$id))
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
        ->join('packing_list T2','LEFT',array('T1.reference','T2.id'))
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
        ->select('location_id',sql::COUNT('id','count_id'),sql::SUM('actual_quantity','qty'))
        ->from('inventory_stock')
        ->where(array(array('location_id',$id),array('actual_quantity','!=',0)))
        ->groupBy('location_id')
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

                    if ($request->post('location_code')->toString() == '') {
                        $ret['serial_number']='';
                        $ret['ret_location_code'] = 'Please fill in';
                    } else {
                        if ($request->post('status')->toInt() == 0) {
                            
                            $get_location = \wms\receive\Model::getLocation($request->post('location_code')->toString());

                            if ($get_location == false) {

                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Location Incorrect !!');
                                
                            } else {

                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-relocation', 
                                'total_box' => 0,
                                'material_number' => '',
                                'material_name' => '',
                                'total_qty' => 0,
                                'qty' => 0,
                                'location' => $get_location[0]->location_code,
                                'status' => 1,'time' => date('H-i-s')));

                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken();
                            }
                        } elseif (($request->post('status')->toInt() == 1)) {

                            $get_location = \wms\receive\Model::getLocation($request->post('location_code')->toString());

                            if ($get_location == false) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Location Incorrect !!');
                                
                            } else {
                                $scan_qr = explode("_",$request->post('serial_number')->topic());

                                $save = array(
                                    'location_id' => $get_location[0]->id,
                                );

                                if (empty($save['location_id'])) {
                                    $ret['serial_number']='';
                                    $ret['ret_location_code'] = 'Please fill in';
                                } else {
                                    if (count($scan_qr) >= 6){
            
                                        $check_box = \wms\relocation\Model::checkSer($scan_qr[4]);
            
                                        $get_id = \wms\relocation\Model::checkSerid($scan_qr[4]);
            
                                        if ($check_box == false) {
                                            $ret['serial_number']='';
                                            $ret['fault'] = Language::get('No Data');
                                            $request->removeToken(); 
                                        } else {
                
                                            if ($check_box->actual_quantity == 0) {
                                                $ret['serial_number']='';
                                                $ret['fault'] = Language::get('Box Empty');
                                                $request->removeToken(); 
                                            } else {
                
                                                if ($check_box->location_id == $get_location[0]->id) {
                                                    $ret['serial_number']='';
                                                    $ret['fault'] = Language::get('Double Location');
                                                    $request->removeToken(); 
                                                } else {
            
                                                    $db->update($table,array('id', $get_id->id),$save);
                                                    
                                                    $save_tran = array(
                                                        'id' => NULL,
                                                        'transaction_date' => date("Y-m-d H:i:s"),
                                                        'transaction_type' => 'Move Location',
                                                        'reference' => $check_box->reference,
                                                        'serial_number' => $scan_qr[4],
                                                        'material_id' => $check_box->material_id,
                                                        'quantity' => $check_box->actual_quantity,
                                                        'from_location' => $check_box->location_id,
                                                        'location_id' => $save['location_id'],
                                                        'sale_id' => 0,
                                                        'pallet_id' => 0,
                                                        'created_at' => date('Y-m-d'),
                                                        'created_by' => $login['id']
                                                    );
            
                                                    $db->insert($table_tran,$save_tran);
            
                                                    $material_number = \wms\relocation\Model::get_material($scan_qr[1]);
                                                    $total_sum = \wms\relocation\Model::sumstock($save['location_id']);
                                        
                                                    $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-relocation', 
                                                    'total_box' => $total_sum[0]->count_id,
                                                    'material_number' => isset($material_number[0]->material_number) ? $material_number[0]->material_number : '',
                                                    'material_name' => isset($material_number[0]->material_name_en) ? $material_number[0]->material_name_en : '',
                                                    'total_qty' => $total_sum[0]->qty,
                                                    'qty' => $scan_qr[3],
                                                    'location' => $get_location[0]->location_code,
                                                    'status' => 1,'time' => date('H-i-s')));
            
                                                    $ret['message'] = Language::get('Saved successfully');
                                                    $ret['serial_number']='';
                                                    $request->removeToken();
                                                }
                
                                            }
                                        }
                
                                    } else {
                                        $ret['serial_number']='';
                                        $ret['fault'] = Language::get('Please Scan QR Code');
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