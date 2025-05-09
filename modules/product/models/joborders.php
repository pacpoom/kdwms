<?php

namespace product\joborders;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Number;

/**
 * module=inventory-customer
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model{

    protected $table ='material';
    private $category;

    public static function get($id) {
        return static::createQuery()
        ->select('T1.id','T1.material_number','T1.material_name_en','T2.unit')
        ->from('material T1')
        ->join('unit T2','LEFT',array('T1.unit','T2.id'))
        ->where(array('T1.material_type','!=',$id))
        ->order('material_number')
        ->execute();
    }

    public static function getBom($id){
        return static::createQuery()
        ->select('id','material_id','usage')
        ->from('bom')
        ->where(array('model_no',$id))
        ->order('material_id')
        ->execute();
    }

    public static function getJob($id){

        return static::createQuery()
        ->select('id','job_no')
        ->from('job_order')
        ->where(array('job_no',$id))
        ->execute();
    }

    public static function getJob_d($id){

        return static::createQuery()
        ->select('id','job_id','material_id','quantity_req','quantity_stock')
        ->from('job_order_d')
        ->where(array('job_id',$id))
        ->order('material_id')
        ->execute();
    }

    public static function getBox($id){
        return static::createQuery()
        ->select('id','serial_number','material_id','actual_quantity','inbound_date')
        ->from('inventory_stock')
        ->where(array(array('material_id',$id),array('actual_quantity','!=',0)))
        ->order('inbound_date')
        ->execute();
    }

    public static function get_currency(){

        return static::createQuery()
        ->select('id','currency')
        ->from('currency')
        ->order('currency')
        ->execute();
    }

    public function submit(Request $request){
        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('job_order');
        $table_d = $model->getTableName('job_order_d');
        $table_req = $model->getTableName('job_order_req');
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            try {
                
                if ($request->post('model')->toInt() == 0) {
                    $ret['ret_model'] = 'Please fill in';
                } elseif ($request->post('plan')->toInt() == 0) {
                    $ret['ret_plan'] = 'Please fill in';
                } elseif ($request->post('po')->topic() == '') {
                    $ret['ret_po'] = 'Please fill in';
                } else {

                    $order = \Index\Number\Model::get(0,'JOB_NO', $table, 'job_no');

                    $save = array(
                        'id' => NULL,
                        'job_no' => $order,
                        'status' => 1,
                        'model_no' => $request->post('model')->toInt(),
                        'production_date' => $request->post('production_date')->date(),
                        'plan' => $request->post('plan')->toInt(),
                        'total_production' => 0,
                        'total_ng' => 0,
                        'finished_date' => '0000-00-00',
                        'purchase_order' => $request->post('po')->topic(),
                        'delivery_date' => $request->post('production_date')->date(),
                        'machine_id' => 1,
                        'created_at' => date('Y-m-d'),
                        'created_by' => $login['id']
                    );

                    $db->insert($table,$save);

                    $job_id = \product\joborders\Model::getJob($order);
                    
                    $bom = \product\joborders\Model::getBom($request->post('model')->toInt());

                    foreach ($bom as $item){

                        $usage = $item->usage;
                        $plan_qty = $request->post('plan')->toInt();

                        $insert_bom = array(
                            'id' => NULL,
                            'job_id' => $job_id[0]->id,
                            'material_id' => $item->material_id,
                            'quantity_req' => (float)$usage * (float)$plan_qty,
                            'quantity_stock' => 0,
                            'quantity_pick' => 0,
                            'created_at' => date('Y-m-d'),
                            'created_by' => $login['id']
                        );

                        $db->insert($table_d,$insert_bom);
                    }

                    $get_bom = \product\joborders\Model::getJob_d($job_id[0]->id);

                    foreach ($get_bom as $item) {
                        $qty_req = 0 ;
                        $qty_pick = 0;

                        $qty_req = $item->quantity_req;
                        $qty_pick = $item->quantity_stock;

                        $get_item = \product\joborders\Model::getBox($item->material_id);

                        foreach ($get_item as $item1){

                            if ($qty_req > $qty_pick) {

                                $mat_req = array();

                                $mat_req = array(
                                    'id' => NULL,
                                    'job_id' => $item->job_id,
                                    'inventory_id' => $item1->id,
                                    'pick_flg' => 0,
                                    'pick_qty' => 0,
                                    'created_at' => date('Y-m-d'),
                                    'created_by' => $login['id']
                                );

                                $db->insert($table_req,$mat_req);

                                $qty_pick += (float)$item1->actual_quantity;

                            } else {
                                break;
                            }
                        }

                        $job_update = array();

                        $job_update = array(
                            'quantity_stock' => $qty_pick
                        );

                        $db->update($table_d,array('id',$item->id),$job_update);

                    }

                    $ret['alert'] = Language::replace('Successfully Create Job Order No. :count', array(':count' => $order));
                    $ret['location'] = 'reload';
                    $request->removeToken();    
                }
                
            } catch (\Kotchasan\InputItemException $e){
                $ret['alert'] = $e->getMessage();
            }
        }
        echo json_encode($ret);
    }
}