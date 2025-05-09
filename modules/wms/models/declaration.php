<?php

namespace wms\declaration;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='declaration';
    private $header = array();
    private $row = 0;

    public static function toDataTable($params){
        $params = array();
        return static::createQuery()
        ->select('T1.id','T1.declaration_no','T1.invoice_no','T1.date_transmit','T1.item_no','T2.material_number','T2.material_name_en','T2.material_name_thai','T3.unit',
         'T1.quantity','T1.id actual_quantity','T2.net_weight','0 sum_weight','T2.unit_price','0 sum_price','T6.currency')
        ->from('declaration T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->join('unit T3','LEFT',array('T3.id','T2.unit'))
        ->join('transfer T4','LEFT',array('T4.declaration_no','T1.id'))
        ->join('inventory_stock T5','LEFT',array('T5.reference','T1.id'))
        ->join('currency T6','LEFT',array('T6.id','T2.currency'))
        ->groupBy('T1.id','T1.declaration_no','T2.material_number','T4.declaration_no','T5.reference')
        ->order('T1.declaration_no','T1.item_no');

    }

    public static function sumQty($id){
        return static::createQuery()
        ->select(sql::SUM('quantity','qty'))
        ->from('transfer')
        ->where(array('declaration_no',$id))
        ->execute();
    }

    public static function get_ccl($id){
        return static::createQuery()
        ->select('id','declaration_no','date_transmit','item_no','invoice_no','T2.material_number','T2.material_name_en')
        ->from('declaration T1')
        ->join('material T2','LEFT',array('T1.material_id','T2.id'))
        ->where(array('T1.id',$id))
        ->first();
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

    public static function getMat($id){

        return static::createQuery()
        ->select('id','material_number')
        ->from('material')
        ->where(array('material_number',$id))
        ->first();
    }

    public static function getDecla($id,$item){
        return static::createQuery()
        ->select('id','declaration')
        ->from('declaration')
        ->where(array(
            array('declaration_no',$id),
            array('material_id',$item)))
        ->first();
    }

    public function submit(Request $request){

        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('declaration');

        if ($request->initSession() && $request->isSafe() && $this->login = Login::isMember()) {
            foreach ($request->getUploadedFiles() as $item => $file){
                if ($file->hasUploadFile()){
                    if (!$file->validFileExt(array('csv'))) {
                    // ชนิดของไฟล์ไม่ถูกต้อง
                    $ret['ret_'.$item] = Language::get('The type of file is invalid');
                    } else {
                        try {
                     
                            $this->header = \wms\csv\Model::importccl();

                            \Kotchasan\Csv::read(
                                $file->getTempFileName(),
                                array($this, 'importccl'),
                                $this->header,
                                self::$cfg->csv_language
                            );
                            $ret['location'] = 'reload';
                            //$ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'index-gaoff', 'id' => 0));
                            $ret['alert'] = Language::replace('Successfully imported :count items', array(':count' => $this->row));
                        } catch (\Exception $ex) {
                            $ret['ret_'.$item] = $ex->getMessage();
                        }
                    }
                } elseif ($file->hasError()) {
                    // upload Error
                    $ret['ret_'.$item] = $file->getErrorMessage();
                } else {
                    // ไม่ได้เลือกไฟล์
                    $ret['ret_'.$item] = 'Please browse file';
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
    
    public function importccl($data){

        $getId = \wms\declaration\Model::getMat($data[$this->header[3]]);

        if ($getId == true) {

            $check = \wms\declaration\Model::getDecla($data[$this->header[0]],$getId->id);

            if ($check == false) {
                $save = array(
                    'id' => NULL,
                    'declaration_no' => $data[$this->header[0]],
                    'date_transmit' => date('Y-m-d'),
                    'item_no' => $data[$this->header[2]],
                    'material_id' => $getId->id,
                    'invoice_no' => $data[$this->header[1]],
                    'quantity' => $data[$this->header[4]],
                    'created_at' => date('Y-m-d'),
                    'created_by' => $this->login['id']
                );
                $this->db()->insert($this->getTableName('declaration'),$save);
                ++$this->row;
            }
           
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
                if ($action ==='add'){

                    $index = \wms\declaration\Model::get_ccl($request->post('id')->toInt());
                    
                    $ret['modal'] = Language::trans(\wms\declarations\View::create()->render($index,$login));
                    
                } else{
                    if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->filter('0-9,'), $match)) {
                       
                        if ($action === 'delete') {

                            //var_dump('delete');
                            // ลบ
                            $db->delete($this->getTableName('material'), array('id', $match[1]), 0); 
                            // log
                            \Index\Log\Model::add(0, 'material', 'Delete', '{LNG_Delete} {LNG_Inventory} ID : '.implode(', ', $match[1]), $login['id']);
                            // reload
                            $ret['location'] = 'reload';
    
                        } elseif ($action ==='statusd'){
    
                            $index = \wms\declaration\Model::get_ccl($request->post('id')->toInt());
    
                            var_dump($index);
                            $ret['modal'] = Language::trans(\wms\declarations\View::create()->render($index,$login));
    
                        } elseif ($action ==='Active'){
                            $update_data['active'] = 1;
                            $db->update($this->getTableName('material'), array('id', $match[1]), $update_data);
                            $ret['location'] = 'reload';

                        } elseif ($action ==='Non Active'){
                            $update_data['active'] = 0;
                            
                            $db->update($this->getTableName('material'), array('id', $match[1]), $update_data);
                            $ret['location'] = 'reload';
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