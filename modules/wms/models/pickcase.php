<?php

namespace wms\pickcase;

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

    public static function toDataTable($so){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.actual_id',0);

        return static::createQuery()
        ->select('T1.id','T2.serial_number','T5.case_number','T4.material_number','T2.actual_quantity','T3.location_code')
        ->from('delivery_order T1')
        ->join('inventory_stock T2','LEFT',array('T1.inventory_id','T2.id'))
        ->join('location T3','LEFT',array('T2.location_id','T3.id'))
        ->join('material T4','LEFT',array('T2.material_id','T4.id'))
        ->join('packing_list T5','LEFT',array('T2.reference','T5.id'))
        ->where($where)
        ->order('T3.location_code');

    }

    public static function checkSer($id){
        return static::createQuery()
        ->select('T1.id','T1.serial_number','T1.quantity','T1.actual_quantity','T1.material_id','T1.location_id','T2.material_number','T2.material_name_en','T3.location_code','T4.container')
        ->from('inventory_stock T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->join('packing_list T4','LEFT',array('T1.reference','T4.id'))
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

    public static function get_so(){

        $where = array();
        $where[] = array('status','!=',4);

        return static::createQuery()
        ->select('sale_order')
        ->from('sale_order_status')
        ->where($where)
        ->groupBy('sale_order')
        ->order('sale_order')
        ->toArray()
        ->execute();

    }

    public static function get_so_id($so,$material){

        $where = array();
        $where[] = array('sale_order',$so);
        $where[] = array('material_number',$material);

        return static::createQuery()
        ->select('material_number',sql::SUM('planed_quantity','planed_quantity'),'ship_qty')
        ->from('sale_order')
        ->where($where)
        ->groupBy('material_number','ship_qty')
        ->order('material_number')
        ->execute();

    }

    public static function get_total($id){

        $where = array();
        $where[] = array('sale_order',$id);

        return static::createQuery()
        ->select(sql::COUNT('id','total'))
        ->from('delivery_order')
        ->where($where)
        ->execute();

    }

    public static function get_actual($id){

        $where = array();
        $where[] = array('sale_order',$id);
        $where[] = array('actual_id','!=',0);

        return static::createQuery()
        ->select(sql::COUNT('id','total'))
        ->from('delivery_order')
        ->where($where)
        ->execute();

    }

    public static function GetBoxID($box){

        $where = array();
        $where[] = array('T5.case_number',$box);
        $where[] = array('T1.actual_quantity','!=',0);

        return static::createQuery()
        ->select('T1.id','T1.serial_number','T1.reference','T5.case_number','T1.material_id','T4.material_number','T1.actual_quantity','T1.location_id')
        ->from('inventory_stock T1')
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->join('material T4','LEFT',array('T1.material_id','T4.id'))
        ->join('packing_list T5','LEFT',array('T1.reference','T5.id'))
        ->where($where)
        ->execute();

    }

    public static function GetBoxSum($box){

        $where = array();
        $where[] = array('T5.case_number',$box);
        $where[] = array('T1.actual_quantity','!=',0);

        return static::createQuery()
        ->select('T5.case_number','T1.material_id','T4.material_number',sql::SUM('T1.actual_quantity','total_qty'))
        ->from('inventory_stock T1')
        ->join('material T4','LEFT',array('T1.material_id','T4.id'))
        ->join('packing_list T5','LEFT',array('T1.reference','T5.id'))
        ->where($where)
        ->groupBy('T5.case_number','T1.material_id','T4.material_number')
        ->order('T1.material_id')
        ->execute();

    }

    public static function GetDetailSo($so,$material){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.material_number',$material);

        return static::createQuery()
        ->select('T1.material_number',SQL::SUM('T1.planed_quantity','plan_qty'),'T1.ship_qty')
        ->from('sale_order T1')
        ->where($where)
        ->groupBy('T1.material_number','T1.ship_qty')
        ->execute();

    }

    public static function GetSo($so,$box){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.inventory_id',$box);
        $where[] = array('T1.actual_id',0);

        return static::createQuery()
        ->select('T1.id','T1.actual_id','T1.sale_order_id')
        ->from('delivery_order T1')
        ->where($where)
        ->execute();

    }

    public static function GetSoId($so,$mat){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.material_number',$mat);
        $where[] = array('T1.actual_id',0);

        return static::createQuery()
        ->select('T1.id','T1.actual_id','T1.inventory_id')
        ->from('delivery_order T1')
        ->where($where)
        ->order('T1.id')
        ->execute();

    }


    public static function GetSo_detail($so){

        $where = array();
        $where[] = array('T1.sale_order',$so);

        return static::createQuery()
        ->select('T1.id','T2.customer_code','T2.customer_name')
        ->from('sale_order_status T1')
        ->join('customer_master T2','LEFT',array('T1.customer_id','T2.id'))
        ->where($where)
        ->execute();

    }

    public static function GetPallet($pallet){

        $where = array();
        $where[] = array('T1.location_code',$pallet);

        return static::createQuery()
        ->select('T1.id','T1.location_code','T1.sale_order','T1.truck_flg')
        ->from('pallet_log T1')
        ->where($where)
        ->execute();

    }

        public static function check_receive($pallet){

        $where = array();
        $where[] = array('T1.case_number',$pallet);

        return static::createQuery()
        ->select('T1.id','T1.case_number')
        ->from('container_case T1')
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

                    if ($request->request('status')->toInt() == 0) {

                        if ($request->request('so')->toString() =='') {
                            $ret['so']='';
                            $ret['fault'] = Language::get('Please Select Sale Order !!');
                            $request->removeToken(); 
                        } else {

                            $get_total = \wms\pickcase\Model::get_total($request->request('so')->toString());
                            $get_actual = \wms\pickcase\Model::get_actual($request->request('so')->toString());

                            $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-pickcase',
                            'so' => $request->request('so')->toString(),
                            'status' => 1,
                            'total_quantity' => $get_total[0]->total,
                            'actual_quantity' => $get_actual[0]->total,
                            'pallet' => '','pallets' => 1 )); 
                            $request->removeToken();

                        }

                    } elseif ($request->request('status')->toInt() == 1) {

                        if  ($request->request('pallet')->toString() =='') {
                                $ret['pallet']='';
                                $ret['fault'] = Language::get('Please Fill Pallet ID !!');
                                $request->removeToken(); 
                        } else {
                            $pallet = \wms\pickcase\Model::GetPallet($request->post('pallet')->toString());
                            if ($pallet == false) {
                                $ret['pallet']='';
                                $ret['fault'] = Language::get('Pallet No. Incorrect');
                                $request->removeToken(); 
                            } elseif ($pallet[0]->sale_order != $request->post('so')->toString()) {
                                $ret['pallet']='';
                                $ret['fault'] = Language::get('Pallet No. And SO Not Match !!');
                                $request->removeToken();
                            } elseif ($pallet[0]->truck_flg == 1) {
                                $ret['pallet']='';
                                $ret['fault'] = Language::get('Pallet No. Already Confirm !!');
                                $request->removeToken();
                            } else {

                                $get_total = \wms\pickcase\Model::get_total($request->request('so')->toString());
                                $get_actual = \wms\pickcase\Model::get_actual($request->request('so')->toString());

                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-pickcase',
                                'so' => $request->request('so')->toString(),
                                'status' => 2,
                                'total_quantity' => $get_total[0]->total,
                                'actual_quantity' => $get_actual[0]->total,
                                'pallet' => $request->post('pallet')->toString(),
                                'pallets' => 0)); 

                                $ret['message'] = Language::get('Saved successfully');
                                $ret['serial_number']='';
                                $request->removeToken();
                            }
                        }

                    } elseif ($request->request('status')->toInt() == 2) {
                        
                        $pallet = \wms\pickcase\Model::GetPallet($request->post('pallet')->toString());

                        if ($pallet == false) {
                            $ret['pallet']='';
                            $ret['fault'] = Language::get('Pallet No. Incorrect');
                            $request->removeToken(); 
                        } elseif ($pallet[0]->sale_order != $request->post('so')->toString()) {
                            $ret['pallet']='';
                            $ret['fault'] = Language::get('Pallet No. And SO Not Match !!');
                            $request->removeToken();
                        } elseif ($pallet[0]->truck_flg == 1) {
                            $ret['pallet']='';
                            $ret['fault'] = Language::get('Pallet No. Already Confirm !!');
                            $request->removeToken();
                        } else {

                            $scan_qr = explode("_",$request->post('serial_number')->toString());
                            
                            $check_receive = \wms\pickcase\Model::check_receive($scan_qr[0]);

                            if ($check_receive == false) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Case Number No Data 10124 !!');
                                $request->removeToken();
                            } else {
    
                                $checkBox = \wms\pickcase\Model::GetBoxID($scan_qr[0]);
    
                                if ($checkBox == false) {
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Case Number No Data !!');
                                    $request->removeToken();
                                } else {
    
                                    $error = false;
                                    $error_code = 0;
    
                                    foreach ($checkBox As $item) {
                                        if ($item->actual_quantity == 0) {
                                            $error = true;
                                        }
                                    }
    
                                    if ($error == true) {
                                        $ret['serial_number']='';
                                        $ret['fault'] = Language::get('Case Number already Ship Out !!');
                                        $request->removeToken();
                                    } else {
    
                                        $checksum = \wms\pickcase\Model::GetBoxSum($scan_qr[0]);
    
                                        foreach ($checksum As $item) {
    
                                            $check_so = \wms\pickcase\Model::get_so_id($request->post('so')->toString(),$item->material_number);
                                        
                                            if ($check_so == false) {
                                                $error_code = 1;
                                                break;
                                            } else {
                                                $sum_qty = $check_so[0]->ship_qty + $item->total_qty;
                                                if ($sum_qty > $check_so[0]->planed_quantity) {
                                                    $error_code = 2;
                                                    break;
                                                } 
                                            }
                                            
                                        }
    
                                        if ($error_code == 1) {
                                            $ret['serial_number']='';
                                            $ret['fault'] = Language::get('Case Number Not Match SO !!');
                                            $request->removeToken();
                                        } elseif ($error_code == 2) {
                                            $ret['serial_number']='';
                                            $ret['fault'] = Language::get('Case Number Over Quantity !!');
                                            $request->removeToken();
                                        } else {
    
                                            foreach ($checkBox As $item) {
    
                                                $check_so = \wms\pickcase\Model::get_so_id($request->post('so')->toString(),$item->material_number);
                                               
                                                $sum_qty = $check_so[0]->ship_qty + $item->actual_quantity;
    
                                                if ($check_so[0]->planed_quantity == $sum_qty) {
                                            
                                                    $update = array(
                                                        'status' => 1,
                                                        'ship_qty' => $sum_qty,
                                                    );
                
                                                    $where = array();
                                                    $where[] = array('sale_order',$request->post('so')->toString());
                                                    $where[] = array('material_number',$item->material_number);
                
                                                    $table = $model->getTableName('sale_order');
                                                    $db->update($table,$where,$update);
                                                    
                                                } else {
                                                    $update = array(
                                                        'status' => 0,
                                                        'ship_qty' => $sum_qty,
                                                    );
                
                                                    $where = array();
                                                    $where[] = array('sale_order',$request->post('so')->toString());
                                                    $where[] = array('material_number',$item->material_number);
                
                                                    $table = $model->getTableName('sale_order');
                                                    $db->update($table,$where,$update);
                                                }
    
                                                $checkDetail = \wms\pickcase\Model::GetSo($request->post('so')->toString(),$item->id);
                                                $check_so = \wms\pickcase\Model::GetSo_detail($request->post('so')->toString());
    
                                                if ($checkDetail == false) {
    
                                                    $check_id = \wms\pickcase\Model::GetSoId($request->post('so')->toString(),$item->material_number);
    
                                                    if ($check_id == false) {
    
                                                        $insert = array(
                                                            'id' => NULL,
                                                            'sale_order_id' => $check_so[0]->id,
                                                            'sale_order' => $request->post('so')->toString(),
                                                            'customer_code' => $check_so[0]->customer_code,
                                                            'customer_name' => $check_so[0]->customer_name,
                                                            'material_number' => $item->material_number,
                                                            'inventory_id' => $item->id,
                                                            'actual_id' => $item->id,
                                                            'quantity' => $item->actual_quantity,
                                                            'ship_date' => date('Y-m-d H:i:s'),
                                                            'pallet_no' => $pallet[0]->location_code,
                                                            'truck_confirm' => 0,
                                                            'truck_confirm_date' => NULL,
                                                            'confirm_flg' => 0,
                                                            'confirm_date' => NULL,
                                                            'truck_id' => '',
                                                            'truck_date' => NULL,
                                                            'file_name' => '',
                                                            'running_no' => 0,
                                                            'created_at' => date('Y-m-d'),
                                                            'created_by' => $login['id']
                                                        );
            
                                                        $table = $model->getTableName('delivery_order');
                                                        $db->insert($table,$insert);
    
                                                    } else {
    
                                                        $update = array(
                                                            'actual_id' => $item->id,
                                                            'quantity' => $item->actual_quantity,
                                                            'ship_date' => date('Y-m-d H:i:s'),
                                                            'pallet_no' => $pallet[0]->location_code,
                                                        );
                
                                                        $where = array();
                                                        $where[] = array('sale_order',$request->post('so')->toString());
                                                        $where[] = array('inventory_id',$check_id[0]->inventory_id);
                    
                                                        $table = $model->getTableName('delivery_order');
                                                        $db->update($table,$where,$update);
    
                                                    }
    
                                                } else {
    
                                                    $update = array(
                                                        'actual_id' => $item->id,
                                                        'quantity' => $item->actual_quantity,
                                                        'ship_date' => date('Y-m-d H:i:s'),
                                                        'pallet_no' => $pallet[0]->location_code
                                                    );
            
                                                    $where = array();
                                                    $where[] = array('sale_order',$request->post('so')->toString());
                                                    $where[] = array('inventory_id',$item->id);
                
                                                    $table = $model->getTableName('delivery_order');
                                                    $db->update($table,$where,$update);
                                                }
    
                                                $update = array(
                                                    'actual_quantity' => 0
                                                );
            
                                                $where = array();
                                                $where[] = array('id',$item->id);
            
                                                $table = $model->getTableName('inventory_stock');
                                                $db->update($table,$where,$update);
    
                                                $update = array(
                                                'status' => 2
                                                );
        
                                                $where = array();
                                                $where[] = array('id',$check_so[0]->id);
        
                                                $table = $model->getTableName('sale_order_status');
                                                $db->update($table,$where,$update);

                                                $save_tran = array(
                                                    'id' => NULL,
                                                    'transaction_date' => date("Y-m-d H:i:s"),
                                                    'transaction_type' => 'Shipped By Case',
                                                    'reference' => $item->reference,
                                                    'serial_number' => $item->serial_number,
                                                    'material_id' => $item->material_id,
                                                    'quantity' => -(int)$item->actual_quantity,
                                                    'from_location' => 0,
                                                    'location_id' => $item->location_id,
                                                    'sale_id' => $check_so[0]->id,
                                                    'pallet_id' => $pallet[0]->id,
                                                    'created_at' => date('Y-m-d'),
                                                    'created_by' => $login['id']
                                                );
            
                                                $table = $model->getTableName('transaction');
            
                                                $db->insert($table,$save_tran);
                                            }
    
                                            $get_total = \wms\pickcase\Model::get_total($request->request('so')->toString());
                                            $get_actual = \wms\pickcase\Model::get_actual($request->request('so')->toString());
        
                                            $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-pickcase',
                                            'so' => $request->request('so')->toString(),
                                            'status' => 2,
                                            'total_quantity' => $get_total[0]->total,
                                            'actual_quantity' => $get_actual[0]->total,
                                            'pallet' => $pallet[0]->location_code,
                                            'pallets' => 0)); 
        
                                            $ret['serial_number']='';
                                            $ret['message'] = Language::get('Saved successfully');
                                            $request->removeToken();
    
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