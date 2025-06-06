<?php

namespace wms\export;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{


    public static function shipstock($params){
        $params = array();
        $where = array();
        $where[] = array('T1.actual_id','!=',0);
        $where[] = array('T1.truck_confirm',0);

        return static::createQuery()
        ->select('T1.id','T1.sale_order','T1.customer_code','T1.customer_name','T2.serial_number','T1.material_number','T1.quantity','T1.ship_date','T1.pallet_no')
        ->from('delivery_order T1')
        ->join('inventory_stock T2', 'LEFT',array('T2.id','T1.actual_id'))
        ->where($where)
        ->order('T1.sale_order','T1.material_number','T1.pallet_no')
        ->toArray()
        ->execute();

    }

    public static function containers($params) {

     $where = array();

        if ($params['status'] == 0) {
            $where[] = array('status',0);
        } elseif ($params['status'] == 1){
            $where[] = array('status',1);
        }
        
        if (!empty($params['from'])){
                $strNewDate = date('Y-m-d', strtotime($params['from']));
                $where[] = array(sql::DATE('delivery_date'),'>=',$strNewDate);
        } else {
                $strNewDate = date('Y-m-d', strtotime('-365 day'));
                $where[] = array(sql::DATE('delivery_date'),'>=',$strNewDate);
        } 
        
        if (!empty($params['to'])){
                $strNewDate = date('Y-m-d', strtotime($params['to']));
                $where[] = array(sql::DATE('delivery_date'),'<=',$strNewDate);
        } else {
                $strNewDate = date('Y-m-d');
                $where[] = array(sql::DATE('delivery_date'),'<=',$strNewDate);
        } 

        if (!empty($params['container'])) {
            $where[] = array('container',$params['container']);
        } 

        return static::createQuery()
        ->select('id','status','receive_date','year_lot','week_lot','lot_no','container_size','model','delivery_date','eta_date'
        ,'ata_date','container_type','container','container_bl','total_material','total_case','total_box','total_quantity'
        ,'receive_material','receive_case','receive_box','receive_quantity')
        ->from('container')
        ->where($where)
        ->order('delivery_date desc')
        ->toArray()
        ->execute();

    }

    public static function requisition($params){
        
        $where = array();

        if (!empty($params['from'])){
            $where[] = array(sql::DATE('T1.created_at'),'>=',$params['from']);
        } else {

            $strStartDate = date('Y-m-d 00:00:00');
            $strNewDate = date('Y-m-d 00:00:00', strtotime('-7 day', strtotime($strStartDate)));

            $where[] = array(sql::DATE('T1.created_at'),'>=',$strNewDate);
        }

        if (!empty($params['to'])){
            $where[] = array(sql::DATE('T1.created_at'),'<=',$params['to']);
        } else {
            $where[] = array(sql::DATE('T1.created_at'),'<=',date('Y-m-d 23:59:59'));
        }

        return static::createQuery()
        ->select('T1.id','T1.tag_no','T1.reason','T1.req_by','T2.serial_number',
        'T3.material_number','T2.quantity','T1.issue_qty','T1.created_at','T4.username')
        ->from('requisition T1')
        ->join('inventory_stock T2','LEFT',array('T1.inventory_id','T2.id'))
        ->join('material T3','LEFT',array('T2.material_id','T3.id'))
        ->join('user T4','LEFT',array('T1.created_by','T4.id'))
        ->where($where)
        ->order('T1.id')
        ->toArray()
        ->execute();

    }

