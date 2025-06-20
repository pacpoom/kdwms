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

    public static function declaration($id,$mat){

        return static::createQuery()
        ->select('id','declaration_no','material_id','quantity')
        ->from('declaration')
        ->where(array(
            array('declaration_no',$id),
            array('material_id',$mat)
        ))
        ->first();
    }

    public static function checkSer($id){
        return static::createQuery()
        ->select('id','serial_number')
        ->from('transfer')
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

                                if ($check_box == true) {
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Box ID already exists');
                                    $request->removeToken(); 
                                } else {
                                    if ($scan_qr[3] > 0) {
                                        
                                    }
                                }
                            } else {
                                $ret['serial_number'] = 'Please fill in';
                                $ret['fault'] = Language::get('Scan Error');
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
