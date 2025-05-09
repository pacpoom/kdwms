<?php

namespace wms\sale;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='location';

    public static function toDataTable($params){
        
        $where = array();
        $params = array();
            if ($params['status'] == 0) {
                $where[] = array('T1.status',0);
            } elseif ($params['status'] == 1){
                $where[] = array('T1.status',1);
                
                if (empty($params['so'])) {
                    if (!empty($params['from'])){
                        $strNewDate = date('Ymd', strtotime($params['from']));
                        $where[] = array(sql::DATE('T1.delivery_date'),'>=',$strNewDate);
                    } else {
                        $strNewDate = date('Ymd');
                        $where[] = array(sql::DATE('T1.delivery_date'),'>=',$strNewDate);
                    }
        
                    if (!empty($params['to'])){
                        $strNewDate = date('Ymd', strtotime($params['to']));
                        $where[] = array(sql::DATE('T1.delivery_date'),'<=',$strNewDate);
                    } else {
                        $strNewDate = date('Ymd');
                        $where[] = array(sql::DATE('T1.delivery_date'),'<=',$strNewDate);
                    }
                }

            } elseif ($params['status'] == 2) {

                if (empty($params['so'])) {
                    if (!empty($params['from'])){
                        $strNewDate = date('Ymd', strtotime($params['from']));
                        $where[] = array(sql::DATE('T1.delivery_date'),'>=',$strNewDate);
                    } else {
                        $strNewDate = date('Ymd');
                        $where[] = array(sql::DATE('T1.delivery_date'),'>=',$strNewDate);
                    }
        
                    if (!empty($params['to'])){
                        $strNewDate = date('Ymd', strtotime($params['to']));
                        $where[] = array(sql::DATE('T1.delivery_date'),'<=',$strNewDate);
                    } else {
                        $strNewDate = date('Ymd');
                        $where[] = array(sql::DATE('T1.delivery_date'),'<=',$strNewDate);
                    }
                }

            }

            if (!empty($params['so'])) {
                $where[] = array('T1.sale_order',$params['so']);
            } 

            if (!empty($params['customer'])) {
                $where[] = array('T1.customer_code',$params['customer']);
            }

            if (!empty($params['from'])){
                $strNewDate = date('Ymd', strtotime($params['from']));
                $where[] = array(sql::DATE('T1.delivery_date'),'>=',$strNewDate);
            }

            if (!empty($params['to'])){
                $strNewDate = date('Ymd', strtotime($params['to']));
                $where[] = array(sql::DATE('T1.delivery_date'),'<=',$strNewDate);
            }
        
        return static::createQuery()
        ->select('T1.id','T1.status','T1.sale_order','T1.delivery_date','T2.customer_code','T2.customer_name','T1.ship_type','T1.delivery_type')
        ->from('sale_order_status T1')
        ->join('customer_master T2','LEFT',array('T1.customer_id','T2.id'))
        ->where(array('T1.status','!=',4))
        ->order('T1.id');
    }

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
                array('id', $id)
            ));

            return $search;
        }

    }

    
    public function action(Request $request)
    {
        $ret = array();
        // session, referer, can_manage_inventory, ไม่ใช่สมาชิกตัวอย่าง
        
        if ($request->initSession() && $request->isReferer() &&  $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_manage_inventory')) {
                // รับค่าจากการ POST
                $action = $request->post('action')->toString(); 
                // Database
                $db = $this->db();
                // id ที่ส่งมา
                if ($action ==='addlocation'){

                    $index = \wms\location\Model::get($request->post('id')->toInt());

                    $ret['modal'] = Language::trans(\wms\locations\View::create()->render($index,$login));
                    
                } elseif ($action ==='export') {

                    $params = $request->getParsedBody();
                    $params['module'] = 'wms-export';
                    $ret['location'] = WEB_URL.'export.php?'.http_build_query($params).'&type=stock&amp;';

                } elseif ($action ==='tag') {

                    //$index = \wms\location\Model::get($request->post('id')->toInt());
    
                    $ret['modal'] = Language::trans(\wms\pallet\View::create()->render($request->post('id')->toString()));

                } elseif ($action === 'truck') {

                    $ret['modal'] = Language::trans(\wms\printtruck\View::create()->render($request->post('id')->toString()));
                }
                 else{
                    if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->filter('0-9,'), $match)) {
                       
                        if ($action === 'delete') {

                            //var_dump('delete');
                            // ลบ
                            $db->delete($this->getTableName('location'), array('id', $match[1]), 0); 
                            // log
                            \Index\Log\Model::add(0, 'location', 'Delete', '{LNG_Delete} {LNG_Inventory} ID : '.implode(', ', $match[1]), $login['id']);
                            // reload
                            $ret['location'] = 'reload';
    
                        } elseif ($action ==='export'){
    
                            $params = $request->getParsedBody();
                            $params['module'] = 'wms-export';
                            $ret['location'] = WEB_URL.'export.php?'.http_build_query($params).'&type=stock&amp;';
    
                        } 
                    }
                }

            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }
}