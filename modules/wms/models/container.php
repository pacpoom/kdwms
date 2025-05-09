<?php

namespace wms\container;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Database\Sql;

class model extends \Kotchasan\Model{

    protected $table ='container';
    private $header = array();
    private $row = 0;
    
    public static function toDataTable($params){
        $params = array();

        return static::createQuery()
        ->select('id','status','receive_date','year_lot','week_lot','lot_no','container_size','model','delivery_date','eta_date'
        ,'ata_date','container_type','container','container_bl','total_material','total_case','total_box','total_quantity'
        ,'receive_material','receive_case','receive_box','receive_quantity')
        ->from('container')
        ->order('delivery_date');

    }

    public static function get_container($id){

        return static::createQuery()
        ->select('id','status','receive_date','year_lot','week_lot','lot_no','container_size','model','delivery_date','eta_date'
        ,'ata_date','container_type','container','container_bl','total_material','total_case','total_box','total_quantity'
        ,'receive_material','receive_case','receive_box','receive_quantity')
        ->from('container')
        ->where(array('id',$id))
        ->execute();

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
                if ($action ==='statusd'){

                    $index = \wms\container\Model::get_container($request->post('id')->toInt());
    
                    $ret['modal'] = Language::trans(\wms\containers\View::create()->render($index,$login['id']));
                    //$ret['modal'] = Language::trans(\wms\materials\View::create()->render($index,$login));
                }

            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }

    public function submit(Request $request)
    {
        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('packing_list');
        $table_container = $model->getTableName('container');
        $header = array();

        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            foreach ($request->getUploadedFiles() as $item => $file) {
                if ($file->hasUploadFile()) {
                    if (!$file->validFileExt(array('csv'))) {
                        // ชนิดของไฟล์ไม่ถูกต้อง
                        $ret['ret_' . $item] = Language::get('The type of file is invalid');
                    } else {
                        try {
                            $this->header = \wms\csv\Model::container();

                            \Kotchasan\Csv::read(
                                $file->getTempFileName(),
                                function ($data) use ($login) {
                                    $this->importPL($data, $login);
                                },
                                $this->header,
                                self::$cfg->csv_language
                            );

                            $ret['location'] = 'reload';
                            $ret['alert'] = Language::replace('Successfully imported :count items', array(':count' => $this->row));
                        } catch (\Exception $ex) {
                            $ret['ret_' . $item] = $ex->getMessage();
                        }
                    }
                } elseif ($file->hasError()) {
                    // upload Error
                    $ret['ret_' . $item] = $file->getErrorMessage();
                } else {
                    // ไม่ได้เลือกไฟล์
                    $ret['ret_' . $item] = 'Please browse file';
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
  
    public function importPL($data, $login)
    {
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table_container = $model->getTableName('container');

        if (count($data) > 0) {
            // ตรวจสอบข้อมูล
            if (isset($data[$this->header[0]])) {
                // ตรวจสอบข้อมูลซ้ำ
                $check = $db->first($table_container, array(
                    array('container', $data[$this->header[0]]),
                    array('status', 0)
                ));

                if ($check) {
                    // ถ้ามีข้อมูลอยู่แล้ว
                    $update = array(
                        'status' => 1,
                        'year_lot' => $data[$this->header[1]],
                        'week_lot' => $data[$this->header[2]],
                        'lot_no' => $data[$this->header[3]],
                        'container_size' => $data[$this->header[4]],
                        'model' => $data[$this->header[5]],
                        'container_type' => $data[$this->header[6]],
                        'container_bl' => $data[$this->header[7]],
                        'gr_flg' => 1,
                        'receive_date' => date('Y-m-d'),
                        'created_at' => date('Y-m-d'),
                        'created_by' => $login['id']
                    );
                    // update
                    $db->update($table_container, array('id', $check->id), $update);
                    ++$this->row;
                }
            }
        }
    }
}