<?php

namespace wms\return;

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

    public static function GetBoxID($box){

        $where = array();
        $where[] = array('T1.serial_number',$box);

        return static::createQuery()
        ->select('T1.id','T1.serial_number','T1.material_id','T4.material_number','T1.actual_quantity','T1.location_id')
        ->from('inventory_stock T1')
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->join('material T4','LEFT',array('T1.material_id','T4.id'))
        ->where($where)
        ->execute();
    }

    public static function checkSerid($id){
        return static::createQuery()
        ->select('id')
        ->from('inventory_stock')
        ->where(array('serial_number',$id))
        ->first();
    }

    public static function checkSerSO($id){
        return static::createQuery()
        ->select('id')
        ->from('inventory_stock')
        ->where(array('serial_number',$id))
        ->first();
    }

    public static function GetSoId($box_id){

        $where = array();
        $where[] = array('T1.actual_id',$box_id);

        return static::createQuery()
        ->select('T1.id','T1.sale_order_id','T1.sale_order','T1.material_number','T1.actual_id','T1.inventory_id','T1.quantity','T1.truck_id','T1.pallet_no','T2.truck_flg')
        ->from('delivery_order T1')
        ->join('pallet_log T2','LEFT',array('T1.pallet_no','T2.location_code'))
        ->where($where)
        ->order('T1.id')
        ->execute();
    }

    public static function GetSoSum($so,$mat){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.material_number',$mat);

        return static::createQuery()
        ->select('T1.sale_order','T1.material_number',SQL::SUM('T1.quantity','qty'))
        ->from('delivery_order T1')
        ->where($where)
        ->groupBy('T1.sale_order','T1.material_number')
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

                    $scan_qr = explode("_",$request->post('serial_number')->topic());

                    if (count($scan_qr) >= 5) {
                        $get = \wms\return\Model::GetBoxID($scan_qr[4]);
                       // var_dump($get);
                        if ($get == false) {
                            $ret['serial_number']='';
                            $ret['fault'] = Language::get('No Data !!');
                            $request->removeToken();
                        } elseif ($get[0]->actual_quantity > 0) {
                            $ret['serial_number']='';
                            $ret['fault'] = Language::get('Quantity Is Not Zero !!');
                            $request->removeToken();
                        } else {

                            $get_id = \wms\return\Model::GetSoId($get[0]->id);

                            if ($get_id == false) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('This Box Don,t Picking !!');
                                $request->removeToken();
                            } elseif ($get_id[0]->truck_flg == 1) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('This Box already truck !!');
                                $request->removeToken();
                            } else {

                                $get_sum = \wms\return\Model::GetSoSum($get_id[0]->sale_order,$get_id[0]->material_number);

                                if ($get_sum == false) {
                                    $ret['serial_number']='';
                                    $ret['fault'] = Language::get('Error Code 003 !!');
                                    $request->removeToken();
                                } else {

                                    $db->delete($this->getTableName('delivery_order'), array('id', $get_id[0]->id), 0);

                                    $get_sum1 = \wms\return\Model::GetSoSum($get_id[0]->sale_order,$get_id[0]->material_number);

                                    if ($get_sum1 == false) {
                                        $update = array(
                                            'ship_qty' => 0
                                        );
                                    } else {
                                        $update = array(
                                            'ship_qty' => (int)$get_sum1[0]->qty
                                        );
                                    }
                                   

                                    $where = array();
                                    $where[] = array('sale_order',$get_id[0]->sale_order);
                                    $where[] = array('material_number',$get_id[0]->material_number);

                                    $table = $model->getTableName('sale_order');
                                    $db->update($table,$where,$update);            

                                    $update = array(
                                        'actual_quantity' => $get_id[0]->quantity,
                                        'location_id' => 2283
                                    );

                                    $where = array();
                                    $where[] = array('id',$get[0]->id);

                                    $table = $model->getTableName('inventory_stock');
                                    $db->update($table,$where,$update);

                                    $check_so = \wms\picking\Model::GetSo_detail($get_id[0]->sale_order);
                                    $pallet = \wms\picking\Model::GetPallet($get_id[0]->pallet_no);

                                    $save_tran = array(
                                        'id' => NULL,
                                        'transaction_date' => date("Y-m-d H:i:s"),
                                        'transaction_type' => 'Return',
                                        'reference' => $get[0]->id,
                                        'serial_number' => $scan_qr[4],
                                        'material_id' => $get[0]->material_id,
                                        'quantity' => (int)$get_id[0]->quantity,
                                        'from_location' => $get[0]->location_id,
                                        'location_id' => 2283,
                                        'sale_id' => $check_so[0]->id,
                                        'pallet_id' => $pallet[0]->id,
                                        'created_at' => date('Y-m-d'),
                                        'created_by' => $login['id']
                                    );

                                    $table = $model->getTableName('transaction');

                                    $db->insert($table,$save_tran);

                                    $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-return',
                                    'so' => $get_id[0]->sale_order,
                                    'actual_quantity' => (int)$get_id[0]->quantity,
                                    'pallet' => '','pallets' => 1 )); 

                                    $ret['serial_number']='';
                                    $ret['message'] = Language::get('Saved successfully');
                                    $request->removeToken();
                                }

                            }

                        }
                    } else {
                        $ret['serial_number']='';
                        $ret['fault'] = Language::get('Please Scan QR Code');
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