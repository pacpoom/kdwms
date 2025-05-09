<?php

namespace product\boms;

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
        $table = $model->getTableName('bom');

        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            try {
                
                if ($request->post('model')->toInt() == 0) {
                    $ret['ret_model'] = 'Please fill in';
                } elseif ($request->post('raw')->toInt() == 0) {
                    $ret['ret_raw'] = 'Please fill in';
                } else {

                    if ($request->post('status')->toInt() == 0) {
                        $save = array(
                            'id' => NULL,
                            'model_no' => $request->post('model')->toInt(),
                            'material_id' => $request->post('raw')->toInt(),
                            'usage' => $request->post('usage')->topic(),
                            'created_at' => date('Y-m-d'),
                            'created_by' => $login['id']
                        );
    
                        $db->insert($table,$save);
                        $ret['alert'] = Language::get('Saved successfully');
                        $ret['location'] = 'reload';
                        $request->removeToken();
                    } else {

                        $save = array(
                            'usage' => $request->post('usage')->topic()
                        );

                        $db->update($table,array('id',$request->post('id')->toInt()),$save);
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