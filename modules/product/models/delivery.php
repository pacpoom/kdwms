<?php

namespace product\delivery;

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
        ->select('T1.id','T2.serial_number','T4.material_number','T2.actual_quantity','T3.location_code')
        ->from('job_order_req T1')
        ->join('inventory_stock T2','LEFT',array('T1.inventory_id','T2.id'))
        ->join('location T3','LEFT',array('T3.id','T2.location_id'))
        ->join('material T4','LEFT',array('T2.material_id','T4.id'))
        ->where(array(array('T1.job_id',$params),array('pick_flg',0)))
        ->order('T3.location_code','T4.material_number');
    }

    public static function getJob_d($id){
        return static::createQuery()
        ->select('T1.id','T1.job_no','T1.purchase_order','T1.delivery_date','T2.material_number','T2.material_name_en','T1.plan','T1.production_date')
        ->from('job_order T1')
        ->join('material T2','LEFT',array('T1.model_no','T2.id'))
        ->where(array('T1.id',$id))
        ->first();
    }

    public static function getJOB(){
        return static::createQuery()
        ->select('id','job_no')
        ->from('job_order')
        ->order('job_no')
        ->groupBy('job_no')
        ->execute();
    }

    public static function getJOB_ID($id){
        return static::createQuery()
        ->select('id','job_no')
        ->from('job_order')
        ->where(array('job_no',$id))
        ->execute();
    }

    public static function getJOB_BYID($id){
        return static::createQuery()
        ->select('id','job_no')
        ->from('job_order')
        ->where(array('id',$id))
        ->execute();
    }

    public static function getBox($id){
        return static::createQuery()
        ->select('id','serial_number','actual_quantity')
        ->from('inventory_stock')
        ->where(array('serial_number',$id))
        ->execute();
    }

    public static function JobBox($id){
        return static::createQuery()
        ->select('T1.id','T1.job_id','T3.declaration_no','T1.inventory_id','T2.serial_number','T2.material_id','T2.actual_quantity','T1.pick_flg','T2.location_id')
        ->from('job_order_req T1')
        ->join('inventory_stock T2','LEFT',array('T1.inventory_id','T2.id'))
        ->join('declaration T3','LEFT',array('T3.id','T2.reference'))
        ->where(array('T2.serial_number',$id))
        ->execute();
    }

    public static function JobDetail($id,$job) {
        return static::createQuery()
        ->select('id','quantity_pick')
        ->from('job_order_d')
        ->where(array(array('job_id',$id),array('material_id',$job)))
        ->execute();
    }

    public static function Check_WIP($id) {
        return static::createQuery()
        ->select('id','material_id','quantity')
        ->from('inventory_wip')
        ->where(array('material_id',$id))
        ->execute();
    }


    public function submit(Request $request){

        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('job_order_d'); 
        $table_D = $model->getTableName('job_order_req');
        $table_stock = $model->getTableName('inventory_stock');
        $table_sac = $model->getTableName('transaction');
        $table_wip = $model->getTableName('inventory_wip');
        //var_dump($request->post('job_order')->toString());

        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login)) {
                try {

                    if ($request->post('job_order')->toInt() != 0) {

                        $scan_qr = explode("_",$request->post('serial_number')->topic());

                        if (count($scan_qr) == 6){
                            
                            $check = \product\transferpd\Model::JobBox($scan_qr[4]);

                            if ($check == false) {

                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Not In The Plan');
                                $request->removeToken();

                            } else {

                                if ($check[0]->actual_quantity == 0) {

                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Quantity Equal 0 !!');
                                    $request->removeToken();
                                } elseif ($check[0]->pick_flg == 1) {
                                    $ret['serial_number']='';

                                    $ret['fault'] = Language::get('Already Pick !!');
                                    $request->removeToken();
                                } else {

                                    $check_job = \product\transferpd\Model::JobDetail($check[0]->job_id,$check[0]->material_id);
        
                                    if ($check_job == false) {
                                        $ret['serial_number']='';
                                        $ret['fault'] = Language::get('Please Contact IT Admin !!');
                                        $request->removeToken();
                                    } else {
                                        $pick_qty =  (int)$check_job[0]->quantity_pick + (int)$check[0]->actual_quantity;

                                        $save = array(
                                            'quantity_pick' => $pick_qty
                                        );

                                        $db->update($table,array('id',$check_job[0]->id),$save);

                                        $save = array(
                                            'pick_flg' => 1,
                                            'pick_qty' => (int)$check[0]->actual_quantity
                                        );

                                        $db->update($table_D,array(array('job_id',$check[0]->job_id),array('inventory_id',$check[0]->inventory_id)),$save);

                                        $save = array(
                                            'actual_quantity' => 0
                                        );

                                        $db->update($table_stock,array('serial_number',$check[0]->serial_number),$save);

                                        $job_ = \product\transferpd\Model::getJOB_BYID($request->post('job_order')->toInt());

                                        $save_tran = array(
                                            'id' => NULL,
                                            'transaction_date' => date("Y-m-d H:i:s"),
                                            'transaction_type' => 'Warehouse To Production',
                                            'reference' => $check[0]->declaration_no .'('.$job_[0]->job_no .')',
                                            'serial_number' => $scan_qr[4],
                                            'material_id' => $check[0]->material_id,
                                            'quantity' => $check[0]->actual_quantity,
                                            'from_location' => 0,
                                            'location_id' => $check[0]->location_id,
                                            'created_at' => date('Y-m-d'),
                                            'created_by' => $login['id']
                                        );

                                        $db->insert($table_sac,$save_tran);

                                        $check_wip = \product\transferpd\Model::Check_WIP($check[0]->material_id);

                                        if ($check_wip == false){

                                            $save_wip = array(
                                                'id' => NULL,
                                                'material_id' => $check[0]->material_id,
                                                'quantity' => $check[0]->actual_quantity,
                                                'created_at' => date('Y-m-d'),
                                                'created_by' => $login['id']
                                            );
    
                                            $db->insert($table_wip,$save_wip);

                                        } else {

                                            $sum_wip = (int)$check_wip[0]->quantity + $check[0]->actual_quantity ;

                                            $save_wip = array(
                                                'quantity' => $sum_wip
                                            );

                                            $db->update($table_wip,array('id',$check_wip[0]->id),$save_wip);
                                        }
                                   

                                        $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'product-transferpd', 
                                        'job_id' => $request->post('job_order')->toInt(),
                                        'status' => 1,'time' => date('H-i-s'))); 

                                        $ret['message'] = Language::get('Saved successfully');
                                        $ret['serial_number']='';
                                        $request->removeToken();
                                    }
                                }


                            }

                        } else {

                            if ($request->post('scan_status')->toInt() == 0){

                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'product-transferpd', 
                                'job_id' => $request->post('job_order')->toInt(),
                                'status' => 1,'time' => date('H-i-s'))); 

                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken();

                            } else {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Please Scan QR Code');
                                $request->removeToken();
                            }

                        }
                        
                    } else {

                        $ret['serial_number']='';
                        $ret['fault'] = Language::get('Please Choose Job Order');
                        $ret['location'] = 'reload';
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