    public static function saledetail($params){
        
    
        $where = array();

        
        if ($params['status'] == 0) {
            $where[] = array('T1.status',0);
        } elseif ($params['status'] == 1){
            $where[] = array('T1.status',1);
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
        ->select('T1.sale_order','T1.delivery_date','T1.customer_code','T1.customer_name',
        'T1.material_number',SQL::create('SUM(T1.planed_quantity) AS planed_quantity'),'T1.ship_qty')
        ->from('sale_order T1')
        ->groupBy('T1.sale_order','T1.delivery_date','T1.customer_code','T1.customer_name',
        'T1.material_number','T1.ship_qty')
        ->where($where)
        ->order('T1.material_number')
        ->toArray()
        ->execute();
    }

    public static function get_detail($params){
        
         $where = array();
        
        if (!empty($params['material_number'])) {
            $where[] = array('material_number',$params['material_number']);
        }
 
        if (!empty($params['sale_order'])){
            $where[] = array('sale_order',$params['sale_order']);
        }

        if (!empty($params['create_from'])){
                $strNewDate = date('Y-m-d', strtotime($params['create_from']));
                $where[] = array(sql::DATE('T1.created_at'),'>=',$strNewDate);
        } else {
                $strNewDate = date('Y-m-d', strtotime('-30 day'));
                $where[] = array(sql::DATE('T1.created_at'),'>=',$strNewDate);
        }

        if (!empty($params['create_to'])){
                $strNewDate = date('Y-m-d', strtotime($params['create_to']));
                $where[] = array(sql::DATE('T1.created_at'),'<=',$strNewDate);
        } else {
                $strNewDate = date('Y-m-d');
                $where[] = array(sql::DATE('T1.created_at'),'<=',$strNewDate);
        }

        if (!empty($params['from'])){
                $strNewDate = date('Y-m-d', strtotime($params['from']));
                $where[] = array(sql::DATE('T1.truck_date'),'>=',$strNewDate);
        }

        if (!empty($params['to'])){
                $strNewDate = date('Y-m-d', strtotime($params['to']));
                $where[] = array(sql::DATE('T1.truck_date'),'<=',$strNewDate);
        }


       // var_dump($where);
        return static::createQuery()
        ->select('T1.id','T1.sale_order','T1.material_number','T3.serial_number','T5.location_code original_location'
        ,'T2.serial_number pick','T4.location_code','T1.quantity','T1.ship_date','T1.pallet_no','T1.truck_confirm_date','T1.truck_date','T1.truck_id','T1.confirm_flg','T1.confirm_date','T1.file_name')
        ->from('delivery_order T1')
        ->join('inventory_stock T2','LEFT',array('T1.actual_id','T2.id'))
        ->join('inventory_stock T3','LEFT',array('T1.inventory_id','T3.id'))
        ->join('location T4','LEFT',array('T2.location_id','T4.id'))
        ->join('location T5','LEFT',array('T3.location_id','T5.id'))
        ->where($where)
        ->order('T1.id')
        ->toArray()
        ->execute();

    }

    public static function cyStock(){

        $params = array();
        $where = array();
        $where[] = array('gr_flg',1);
        $where[] = array('cy_flg',1);
        $where[] = array('storage_location',1097);

        return static::createQuery()
        ->select('id','container_received','delivery_order','container','case_number','temp_material','box_id','quantity','storage_location')
        ->from('packing_list')
        ->where($where)
        ->order('container','case_number','box_id')
        ->toArray()
        ->execute();

    }

    public static function getStock(){
        return static::createQuery()
        ->select('T1.id','T3.container','T3.case_number','T1.serial_number','T2.material_number','T2.material_name_en',
        'T2.material_type','T1.actual_quantity','T5.unit','T1.inbound_date','T4.location_code')
        ->from('inventory_stock T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('packing_list T3','LEFT',array('T1.reference','T3.id'))
        ->join('location T4','LEFT',array('T1.location_id','T4.id'))
        ->join('unit T5','LEFT',array('T2.unit','T5.id'))
        ->where(array('T1.actual_quantity','!=',0))
        ->order('id')
        ->toArray()
        ->execute();
    }

    public static function PackingList($params){

        $where = array();

        if ($params['from'] != ''){
            $where[] = array(sql::DATE('T1.created_at'),'>=',$params['from']);
        } else {

            $strStartDate =date('Y-m-d');
            $strNewDate = date('Y-m-d', strtotime('-7 day', strtotime($strStartDate)));

            $where[] = array(sql::DATE('T1.created_at'),'>=',$strNewDate);

        }

        if ($params['to'] != ''){
            $where[] = array(sql::DATE('T1.created_at'),'<=',$params['to']);
        } else {
            $where[] = array(sql::DATE('T1.created_at'),'<=',date('Y-m-d'));
        }

        if ($params['status'] == 1) {
            $where[] = array('receive_flg',1);
        } elseif ($params['status'] == 2) {
            $where[] = array('receive_flg',0);
        } elseif ($params['status'] == 3) {
            $where[] = array('receive_flg',2);
        }

        if ($params['container'] != ''){
            $where[] = array('container',$params['container']);
        }

        return static::createQuery()
        ->select('T1.id','T1.container','T1.case_number','T1.box_id',
        'T2.material_number','T2.material_name_en','T1.quantity','T3.unit','T1.receive_flg')
        ->from('packing_list T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('unit T3','LEFT',array('T3.id','T2.unit'))
        ->where($where)
        ->order('T1.container')
        ->toArray()
        ->execute();
    }

    public static function getTransaction($params){

        $where = array();

        if (!empty($params['from'])){
            $where[] = array(sql::DATE('T1.transaction_date'),'>=',$params['from']);
        } else {

            $strStartDate = date('Y-m-d 00:00:00');
            $strNewDate = date('Y-m-d 00:00:00', strtotime('-7 day', strtotime($strStartDate)));

            $where[] = array(sql::DATE('T1.transaction_date'),'>=',$strNewDate);
        }

        if (!empty($params['to'])){
            $where[] = array(sql::DATE('T1.transaction_date'),'<=',$params['to']);
        } else {
            $where[] = array(sql::DATE('T1.transaction_date'),'<=',date('Y-m-d 23:59:59'));
        }

        return static::createQuery()
        ->select('T1.id','T1.transaction_date','T1.transaction_type','T6.container','T6.case_number'
        ,'T1.serial_number','T2.material_number','T2.material_name_en','T1.quantity','T4.unit','T7.location_code from_location'
        ,'T3.location_code','T8.sale_order','T9.location_code pallet','T9.truck_id','T9.truck_date','T5.username')
        ->from('transaction T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('location T3','LEFT',array('T1.location_id','T3.id'))
        ->join('unit T4','LEFT',array('T2.unit','T4.id'))
        ->join('user T5','LEFT',array('T1.created_by','T5.id'))
        ->join('packing_list T6','LEFT',array('T1.reference','T6.id'))
        ->join('location T7','LEFT',array('T1.from_location','T7.id'))
        ->join('sale_order_status T8','LEFT',array('T1.sale_id','T8.id'))
        ->join('pallet_log T9','LEFT',array('T1.pallet_id','T9.id'))
        ->where($where)
        ->order('T1.transaction_date')
        ->toArray()
        ->execute();
    }

}