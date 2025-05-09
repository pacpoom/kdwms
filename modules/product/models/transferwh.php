<?php

namespace product\transferwh;

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

    public static function toDataTable($id){

        return static::createQuery()
        ->select('T1.id','T2.material_number','T1.plan','T1.total_production')
        ->from('job_order T1')
        ->join('material T2','LEFT',array('T1.model_no','T2.id'))
        ->where(array('T1.id',$id));
    }

    public static function get_job($id){
        return static::createQuery()
        ->select('T1.id','T1.job_no','T1.model_no','T1.plan','T1.total_production','T2.material_number','T2.material_name_en','T1.purchase_order','T1.delivery_date')
        ->from('job_order T1')
        ->join('material T2','LEFT',array('T1.model_no','T2.id'))
        ->where(array('T1.id',$id))
        ->execute();
    }

    public static function get_jobDetail($id){
        return static::createQuery()
        ->select('T1.id','T1.job_no','T1.plan','T1.total_production','T2.material_number','T2.material_name_en','T1.purchase_order','T1.delivery_date')
        ->from('job_order T1')
        ->join('material T2','LEFT',array('T1.model_no','T2.id'))
        ->where(array(array('T1.id',$id)))
        ->execute();
    }

    public static function getBom($id){
        return static::createQuery()
        ->select('T1.id','T1.material_id','T1.usage')
        ->from('bom T1')
        ->join('job_order T2','LEFT',array('T1.model_no','T2.model_no'))
        ->where(array('T2.job_no',$id))
        ->order('T1.material_id')
        ->execute();
    }

    public static function getWIP($id){
        return static::createQuery()
        ->select('id','quantity')
        ->from('inventory_wip')
        ->where(array('material_id',$id))
        ->execute();
    }

    public static function getBox($id){
        return static::createQuery()
        ->select('id','serial_number')
        ->from('inventory_stock')
        ->where(array('serial_number',$id))
        ->execute();
    }

    public function submit(Request $request){

        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('job_order'); 
        $table_ccl = $model->getTableName('declaration');
        $table_wip = $model->getTableName('inventory_wip');
        $table_sac = $model->getTableName('transaction');
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login)) {
                try {

                    if (($request->post('status')->toInt() == 0)) {
                        if ($request->post('job_order')->toInt() == 0){
                            $ret['serial_number']='';
                            $ret['ret_job_order'] = 'Please fill in';
                        } else {
                            
                            $get_job = \product\transferwh\Model::get_job($request->post('job_order')->toInt());
                            
                            if ($get_job == false) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Please Contact IT Admin !!');
                                $request->removeToken();
                            } else {
                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'product-transferwh', 
                                'po' => $get_job[0]->purchase_order,
                                'job' =>  $get_job[0]->id,
                                'delivery' => $get_job[0]->delivery_date, 
                                'material_name' => $get_job[0]->material_name_en,
                                'location_code' => $request->post('location_code')->toInt(),
                                'status' => 1,'time' => date('H-i-s'))); 
    
                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken();
                            }
                        }
                    } else {
                        $scan_qr = explode("_",$request->post('serial_number')->topic());
                        if ($request->post('job_order')->toInt() == 0){
                            $ret['serial_number']='';
                            $ret['ret_job_order'] = 'Please fill in';
                        } elseif ($request->post('location_code')->toInt() == 0){
                            $ret['serial_number']='';
                            $ret['ret_location_code'] = 'Please fill in';
                        } elseif (count($scan_qr) != 6){
                            $ret['serial_number']='';
                            $ret['fault'] = Language::get('Please Scan QR Code');
                            $request->removeToken();
                        } else {

                            $checkBox = \product\transferwh\Model::getBox($scan_qr[4]);

                            if ($checkBox == true){
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Do not double scan');
                                $request->removeToken();

                            } else {

                                $get_job = \product\transferwh\Model::get_job($request->post('job_order')->toInt());

                                if ($get_job[0]->material_number == $scan_qr[1]) {
                                    $product_qty = (int)$get_job[0]->total_production + (int)$scan_qr[3];
                                    if ($get_job[0]->total_production >= $get_job[0]->plan){
                                        $ret['serial_number']='';
                                        $ret['fault'] = Language::get('Can,t Receive Over Plan !!');
                                        $request->removeToken();
                                    } elseif ($product_qty > $get_job[0]->plan) {
                                        $ret['serial_number']='';
                                        $ret['fault'] = Language::get('Can,t Receive Over Plan !!');
                                        $request->removeToken();
                                    } else {
    
                                        $bom_detail = \product\transferwh\Model::getBom($get_job[0]->job_no);
    
                                        foreach ($bom_detail as $item){
                                            $getWIP = \product\transferwh\Model::getWIP($item->material_id);
                                            if ($getWIP == false) {
                                                $qtyIssue = - ((int)$item->usage * (int)$scan_qr[3]);
                                                $save_wip = array(
                                                    'id' => NULL,
                                                    'material_id' => $item->material_id,
                                                    'quantity' => $qtyIssue,
                                                    'created_at' => date('Y-m-d'),
                                                    'created_by' => $login['id']
                                                );
        
                                                $db->insert($table_wip,$save_wip);
                                            } else {
    
                                                $qtyIssue = (int)$getWIP[0]->quantity - ((int)$item->usage * (int)$scan_qr[3]);
                                                $save_wip = array(
                                                    'quantity' => $qtyIssue,
                                                );
                                                $db->update($table_wip,array('id',(int)$getWIP[0]->id),$save_wip);
                                            }
                                        }
    
                                        $total_FG = (int)$get_job[0]->total_production + (int)$scan_qr[3];
                                        
    
                                        $save = array(
                                            'total_production' => (int)$total_FG
                                        );
    
                                        $db->update($table,array('id',$request->post('job_order')->toInt()),$save);
    
                                        $save_tran = array(
                                            'id' => NULL,
                                            'transaction_date' => date("Y-m-d H:i:s"),
                                            'transaction_type' => 'Production To Warehouse(FG)',
                                            'reference' => $get_job[0]->job_no,
                                            'serial_number' => $scan_qr[4],
                                            'material_id' => $get_job[0]->model_no,
                                            'quantity' => (int)$scan_qr[3],
                                            'from_location' => 0,
                                            'location_id' => $request->post('location_code')->toInt(),
                                            'created_at' => date('Y-m-d'),
                                            'created_by' => $login['id']
                                        );
    
                                        $db->insert($table_sac,$save_tran);
    
                                        $save_stock = array(
                                            'id' => NULL,
                                            'reference' => 0,
                                            'job_id' => $get_job[0]->id,
                                            'serial_number' => $scan_qr[4],
                                            'material_id' => $get_job[0]->model_no,
                                            'quantity' => (int)$scan_qr[3],
                                            'actual_quantity' => (int)$scan_qr[3],
                                            'location_id' => $request->post('location_code')->toInt(),
                                            'inbound_date' => date('Y-m-d H:i:s'),
                                            'created_at' => date('Y-m-d'),
                                            'created_by' => $login['id']
                                        );

                                        $table = $model->getTableName('inventory_stock');

                                        $db->insert($table,$save_stock);

                                        $ret['serial_number']='';
                                        $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'product-transferwh', 
                                        'po' => $get_job[0]->purchase_order,
                                        'job' =>  $get_job[0]->id,
                                        'delivery' => $get_job[0]->delivery_date, 
                                        'material_name' => $get_job[0]->material_name_en,
                                        'location_code' => $request->post('location_code')->toInt(),
                                        'status' => 1,'time' => date('H-i-s'))); 
                                        $ret['message'] = Language::get('Saved successfully');
                                        $ret['serial_number']='';
                                        $request->removeToken();
                                    }
    
                                } else {
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Finished goods Not Match In Job Order');
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