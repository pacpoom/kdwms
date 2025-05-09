<?php

namespace wms\materials;

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
                array('material_number', $id),
            ));
            return $search;
        }
    }

    public static function get_currency(){

        return static::createQuery()
        ->select('id','currency')
        ->from('currency')
        ->order('currency')
        ->execute();
    }

    public static function image_resize($file_name, $width, $height, $crop=FALSE) {
        list($wid, $ht) = getimagesize($file_name);
        $r = $wid / $ht;
        if ($crop) {
           if ($wid > $ht) {
              $wid = ceil($wid-($width*abs($r-$width/$height)));
           } else {
              $ht = ceil($ht-($ht*abs($r-$w/$h)));
           }
           $new_width = $width;
           $new_height = $height;
        } else {
           if ($width/$height > $r) {
              $new_width = $height*$r;
              $new_height = $height;
           } else {
              $new_height = $width/$r;
              $new_width = $width;
           }
        }
        $source = imagecreatefromjpeg($file_name);
        $dst = imagecreatetruecolor($new_width, $new_height);
        image_copy_resampled($dst, $source, 0, 0, 0, 0, $new_width, $new_height, $wid, $ht);
        return $dst;
     }

    public function submit(Request $request){
        $ret = array();
        $model = new \Kotchasan\Model();
        $db = $model->db();
        $table = $model->getTableName('material');

        //var_dump($request->post('textarea_checkbox')->topic());
        
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            try {
            
                $save = array(
                    'material_number' => $request->post('material_number')->topic(),
                    'material_name_en' => $request->post('material_name_en')->topic(),
                    'material_name_thai' => $request->post('material_name_thai')->topic(),
                    'unit' => $request->post('unit')->toInt(),
                    'net_weight' => $request->post('net_weight')->topic(),
                    'material_type' => $request->post('material_type')->toInt(),
                    'unit_price' => $request->post('unit_price')->topic(),
                    'currency' => $request->post('currency')->toInt(),
                    'active' => 1,
                    'inspection_flg' => $request->post('qc')->toInt(),
                    'created_at' => date('Y-m-d'),
                    'created_by' => $login['id']
                );

                if (empty($save['material_number'])){
                    $ret['ret_material_number'] = 'Please fill in';
                } elseif (empty($save['material_name_en'])) {
                    $ret['ret_material_name_en'] = 'Please fill in';
                } elseif (empty($save['unit'])) {
                    $ret['ret_unit'] = 'Please fill in';
                } elseif (empty($save['material_type'])) {
                    $ret['ret_material_type'] = 'Please fill in';
                } elseif (empty($save['currency'])) {
                    $ret['ret_currency'] = 'Please fill in';
                } else {
                    
                    $index = \wms\materials\Model::get($save['material_number']);
        
                    if ($index == true){

                        foreach ($request->getUploadedFiles() as $item => $file) {
                            // ไอดีของอินพุตที่ส่งมา
                            $input = $item === 'image_upload' ? 'image_upload' : 'pdf_uploads';

                            /* @var $file UploadedFile */
                            if ($file->hasUploadFile()) {
                                // ตรวจสอบนามสกุลของไฟล์
                                if (!$file->validFileExt(array('jpg', 'jpeg', 'png'))) {
                                    // error ชนิดของไฟล์ไม่ถูกต้อง
                                    $ret['ret_'.$input] = Language::get('The type of file is invalid');
                                } else {
                                    try {
                                        $member_img_typies = array('jpg', 'jpeg', 'png');
                                        $member_img_size = 5000;
                                        $dir = ROOT_PATH.DATA_FOLDER.'material/';
                                        // อัปโหลดไฟล์ไปยังปลายทาง
                                        $file->moveTo(ROOT_PATH.DATA_FOLDER.'material/'.$index->id.'.'.$file->getClientFileExt());
                                        $file->resizeImage($member_img_typies,$dir,$index->id.'.'.$file->getClientFileExt(),$member_img_size);
                                        //$data_resize = \index\driveradd\Model::image_resize(ROOT_PATH.DATA_FOLDER.'driver/'.$save['id'].'.'.$file->getClientFileExt(), 250, 250);
                                    } catch (\Exception $exc) {
                                        // ข้อผิดพลาดการอัปโหลด
                                        $ret['ret_'.$input] = Language::get($exc->getMessage());
                                    }
                                }
                            } elseif ($file->hasError()) {
                                // ข้อผิดพลาดการอัปโหลด
                                $ret['ret_'.$input] = Language::get($file->getErrorMessage());
                            }
                        }

                        $db->update($table,array('material_number', $save['material_number']),$save);
                        $ret['alert'] = Language::get('Saved successfully');
                        $ret['location'] = 'reload';
                        $request->removeToken();

                    } else {
                        
                        $save['id'] = NULL;

                        $db->insert($table,$save);

                        $index = \wms\materials\Model::get($save['material_number']);

                        foreach ($request->getUploadedFiles() as $item => $file) {
                            // ไอดีของอินพุตที่ส่งมา
                            $input = $item === 'image_upload' ? 'image_upload' : 'pdf_uploads';
                            /* @var $file UploadedFile */
                            if ($file->hasUploadFile()) {
                                // ตรวจสอบนามสกุลของไฟล์
                                if (!$file->validFileExt(array('jpg', 'jpeg', 'png'))) {
                                    // error ชนิดของไฟล์ไม่ถูกต้อง
                                    $ret['ret_'.$input] = Language::get('The type of file is invalid');
                                } else {
                                    try {
                                        $member_img_typies = array('jpg', 'jpeg', 'png');
                                        $member_img_size = 5000;
                                        $dir = ROOT_PATH.DATA_FOLDER.'material/';
                                        // อัปโหลดไฟล์ไปยังปลายทาง
                                        $file->moveTo(ROOT_PATH.DATA_FOLDER.'material/'.$index->id.'.'.$file->getClientFileExt());
                                        $file->resizeImage($member_img_typies,$dir,$index->id.'.'.$file->getClientFileExt(),$member_img_size);
                                        //$data_resize = \index\driveradd\Model::image_resize(ROOT_PATH.DATA_FOLDER.'driver/'.$save['id'].'.'.$file->getClientFileExt(), 250, 250);
                                    } catch (\Exception $exc) {
                                        // ข้อผิดพลาดการอัปโหลด
                                        $ret['ret_'.$input] = Language::get($exc->getMessage());
                                    }
                                }
                            } elseif ($file->hasError()) {
                                // ข้อผิดพลาดการอัปโหลด
                                $ret['ret_'.$input] = Language::get($file->getErrorMessage());
                            }
                        }

                        $ret['alert'] = Language::get('Saved successfully');
                        $ret['location'] = 'reload';
                        $request->removeToken();
                    }
                }

            } catch (\Kotchasan\InputItemException $e){
                $ret['alert'] = $e->getMessage();
            }
        }
        echo json_encode($ret);
    }
}