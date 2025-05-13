<?php

namespace wms\caset;

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

    public static function getList(){
        return static::createQuery()
        ->select('container')
        ->from('container')
        ->where(array('status',1))
        ->order('container')
        ->groupBy('container')
        ->execute();
    }

    public static function Container_NO($id){
        return static::createQuery()
        ->select('container')
        ->from('packing_list')
        ->where(array('id',$id))
        ->groupBy('container')
        ->execute();
    }

    public static function Total_case($id){
        return static::createQuery()
        ->select('id','total_case')
        ->from('container')
        ->where(array('container',$id))
        ->execute();
    }

    public static function Total_Receive($id){
        return static::createQuery()
        ->select('id','receive_case')
        ->from('container')
        ->where(array('container',$id))
        ->execute();
    }

    public static function getContainerID($id){
        return static::createQuery()
        ->select('container')
        ->from('packing_list')
        ->where(array('container',$id))
        ->groupBy('container')
        ->execute();
    }

    public static function getContainer_ID($id){
        return static::createQuery()
        ->select('container')
        ->from('packing_list')
        ->where(array('container',$id))
        ->groupBy('container')
        ->execute();
    }

    public static function getContainerNO($id){
        return static::createQuery()
        ->select('id','container')
        ->from('container')
        ->where(array('container',$id))
        ->execute();
    }

    public static function getMaterial($id){
        return static::createQuery()
        ->select('id','material_number','material_name_en')
        ->from('material')
        ->where(array('id',$id))
        ->execute();
    }

    public static function GetBoxID($id,$box){
        return static::createQuery()
        ->select('id','container','receive_flg','material_id','box_id','quantity')
        ->from('packing_list')
        ->where(array(array('container',$id),array('case_number',$box),array('receive_flg',0)))
        ->execute();
    }

    public static function getContainer($id,$location_id){
        return static::createQuery()
        ->select('T1.id','T1.serial_number','T3.material_number','T1.quantity','T4.location_code')
        ->from('inventory_stock T1')
        ->join('packing_list T2','LEFT',array('T1.reference','T2.id'))
        ->join('material T3','LEFT',array('T1.material_id','T3.id'))
        ->join('location T4','LEFT',array('T1.location_id','T4.id'))
        ->where(array(array('T1.location_id',$location_id),array('T2.container',$id)))
        ->order('T3.material_number');

    }

    public static function sumMaterial($id){
        return static::createQuery()
        ->select(sql::COUNT('material_id','qty'))
        ->from('packing_list')
        ->where(array(array('container',$id),array('receive_flg',1)))
        ->groupBy('material_id')
        ->execute();
    }

    public static function SumCase($id){
        return static::createQuery()
        ->select('case_number')
        ->from('packing_list')
        ->where(array(array('container',$id),array('receive_flg',1)))
        ->groupBy('case_number')
        ->execute();
    }

    public static function SumBox($id){
        return static::createQuery()
        ->select(sql::COUNT('box_id','qty'))
        ->from('packing_list')
        ->where(array(array('container',$id),array('receive_flg',1)))
        ->execute();
    }

    public static function SumQty($id){
        return static::createQuery()
        ->select(sql::SUM('quantity','qty'))
        ->from('packing_list')
        ->where(array(array('container',$id),array('receive_flg',1)))
        ->execute();
    }
   
    public function submit(Request $request){

        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('packing_list'); 
        $table_ccl = $model->getTableName('declaration');
        $table_wip = $model->getTableName('inventory_wip');
        $table_sac = $model->getTableName('transaction');
        $table_stock = $model->getTableName('inventory_stock');

        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login)) {
                try {
                    // var_dump($request->post('status')->toInt());
                    if ($request->post('status')->toInt() == 0) {
                        if ($request->post('container')->toString() == ''){
                            $ret['serial_number']='';
                            $ret['ret_container'] = 'Please fill in';
                        } else {
                            
                            $get_job = \wms\caset\Model::getContainer_ID($request->post('container')->toString());

                            $total_box = \wms\caset\Model::Total_case($get_job[0]->container);
                      
                            $total_receive = \wms\caset\Model::Total_Receive($get_job[0]->container);
                            
                            if ($get_job == false) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Please Contact IT Admin !!');
                                $request->removeToken();
                            } else {
                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-caset', 
                                'container' => $get_job[0]->container,
                                'total_box' => isset($total_box[0]->total_case)? $total_box[0]->total_case : 0,
                                'total_receive' => isset($total_receive[0]->receive_case) ? $total_receive[0]->receive_case : 0,
                                'status' => 1,'time' => date('H-i-s'))); 
    
                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken();
                            }
                        }
                    } elseif ($request->post('status')->toInt() == 1) {

                        if ($request->post('location_code')->toString() == '') {
                            $ret['location_code']='';
                            $ret['ret_location_code'] = 'Please fill in';
                        } else {
                            
                            $get_location = \wms\receive\Model::getLocation($request->post('location_code')->toString());

                            if ($get_location == false) {

                                $ret['location_code']='';
                                $ret['fault'] = Language::get('Location Incorrect !!');

                            } else {

                                $get_job = \wms\caset\Model::getContainer_ID($request->post('container')->toString());

                                $total_box = \wms\caset\Model::Total_case($get_job[0]->container);
                          
                                $total_receive = \wms\caset\Model::Total_Receive($get_job[0]->container);

                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-caset', 
                                'container' => $get_job[0]->container,
                                'total_box' => isset($total_box[0]->total_case)? $total_box[0]->total_case : 0,
                                'total_receive' => isset($total_receive[0]->receive_case) ? $total_receive[0]->receive_case : 0,
                                'location' => $get_location[0]->location_code,
                                'location_id' => $get_location[0]->id,
                                'status' => 2,'time' => date('H-i-s'))); 

                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken();

                            }
                        }

                    } elseif ($request->post('status')->toInt() == 2) {

                        if ($request->post('location_code')->toString() == '') {
                            $ret['serial_number']='';
                            $ret['ret_location_code'] = 'Please fill in';
                        } else {
                            
                            $get_location = \wms\receive\Model::getLocation($request->post('location_code')->toString());

                            if ($get_location == false) {

                                $ret['location_code']='';
                                $ret['fault'] = Language::get('Location Incorrect !!');

                            } else {

                                $scan_qr = explode("_",$request->post('serial_number')->topic());

                                if ($request->post('container')->toString() == ''){
                                    $ret['serial_number']='';
                                    $ret['ret_container'] = 'Please fill in';
                                } elseif (count($scan_qr) > 1){
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Please Scan Case Number');
                                    $request->removeToken();
                                } else {
        
        
                                    if ($scan_qr[0] == '') {
                                        $ret['serial_number']='';
                                        $ret['fault'] = Language::get('Please Scan Case Number');
                                        $request->removeToken();
                                    } else {
        
                                        $get_job = \wms\caset\Model::getContainerNO($request->post('container')->toString());
        
                                        $checkBox = \wms\caset\Model::GetBoxID($get_job[0]->container,$scan_qr[0]);
            
                                        if ($checkBox == false) {
                                            $ret['serial_number']='';
                                            $ret['fault'] = Language::get('Case No. Already Receive');
                                            $request->removeToken();
                                        } else {
                                            if ($checkBox[0]->receive_flg == 1) {
                                                $ret['serial_number']='';
                                                $ret['fault'] = Language::get('Do not double scan');
                                                $request->removeToken();
                                            } else {
            
                                                foreach ($checkBox as $row){
                                                    $save = array(
                                                        'receive_flg' => 1,
                                                    );
            
                                                    $db->update($table,array('id',$row->id),$save);
            
                                                    $save_stock = array(
                                                        'id' => NULL,
                                                        'reference' => $row->id,
                                                        'job_id' => 0,
                                                        'serial_number' => $row->box_id,
                                                        'material_id' => $row->material_id,
                                                        'quantity' => (int)$row->quantity,
                                                        'actual_quantity' => (int)$row->quantity,
                                                        'location_id' => $get_location[0]->id,
                                                        'inbound_date' => date('Y-m-d H:i:s'),
                                                        'allocate_flg' => 0,
                                                        'created_at' => date('Y-m-d'),
                                                        'created_by' => $login['id']
                                                    );
            
                                                    $db->insert($table_stock,$save_stock);
            
                                                    $save_tran = array(
                                                        'id' => NULL,
                                                        'transaction_date' => date("Y-m-d H:i:s"),
                                                        'transaction_type' => 'Received(By Case)',
                                                        'reference' => $row->id,
                                                        'serial_number' => $row->box_id,
                                                        'material_id' => $row->material_id,
                                                        'quantity' => (int)$row->quantity,
                                                        'from_location' => 0,
                                                        'location_id' => $get_location[0]->id,
                                                        'created_at' => date('Y-m-d'),
                                                        'created_by' => $login['id']
                                                    );
            
                                                    $db->insert($table_sac,$save_tran);
                                                }
            
                                                $insert_case = array(
                                                    'id' => NULL,
                                                    'container' => $get_job[0]->container,
                                                    'case_number' => $scan_qr[0],
                                                    'case_received' => date('Y-m-d H:i:s'),
                                                    'created_at' => date('Y-m-d'),
                                                    'created_by' => $login['id']
                                                );
            
                                                $db->insert($model->getTableName('container_case'),$insert_case);
            
                                                $update = array(
                                                    'cy_flg' => 0
                                                );
            
                                                $where[] = array('container',$get_job[0]->container);
                                                $where[] = array('case_number',$scan_qr[0]);
            
                                                $db->update($this->getTableName('packing_list'),$where,$update);
            
                                                                                              
                                                // $get_material = \wms\caset\Model::sumMaterial($get_job[0]->container);
                                                // $get_case = \wms\caset\Model::SumCase($get_job[0]->container);
                                                // $get_box = \wms\caset\Model::SumBox($get_job[0]->container);
                                                // $get_Qty = \wms\caset\Model::SumQty($get_job[0]->container);                            
            
                                                // $update_status = array(
                                                //     'receive_material' => isset($get_material[0]->qty) ? $get_material[0]->qty : 0,
                                                //     'receive_case' => isset($get_case) ? COUNT($get_case) : 0,
                                                //     'receive_box' => isset($get_box[0]->qty) ? $get_box[0]->qty : 0,
                                                //     'receive_quantity' => isset($get_Qty[0]->qty) ? $get_Qty[0]->qty : 0,
                                                // );
            
                                                // $db->update($this->getTableName('container'),array('id',$get_job[0]->id),$update_status);
            
                                                $total_box = \wms\caset\Model::Total_case($request->post('container')->toString());
            
                                                $total_receive = \wms\caset\Model::Total_Receive($request->post('container')->toString());
                                                
                                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-caset', 
                                                'container' => $get_job[0]->container,
                                                'total_box' => $total_box[0]->total_case,
                                                'total_receive' => $total_receive[0]->receive_case,
                                                'location' => $request->post('location_code')->toInt(),
                                                'location' => $get_location[0]->location_code,
                                                'location_id' => $get_location[0]->id,
                                                'status' => 2,'time' => date('H-i-s')));
            
                                                $ret['message'] = Language::get('Saved successfully');
                                                $ret['serial_number']='';
                                                $request->removeToken(); 
                                            }
                                        }
                                        
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