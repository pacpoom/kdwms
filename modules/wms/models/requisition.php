<?php

namespace wms\requisition;

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
        $where[] = array('T1.pallet_no','!=','');
        $where[] = array('T1.truck_id','=','');

        return static::createQuery()
        ->select('T1.sale_order','T1.pallet_no')
        ->from('delivery_order T1')
        ->where($where)
        ->groupBy('T1.sale_order','T1.pallet_no')
        ->order('T1.pallet_no');

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

    public function submit(Request $request){

        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $resize = 1200;
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login)) {
                try {

                    if ($request->request('status')->toInt() == 0) {

                        $scan_qr = explode("_",$request->post('serial_number')->toString());

                        if (count($scan_qr) <= 5) {
                            $ret['serial_number']='';
                            $ret['fault'] = Language::get('Please Scan QR Code');
                            $request->removeToken();
                        } else {

                            $checkBox = \wms\requisition\Model::GetBoxID($scan_qr[4]);

                            if ($checkBox == false) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Box ID No Data !!');
                                $request->removeToken();
                            } elseif ($checkBox[0]->actual_quantity == 0) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Box already Ship Out !!');
                                $request->removeToken();
                            } else {

                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-requisition',
                                'status' => 1,
                                'quantity' => $checkBox[0]->actual_quantity,
                                'box' => $scan_qr[4])); 

                                $ret['message'] = Language::get('Saved successfully');
                                $request->removeToken();

                            }
                        }
                    } elseif ($request->request('status')->toInt() == 1) {

                        if ($request->post('tag')->toString() == '') {
                            $ret['tag']='';
                            $ret['fault'] = Language::get('Please Fill Tag !!');
                            $request->removeToken();
                        } elseif ($request->post('req')->toString() == '') {
                            $ret['req']='';
                            $ret['fault'] = Language::get('Please Fill Request By !!');
                            $request->removeToken();
                        } elseif ($request->post('issue_qty')->toInt() == 0) {
                            $ret['issue_qty']='';
                            $ret['fault'] = Language::get('Please Fill Issue Qty !!');
                            $request->removeToken();
                        } else {
                            
                            $checkBox = \wms\requisition\Model::GetBoxID($request->post('serial_number')->toString());

                            if ($checkBox == false) {
                                $ret['serial_number']='';
                                $ret['fault'] = Language::get('Box ID No Data !!');
                            } elseif ($request->post('issue_qty')->toInt() > $checkBox[0]->actual_quantity) {
                                $ret['issue_qty']='';
                                $ret['fault'] = Language::get('Over Issue Qty!!');
                                $request->removeToken();
                            } else {

                                $img1 = '';
                                $img1_name = '';

                                $sum_qty = $checkBox[0]->actual_quantity - $request->post('issue_qty')->toInt();

                                $update = array(
                                    'actual_quantity' => $sum_qty
                                );

                                $where = array();
                                $where[] = array('id',$checkBox[0]->id);

                                $table = $model->getTableName('inventory_stock');
                                $db->update($table,$where,$update);

                                $save_tran = array(
                                    'id' => NULL,
                                    'transaction_date' => date("Y-m-d H:i:s"),
                                    'transaction_type' => 'Requisition / '.$request->post('tag')->toString(),
                                    'reference' => $checkBox[0]->id,
                                    'serial_number' => $request->post('serial_number')->toString(),
                                    'material_id' => $checkBox[0]->material_id,
                                    'quantity' => -$request->post('issue_qty')->toInt(),
                                    'from_location' => 0,
                                    'location_id' => $checkBox[0]->location_id,
                                    'sale_id' => 0,
                                    'pallet_id' => 0,
                                    'created_at' => date('Y-m-d'),
                                    'created_by' => $login['id']
                                );

                                $table = $model->getTableName('transaction');

                                $db->insert($table,$save_tran);

                                $create_date = date('Y-m-d H:i:s');
                                // ไดเร็คทอรี่เก็บไฟล์
                                $dir = 'requisition/';
                                $dir2 = ROOT_PATH . DATA_FOLDER . $dir;
                                $k = 0;

                                foreach ($request->getUploadedFiles() as $item => $file) {

                                    if ($item == 'image_upload1') {
                                        if ($file->hasUploadFile()) {

                                            if (!$file->validFileExt(self::$cfg->dms_file_typies)) {
                                                // ชนิดของไฟล์ไม่ถูกต้อง
                                                $ret['ret_image_upload1'] = Language::get('The type of file is invalid');
                                            } else {
                                                // อัปโหลด ชื่อไฟล์แบบสุ่ม
                                                //$ext = $file->getClientFileExt();
                                                $ext = 'jpg';
                                                $file_upload = uniqid() . '.' . $ext;
                                                while (file_exists($dir2 . $file_upload)) {
                                                    $file_upload = uniqid() . '.' . $ext;
                                                }
                                                try {

                                                   // $file->moveTo($dir2 . $file_upload);
                                                    \Kotchasan\Image::resize($file->getTempFileName(), $dir2, $file_upload, $resize);
                            
                                                    $img1 =$dir2 . $file_upload;
                                                    $img1_name = $file_upload;

                                                } catch (\Exception $exc) {
                                                    // ไม่สามารถอัปโหลดได้
                                                    $ret['ret_image_upload1'] = Language::get($exc->getMessage());
                                                }
                                            }
                                        } elseif ($file->hasError()) {
                                            // ข้อผิดพลาดการอัปโหลด
                                            $ret['ret_image_upload1'] = Language::get($file->getErrorMessage());
                                        }
                                    }
                                }

                                $insert = array(
                                    'id' => NULL,
                                    'inventory_id' => $checkBox[0]->id,
                                    'tag_no' => $request->post('tag')->toString(),
                                    'issue_qty' => $request->post('issue_qty')->toInt(),
                                    'req_by' => $request->post('req')->toString(),
                                    'reason' => $request->post('reason')->toInt(),
                                    'img1' => $img1,
                                    'img1_name' => $img1_name,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'created_by' => $login['id']
                                );

                                $table = $model->getTableName('requisition');
                                $db->insert($table,$insert);

                                $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'wms-requisition',
                                'status' => 0,
                                'quantity' => 0,
                                'box' => '')); 

                                $ret['message'] = Language::get('Saved successfully');
                                $request->removeToken();

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