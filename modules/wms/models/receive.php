<?php

namespace wms\receive;

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

        $where = array();
        $where[] = array('status',1);
        $where[] = array('container_type','KD');

        return static::createQuery()
        ->select('container')
        ->from('container')
        ->where($where)
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

    public static function Total_Box($id){
        return static::createQuery()
        ->select('id','total_box')
        ->from('container')
        ->where(array('container',$id))
        ->execute();
    }

    public static function Total_Receive($id){
        return static::createQuery()
        ->select('id','receive_box')
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

    public static function getLocation($id){
        return static::createQuery()
        ->select('id','location_code')
        ->from('location')
        ->where(array('location_code',$id))
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
        ->select('id','container','receive_flg','material_id','quantity')
        ->from('packing_list')
        ->where(array(array('container',$id),array('box_id',$box)))
        ->execute();
    }

    public static function getContainer($id,$location_id){

        $where = array();
        $where[] = array('container',$id);
        $where[] = array('receive_flg',0);
        
        return static::createQuery()
        ->select('case_number',sql::count('case_number','quantity'))
        ->from('packing_list')
        ->where($where)
        ->groupBy('case_number')
        ->order('case_number');

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
        ->select(sql::COUNT('case_number','qty'))
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

        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login)) {
                try {
                    // var_dump($request->post('status')->toInt());
                    if ($request->post('status')->toInt() == 0) {
                        if ($request->post('container')->toString() == ''){
                            $ret['serial_number']='';
                            $ret['ret_container'] = 'Please fill in';
                        } else {
                        
                            $get_job = \wms\receive\Model::getContainer_ID($request->post('container')->toString());

                            $total_box = \wms\receive\Model::Total_Box($get_job[0]->container);
                      
                            $total_receive = \wms\receive\Model::Total_Receive($get_job[0]->container);
                            

                            if ($get_job == false) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Please Contact IT Admin !!');
                                $request->removeToken();
                            } else {
                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-receive', 
                                'container' => $get_job[0]->container,
                                'total_box' => isset($total_box[0]->total_box)? $total_box[0]->total_box : 0,
                                'total_receive' => isset($total_receive[0]->receive_box) ? $total_receive[0]->receive_box : 0,
                                'status' => 1,'time' => date('H-i-s'))); 
    
                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken();
                            }
                        }
                    } elseif ($request->post('status')->toInt() == 1) {

                        if ($request->post('location_code')->toString() == '') {
                            $ret['serial_number']='';
                            $ret['ret_location_code'] = 'Please fill in';
                        } else {

                            $get_location = \wms\receive\Model::getLocation($request->post('location_code')->toString());
                            
                            if ($get_location == false) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Location Incorrect !!');
                            } else {

                                $get_job = \wms\receive\Model::getContainer_ID($request->post('container')->toString());

                                $total_box = \wms\receive\Model::Total_Box($get_job[0]->container);
                          
                                $total_receive = \wms\receive\Model::Total_Receive($get_job[0]->container);

                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-receive',
                                'location_id' => $get_location[0]->id, 
                                'container' => $get_job[0]->container,
                                'total_box' => isset($total_box[0]->total_box)? $total_box[0]->total_box : 0,
                                'total_receive' => isset($total_receive[0]->receive_box) ? $total_receive[0]->receive_box : 0,
                                'location' => $request->post('location_code')->toString(),
                                'status' => 2,'time' => date('H-i-s'))); 
    
                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken();
                            } 
                        }
                    } elseif ($request->post('status')->toInt() == 2) {

                        $scan_qr = explode("_",$request->post('serial_number')->topic());

                        if ($request->post('container')->toString() == ''){
                            $ret['serial_number']='';
                            $ret['ret_container'] = 'Please fill in';
                        } elseif ($request->post('location_code')->toString() == ''){
                            $ret['serial_number']='';
                            $ret['ret_location_code'] = 'Please fill in';
                        } elseif (count($scan_qr) <= 5){
                            $ret['serial_number']='';
                            $ret['fault'] = Language::get('Please Scan QR Code');
                            $request->removeToken();
                        } else {

                            $get_location = \wms\receive\Model::getLocation($request->post('location_code')->toString());

                            if ($get_location == false) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Location Incorrect !!');
                            } else {

                                $get_job = \wms\receive\Model::getContainerNO($request->post('container')->toString());
                                $checkBox = \wms\receive\Model::GetBoxID($get_job[0]->container,$scan_qr[4]);
                                
                                if ($checkBox == false) {
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Box ID Incorrect');
                                    $request->removeToken();
                                } else {
                                    if ($checkBox[0]->receive_flg == 1) {
                                        $ret['serial_number']='';
                                        $ret['fault'] = Language::get('Do not double scan');
                                        $request->removeToken();
                                    } else {
                                        $save = array(
                                            'receive_flg' => 1
                                        );
                                        $db->update($table,array('id',$checkBox[0]->id),$save);
                                
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
                                            'transaction_type' => 'Received(Box)',
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
                                        $get_Mat = \wms\receive\Model::getMaterial($checkBox[0]->material_id);
                                        $table = $model->getTableName('transaction');
                                        $db->insert($table,$save_tran);
                                        
                                        // $get_material = \wms\receive\Model::sumMaterial($get_job[0]->container);
                                        // $get_case = \wms\receive\Model::SumCase($get_job[0]->container);
                                        // $get_box = \wms\receive\Model::SumBox($get_job[0]->container);
                                        // $get_Qty = \wms\receive\Model::SumQty($get_job[0]->container);
                                        // $update_status = array(
                                        //     'receive_material' => isset($get_material[0]->qty) ? $get_material[0]->qty : 0,
                                        //     'receive_case' => isset($get_case[0]->qty) ? $get_case[0]->qty : 0,
                                        //     'receive_box' => isset($get_box[0]->qty) ? $get_box[0]->qty : 0,
                                        //     'receive_quantity' => isset($get_Qty[0]->qty) ? $get_Qty[0]->qty : 0,
                                        // );
                                        // $db->update($this->getTableName('container'),array('id',$get_job[0]->id),$update_status);
                                        $update = array(
                                            'cy_flg' => 0
                                        );
                                        $where[] = array('id',$checkBox[0]->id);
                                        $db->update($this->getTableName('packing_list'),$where,$update);
                                        $total_box = \wms\receive\Model::Total_Box($request->post('container')->toString());
                                        $total_receive = \wms\receive\Model::Total_Receive($request->post('container')->toString());
                                        
                                        $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-receive',
                                        'location_id' => $get_location[0]->id, 
                                        'container' => $get_job[0]->container,
                                        'total_box' => $total_box[0]->total_box,
                                        'total_receive' => $total_receive[0]->receive_box,
                                        'location' => $request->post('location_code')->toString(),
                                        'status' => 2,'time' => date('H-i-s')));
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