<?php

namespace wms\transfer;

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

    public static function check_pl($box){

        $where = array(
            array('box_id', $box)
        );

        return static::createQuery()
        ->select('id','box_id','material_id','quantity')
        ->from('packing_list')
        ->where($where)
        ->execute();
    }

    public static function check_inv($box){

        $where = array(
            array('serial_number', $box)
        );

        return static::createQuery()
        ->select('id','serial_number')
        ->from('inventory_stock')
        ->where($where)
        ->execute();
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

    public static function toDataTable2($params){

        return static::createQuery()
        ->select('T1.id','T1.serial_number','T2.material_number','T1.quantity')
        ->from('transfer T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->where(array('T1.declaration_no',$params))
        ->order('T1.id');
    
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

    public static function sumQuantity($id){
        return static::createQuery()
        ->select(sql::SUM('quantity','qty'))
        ->from('transfer')
        ->where(array('declaration_no',$id))
        ->execute();
    }

    public static function getCCL(){
        return static::createQuery()
        ->select('declaration_no')
        ->from('declaration')
        ->order('declaration_no')
        ->groupBy('declaration_no')
        ->execute();
    }

    public function submit(Request $request){

        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('transfer'); 
        $table_ccl = $model->getTableName('declaration');
        $table_tran = $model->getTableName('transaction');

        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login)) {
              
                    if ($request->post('location_code')->toString() == '') {
                        $ret['location_code'] = 'Please fill in';
                        $ret['fault'] = Language::get('Location Incorrect !!');
                        $request->removeToken();
                    } else {
                        if ($request->post('status')->toInt() == 0) {

                            $location_code = $request->post('location_code')->toString();
                            $get_location = \wms\receive\Model::getLocation($location_code);

                            if ($get_location == false) {
                                $ret['location_code']='';
                                $ret['fault'] = Language::get('Location Incorrect !!');
                                $request->removeToken();
                            } else {

                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-transfer', 
                                'location_code' => $request->post('location_code')->toString(),
                                'status' => 1,'time' => date('H-i-s'))); 
    
                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken(); 

                            }

                        } else {
                            $scan_qr = explode("_",$request->post('serial_number')->topic());
                           
                            if (count($scan_qr) >= 6) {

                                $checkBox = \wms\transfer\Model::check_pl($scan_qr[4]);

                                if ($checkBox == true) {
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Box ID already exists');
                                    $request->removeToken(); 
                                } else {
                                    if ($scan_qr[3] > 0) {
                                        $checkBox = \wms\transfer\Model::check_inv($scan_qr[4]);

                                        if ($checkBox == true) {
                                            $ret['serial_number']='';
                                            $ret['fault'] = Language::get('Box ID already exists');
                                            $request->removeToken(); 
                                        } else {

                                            $material_id = \wms\transfer\Model::get_material($scan_qr[1]);

                                            if ($material_id == false) {
                                                $ret['serial_number'] = '';
                                                $ret['fault'] = Language::get('Material Number not found');
                                                $request->removeToken();
                                            } else {

                                                $material_id = $material_id->id;

                                                $location_code = $request->post('location_code')->toString();
                                                $get_location = \wms\receive\Model::getLocation($location_code);

                                                $insert_pl = array(
                                                    'id' => NULL,
                                                    'storage_location' => 1097,
                                                    'item_number' => '0000000001',
                                                    'delivery_order' => '6000000001',
                                                    'delivery_item_number' => '000001',
                                                    'delivery_date' => date('Y-m-d'),
                                                    'container' => $request->post('container')->toString(),
                                                    'material_id' => $material_id,
                                                    'case_number' => $request->post('case_number')->toString(),
                                                    'box_id' => $scan_qr[4],
                                                    'quantity' => $scan_qr[3],
                                                    'check_flg' => 1,
                                                    'check_mat' => 1,
                                                    'receive_flg' => 1,
                                                    'temp_container' => $request->post('container')->toString(),
                                                    'temp_material' => $scan_qr[1],
                                                    'gr_flg' => 1,
                                                    'cy_flg' => 0,
                                                    'container_received' => date('Y-m-d H:i:s'),
                                                    'file_name' => '-',
                                                    'unit' => '-',
                                                    'tr_flg' => 1,
                                                    'tr_name' => '-',
                                                    'created_at' => date('Y-m-d H:i:s'),
                                                    'created_by' => $login['id']
                                                );

                                                $db->insert($model->getTableName('packing_list'), $insert_pl);
                                            
                                                $checkBox = \wms\transfer\Model::check_pl($scan_qr[4]);

                                                $save_stock = array(
                                                    'id' => NULL,
                                                   'reference' => $checkBox[0]->id,
                                                   'job_id' => 0,
                                                   'serial_number' => $scan_qr[4],
                                                   'material_id' => $checkBox[0]->material_id,
                                                   'quantity' => (int)$checkBox[0]->quantity,
                                                   'actual_quantity' => (int)$checkBox[0]->quantity,
                                                   'location_id' => $get_location[0]->id,
                                                   'inbound_date' => date('Y-m-d H:i:s'),
                                                   'allocate_flg' => 0,
                                                   'created_at' => date('Y-m-d'),
                                                   'created_by' => $login['id']
                                                );

                                                $table = $model->getTableName('inventory_stock');
                                                $db->insert($table,$save_stock);

                                                $save_tran = array(
                                                    'id' => NULL,
                                                    'transaction_date' => date("Y-m-d H:i:s"),
                                                    'transaction_type' => 'Adjustment Stock',
                                                    'reference' => $checkBox[0]->id,
                                                    'serial_number' => $scan_qr[4],
                                                    'material_id' => $checkBox[0]->material_id,
                                                    'quantity' => (int)$checkBox[0]->quantity,
                                                    'from_location' => 0,
                                                    'location_id' => $get_location[0]->id,
                                                    'sale_id' => 0,
                                                    'pallet_id' => 0,
                                                    'created_at' => date('Y-m-d'),
                                                    'created_by' => $login['id']
                                                );
                                                
                                                $table = $model->getTableName('transaction');
                                                $db->insert($table,$save_tran);

                                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-transfer', 
                                                'location_code' => $request->post('location_code')->toString(),
                                                'status' => 1,'time' => date('H-i-s'))); 
    
                                                $ret['message'] = Language::get('Saved successfully');
                                                $ret['serial_number']='';
                                                $request->removeToken(); 
                                            }
                                        }

                                    } else {
                                        $ret['serial_number'] = '';
                                        $ret['fault'] = Language::get('Scan Error 65018');
                                        $request->removeToken();
                                    }
                                }
                            } else {
                                $ret['serial_number'] = '';
                                $ret['fault'] = Language::get('Scan Error 65019');
                                $request->removeToken();
                            }
                        }
                    }
            }
        } else {
            $ret['fault'] = Language::get('Scan Error');
        }
        echo json_encode($ret);
    }
}
