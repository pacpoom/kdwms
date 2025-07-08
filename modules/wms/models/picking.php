<?php

namespace wms\picking;

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
        $where[] = array('sale_order',$so);

        return static::createQuery()
        ->select('status','material_number',SQL::SUM('planed_quantity','plan_qty'),'ship_qty','ship_qty diff')
        ->from('sale_order')
        ->where($where)
        ->groupBy('status','material_number','ship_qty')
        ->order('status','material_number');

    }

    public static function checkSer($id){
        return static::createQuery()
        ->select('T1.id','T1.serial_number','T1.quantity','T1.actual_quantity','T1.material_id','T1.location_id','T2.material_number','T2.material_name_en','T3.location_code','T4.container')
        ->from('inventory_stock T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->join('packing_list T4','LEFT',array('T1.serial_number','T4.box_id'))
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

    public static function get_total($id){

        $where = array();
        $where[] = array('sale_order',$id);

        return static::createQuery()
        ->select(sql::COUNT('id','total'))
        ->from('delivery_order')
        ->where($where)
        ->execute();

    }

    public static function get_actual($id,$pallet){

        $where = array();
        $where[] = array('sale_order',$id);
        $where[] = array('actual_id','!=',0);
        $where[] = array('pallet_no',$pallet);

        return static::createQuery()
        ->select(sql::COUNT('id','total'))
        ->from('delivery_order')
        ->where($where)
        ->execute();

    }

    public static function GetBoxID($box){

        $where = array();
        $where[] = array('T1.serial_number',$box);

        return static::createQuery()
        ->select('T1.id','T1.reference','T1.serial_number','T1.material_id','T4.material_number','T1.actual_quantity','T1.location_id')
        ->from('inventory_stock T1')
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->join('material T4','LEFT',array('T1.material_id','T4.id'))
        ->where($where)
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

    public static function GetQty($so,$material){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.material_number',$material);

        return static::createQuery()
        ->select(SQL::SUM('T1.quantity','quantity'))
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

                            $get_total = \wms\picking\Model::get_total($request->request('so')->toString());
                            $get_actual = 0;

                            $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-picking',
                            'so' => $request->request('so')->toString(),
                            'status' => 1,
                            'total_quantity' => $get_total[0]->total,
                            'actual_quantity' =>0,
                            'pallet' => '','pallets' => 1 )); 
                            $request->removeToken();

                        }

                    } elseif ($request->request('status')->toInt() == 1) {

                        if ($request->request('pallet')->toString() =='') {
                            $ret['pallet']='';
                            $ret['fault'] = Language::get('Please Fill Pallet ID !!');
                            $request->removeToken(); 
                        } else {

                            $pallet = \wms\picking\Model::GetPallet($request->post('pallet')->toString());

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

                                $get_total = \wms\picking\Model::get_total($request->request('so')->toString());
                                $get_actual = \wms\picking\Model::get_actual($request->request('so')->toString(),$pallet[0]->location_code);

                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-picking',
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
                        
                        if ($request->request('pallet')->toString() =='') {
                            $ret['pallet']='';
                            $ret['fault'] = Language::get('Please Fill Pallet ID !!');
                            $request->removeToken(); 
                        } else {

                            $pallet = \wms\picking\Model::GetPallet($request->post('pallet')->toString());
                            
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
                        
                                if (count($scan_qr) <= 5) {
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Please Scan QR Code');
                                    $request->removeToken();
                                } else {
        
                                    $checkBox = \wms\picking\Model::GetBoxID($scan_qr[4]);
        
                                    if ($checkBox == false) {
                                        $ret['serial_number']='';
                                        $ret['fault'] = Language::get('Box ID No Data !!');
                                        $request->removeToken();
                                    } elseif ($checkBox[0]->actual_quantity == 0) {
                                        $ret['serial_number']='';
                                        $ret['fault'] = Language::get('Box already Ship Out !!');
                                        $request->removeToken();
                                    } else {
        
                                        $checkSo = \wms\picking\Model::GetDetailSo($request->post('so')->toString(),$checkBox[0]->material_number);
        
                                        if ($checkSo == true) {
                                            $total_ship = $checkSo[0]->plan_qty - $checkSo[0]->ship_qty;
                                        }
                                        
                                        if ($checkSo == false) {
                                            $ret['serial_number']='';
                                            $ret['fault'] = Language::get('Material Not Match SO!!');
                                            $request->removeToken();
                                        } elseif ($checkSo[0]->ship_qty >= $checkSo[0]->plan_qty) {
                                            $ret['serial_number']='';
                                            $ret['fault'] = Language::get('Shipped Full !!');
                                            $request->removeToken();
                                        } elseif ($checkBox[0]->actual_quantity > $total_ship) {
                                            $ret['serial_number']='';
                                            $ret['fault'] = Language::get('Over Ship Quantity !!');
                                            $request->removeToken();
                                        } else {
        
                                            $ship = $checkSo[0]->ship_qty + $checkBox[0]->actual_quantity;
        
                                            $update = array(
                                                'ship_qty' => $ship
                                            );
        
                                            $where = array();
                                            $where[] = array('sale_order',$request->post('so')->toString());
                                            $where[] = array('material_number',$checkBox[0]->material_number);
        
                                            $table = $model->getTableName('sale_order');
                                            $db->update($table,$where,$update);
        
                                            if ($checkSo[0]->plan_qty == $ship) {
                                                
                                                $update = array(
                                                    'status' => 1
                                                );
            
                                                $where = array();
                                                $where[] = array('sale_order',$request->post('so')->toString());
                                                $where[] = array('material_number',$checkBox[0]->material_number);
            
                                                $table = $model->getTableName('sale_order');
                                                $db->update($table,$where,$update);
                                            }
        
                                            $checkDetail = \wms\picking\Model::GetSo($request->post('so')->toString(),$checkBox[0]->id);
        
                                            $check_so = \wms\picking\Model::GetSo_detail($request->post('so')->toString());
        
                                            if ($checkDetail == false) {
        
                                                $check_id = \wms\picking\Model::GetSoId($request->post('so')->toString(),$checkBox[0]->material_number);
        
                                                if ($check_id == false) {
        
                                                    $insert = array(
                                                        'id' => NULL,
                                                        'sale_order_id' => $check_so[0]->id,
                                                        'sale_order' => $request->post('so')->toString(),
                                                        'customer_code' => $check_so[0]->customer_code,
                                                        'customer_name' => $check_so[0]->customer_name,
                                                        'material_number' => $checkBox[0]->material_number,
                                                        'inventory_id' => $checkBox[0]->id,
                                                        'actual_id' => $checkBox[0]->id,
                                                        'quantity' => $checkBox[0]->actual_quantity,
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
                                                        'actual_id' => $checkBox[0]->id,
                                                        'quantity' => $checkBox[0]->actual_quantity,
                                                        'pallet_no' => $pallet[0]->location_code,
                                                        'ship_date' => date('Y-m-d H:i:s'),
                                                    );
            
                                                    $where = array();
                                                    $where[] = array('sale_order',$request->post('so')->toString());
                                                    $where[] = array('inventory_id',$check_id[0]->inventory_id);
                
                                                    $table = $model->getTableName('delivery_order');
                                                    $db->update($table,$where,$update);
            
                                                }
                        
        
                                            } elseif ($checkDetail[0]->actual_id == 0) {
        
                                                $update = array(
                                                    'actual_id' => $checkBox[0]->id,
                                                    'quantity' => $checkBox[0]->actual_quantity,
                                                    'ship_date' => date('Y-m-d H:i:s'),
                                                    'pallet_no' => $pallet[0]->location_code
                                                );
        
                                                $where = array();
                                                $where[] = array('sale_order',$request->post('so')->toString());
                                                $where[] = array('inventory_id',$checkBox[0]->id);
            
                                                $table = $model->getTableName('delivery_order');
                                                $db->update($table,$where,$update);
        
                                            }
        
                                            $update = array(
                                                'status' => 2
                                            );
        
                                            $where = array();
                                            $where[] = array('id',$check_so[0]->id);
        
                                            $table = $model->getTableName('sale_order_status');
                                            $db->update($table,$where,$update);
        
        
                                            $update = array(
                                                'actual_quantity' => 0
                                            );
        
                                            $where = array();
                                            $where[] = array('id',$checkBox[0]->id);
        
                                            $table = $model->getTableName('inventory_stock');
                                            $db->update($table,$where,$update);
        
                                            $save_tran = array(
                                                'id' => NULL,
                                                'transaction_date' => date("Y-m-d H:i:s"),
                                                'transaction_type' => 'Shipped',
                                                'reference' => $checkBox[0]->reference,
                                                'serial_number' => $scan_qr[4],
                                                'material_id' => $checkBox[0]->material_id,
                                                'quantity' => -(int)$checkBox[0]->actual_quantity,
                                                'from_location' => 0,
                                                'location_id' => $checkBox[0]->location_id,
                                                'sale_id' => $check_so[0]->id,
                                                'pallet_id' => $pallet[0]->id,
                                                'created_at' => date('Y-m-d'),
                                                'created_by' => $login['id']
                                            );
        
                                            $table = $model->getTableName('transaction');
        
                                            $db->insert($table,$save_tran);
        
                                            $get_total = \wms\picking\Model::get_total($request->request('so')->toString());
                                            $get_actual = \wms\picking\Model::get_actual($request->request('so')->toString(),$pallet[0]->location_code);
        
                                            $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-picking',
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