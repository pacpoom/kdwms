<?php

namespace wms\moveall;

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

    public static function checkSer($id){
        return static::createQuery()
        ->select('T1.id','T1.reference','T2.container','T1.serial_number','T1.actual_quantity','T1.location_id','T1.material_id')
        ->from('inventory_stock T1')
        ->join('packing_list T2','LEFT',array('T1.serial_number','T2.box_id'))
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->where(array(array('T3.location_code',$id),array('actual_quantity','!=',0)))
        ->execute();
    }

    public static function sumstock($id){
        return static::createQuery()
        ->select('T1.location_id',sql::COUNT('T1.id','count_id'),sql::SUM('T1.actual_quantity','qty'))
        ->from('inventory_stock T1')
        ->join('location T2','LEFT',array('T1.location_id','T2.id'))
        ->where(array(array('T2.location_code',$id),array('T1.actual_quantity','!=',0)))
        ->groupBy('T1.location_id')
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

                    if ($request->post('location_from')->toString() == '') {
                        $ret['serial_number']='';
                        $ret['ret_location_from'] = 'Please fill in';
                    } else {
                        if (($request->post('status')->toInt() == 0)) {

                            $get_location = \wms\receive\Model::getLocation($request->post('location_from')->toString());

                            $total_sum = \wms\moveall\Model::sumstock($request->post('location_from')->toString());
                            
                            if ($total_sum == true) {
                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-moveall', 
                                'total_box' => isset($total_sum[0]->count_id) ? $total_sum[0]->count_id : 0,
                                'total_qty' => isset($total_sum[0]->qty) ? $total_sum[0]->qty : 0,
                                'location' => $get_location[0]->location_code,
                                'status' => 1,'time' => date('H-i-s')));
    
                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken();
                            } else {
                                $ret['location_from'] = '';
                                $ret['fault'] = Language::get('Location is Empty');
                                $request->removeToken();
                            }
                 
                        } else {

                            if ($request->post('location_to')->toString() == '') {
                                $ret['serial_number']='';
                                $ret['ret_location_to'] = 'Please fill in';
                            } else {

                                $get_location_code = \wms\receive\Model::getLocation($request->post('location_to')->toString());

                                
                                if ($get_location_code == false) {
                                    $ret['location_to'] = '';
                                    $ret['fault'] = Language::get('Location Incorrect !!');
                                } else {

                                    $Get_Location = \wms\moveall\Model::checkSer($request->post('location_from')->toString());

                                    foreach ($Get_Location as $item){
    
                                        $save = array(
                                            'location_id' => $get_location_code[0]->id,
                                        );
    
                                        $db->update($table,array('id', $item->id),$save);
                                                    
                                        $save_tran = array(
                                            'id' => NULL,
                                            'transaction_date' => date("Y-m-d H:i:s"),
                                            'transaction_type' => 'Location To Location',
                                            'reference' => $item->reference,
                                            'serial_number' => $item->serial_number,
                                            'material_id' => $item->material_id,
                                            'quantity' => $item->actual_quantity,
                                            'from_location' => $item->location_id,
                                            'location_id' => $get_location_code[0]->id,
                                            'created_at' => date('Y-m-d'),
                                            'created_by' => $login['id']
                                        );
    
                                        $db->insert($table_tran,$save_tran);
                                    }
    
                                    $total_sum = \wms\moveall\Model::sumstock($get_location_code[0]->id);
    
                                    $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-moveall', 
                                    'total_box' => isset($total_sum[0]->count_id) ? $total_sum[0]->count_id : 0,
                                    'total_qty' => isset($total_sum[0]->qty) ? $total_sum[0]->qty : 0,
                                    'location' => $get_location_code[0]->location_code,
                                    'status' => 0,'time' => date('H-i-s')));
        
                                    $ret['message'] = Language::get('Saved successfully');
                                    $ret['serial_number']='';
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
            $ret['fault'] = Language::get('Location Error');
        }
        echo json_encode($ret);
    }
}