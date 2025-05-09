<?php

namespace product\rountings;

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

    public static function get() {
        return static::createQuery()
        ->select('T1.id','T1.material_number','T1.material_name_en','T2.unit')
        ->from('material T1')
        ->join('unit T2','LEFT',array('T1.unit','T2.id'))
        ->order('material_number')
        ->execute();
    }

    public static function checkRounting($id,$rount){

        return static::createQuery()
        ->select('id','material_id','routing_id')
        ->from('routing')
        ->where(array(array('material_id',$id),array('routing_id',$rount)))
        ->execute();

    }

    public static function getRounting(){
        return static::createQuery()
        ->select('category_id','topic')
        ->from('category')
        ->where(array('type','routing'))
        ->order('category_id')
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
        $table = $model->getTableName('routing');

        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            try {
                
                if ($request->post('rounting')->toInt() == 0) {
                    $ret['ret_rounting'] = 'Please fill in';
                } elseif ($request->post('material')->toInt() == 0) {
                    $ret['ret_material'] = 'Please fill in';
                } else {

                    if ($request->post('status')->toInt() == 0) {

                        $check_Rount = \product\rountings\Model::checkRounting($request->post('material')->toInt(),$request->post('rounting')->toInt());

                        if ($check_Rount == true) {
                            $ret['fault'] = Language::get('Do not Data Duplicate !!');
                            $request->removeToken();
                        } else {

                            $save = array(
                                'id' => NULL,
                                'material_id' => $request->post('material')->toInt(),
                                'routing_id' => $request->post('rounting')->toInt(),
                                'min' => $request->post('min')->toInt(),
                                'max' => $request->post('max')->toInt(),
                                'created_at' => date('Y-m-d'),
                                'created_by' => $login['id']
                            );
        
                            $db->insert($table,$save);

                            $ret['alert'] = Language::get('Saved successfully');
                            $ret['location'] = 'reload';
                            $request->removeToken();
                        }

                    } else {

                        $check_Rount = \product\rountings\Model::checkRounting($request->post('material')->toInt(),$request->post('rounting')->toInt());

                        if ($check_Rount == true) {
                            $ret['fault'] = Language::get('Do not Data Duplicate !!');
                            $request->removeToken();

                        } else {

                            $save = array(
                                'material_id' => $request->post('material')->toInt(),
                                'routing_id' => $request->post('rounting')->toInt(),
                                'min' => $request->post('min')->toInt(),
                                'max' => $request->post('max')->toInt(),
                                'created_at' => date('Y-m-d'),
                                'created_by' => $login['id']
                            );

                            $db->update($table,array('id',$request->post('id')->toInt()),$save);
                            $ret['alert'] = Language::get('Saved successfully');
                            $ret['location'] = 'reload';
                            $request->removeToken();
                            
                        }
                        
                    }
      
                }
                
            } catch (\Kotchasan\InputItemException $e){
                $ret['alert'] = $e->getMessage();
            }
        }
        echo json_encode($ret);
    }
}