<?php

namespace wms\printtruck;

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

    protected $table ='location';
    private $category;

    public static function getTruck($so){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.truck_flg',1);

        return static::createQuery()
        ->select('T1.id','T1.truck_id')
        ->from('pallet_log T1')
        ->where($where)
        ->order('T1.id')
        ->toArray()
        ->execute();

    }

    public static function getConfirm($so,$truck){

        $where = array();
        $where[] = array('T1.sale_order',$so);
        $where[] = array('T1.truck_id',$truck);

        return static::createQuery()
        ->select('T1.confirm_flg')
        ->from('delivery_order T1')
        ->where($where)
        ->groupBy('T1.confirm_flg')
        ->execute();

    }

    public function submit(Request $request){
        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('location');

        
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            try {

                if ($request->post('truck')->toString() == '') {
                    $ret['truck']='';
                    $ret['fault'] = Language::get('Please Select Truck !!');
                    $request->removeToken(); 
                } else {

                    $check = \wms\printtruck\Model::getConfirm($request->post('so')->toString(),$request->post('truck')->toString());

                    if ($check == false) {
                        $ret['truck']='';
                        $ret['fault'] = Language::get('Truck Fail !!');
                    } elseif ($check[0]->confirm_flg == 0) {
                        $seq = \wms\pallet\Model::getSeq(2);
                        $number = $seq[0]->seq_id;
                        $number++;
    
                        $update = array(
                            'confirm_flg' => 1,
                            'confirm_date' => date('Y-m-d H:i:s'),
                            'running_no' => $number
                        );
    
                        $where = array();
                        $where[] = array('sale_order',$request->post('so')->toString());
                        $where[] = array('truck_id',$request->post('truck')->toString());
                        $where[] = array('running_no',0);
    
                        $table = $model->getTableName('delivery_order');
                        $db->update($table,$where,$update);
    
                        $update = array(
                            'seq_id' => $number
                        );
    
                        $where = array();
                        $where[] = array('id',2);
    
                        $table = $model->getTableName('seq');
                        $db->update($table,$where,$update);
                    }

                    $ret['alert'] = Language::get('Saved successfully');
                    $ret['location'] = 'reload';
                    $ret['open'] = 'https://sail.anjinyk.co.th/pdf/kd-delivery-confirm?sale_order='. $request->post('so')->toString() .'&truck_id='. $request->post('truck')->toString() .'';
                    $request->removeToken();
                }
            } catch (\Kotchasan\InputItemException $e){
                $ret['alert'] = $e->getMessage();
            }
        }
        echo json_encode($ret);
    }
}