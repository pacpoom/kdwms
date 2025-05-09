<?php

namespace product\export;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='bom';

    public static function getJob($id){
        return static::createQuery()
        ->select('T1.job_no','T1.purchase_order','T1.delivery_date','T2.material_number','T2.material_name_en','T1.plan','T1.production_date')
        ->from('job_order T1')
        ->join('material T2','LEFT',array('T1.model_no','T2.id'))
        ->where(array('T1.id',$id))
        ->first();
    }

    public static function getPicking($id){

        return static::createQuery()
        ->select('T1.id','T5.declaration_no','T2.serial_number','T3.material_number','T3.material_name_en','T2.actual_quantity','T4.location_code','"" remark')
        ->from('job_order_req T1')
        ->join('inventory_stock T2','LEFT',array('T1.inventory_id','T2.id'))
        ->join('material T3','LEFT',array('T2.material_id','T3.id'))
        ->join('location T4','LEFT',array('T2.location_id','T4.id'))
        ->join('declaration T5','LEFT',array('T2.reference','T5.id'))
        ->where(array(array('T1.job_id',$id),array('pick_flg',0)))
        ->order('T3.material_number','T4.location_code')
        ->toArray()
        ->execute();
   }
  
}
