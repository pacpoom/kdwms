<?php
/**
 * @filesource modules/index/models/register.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Index\Register;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Validator;

/**
 * module=register
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * บันทึกข้อมูล (register.php)
     *
     * @param Request $request
     */
    
     public static function role_id(){

        return static::createQuery()
        ->select('id','role_name')
        ->from('role')
        ->order('id')
        ->execute();

    }

    public function submit(Request $request)
    {
        $ret = array();
        // session, token
        if ($request->initSession() && $request->isSafe()) {
            // แอดมิน
            $isAdmin = Login::isAdmin();
            // บุคคลทั่วไป สมัครสมาชิกได้ และ ไม่ใช่โหมดตัวอย่าง หรือ แอดมิน
            if ((!empty(self::$cfg->user_register) && self::$cfg->demo_mode == false) || $isAdmin) {
                try {
                    // รับค่าจากการ POST

                    $save = array(
                        'name' => $request->post('register_name')->topic(),
                        'status' => 1
                    );

                    if ($request->post('register_status')->toInt() == 0) {
                        // แอดมิน
                        $ret['ret_register_status'] = 'Please fill in';
                    } else {
                        // สมาชิกทั่วไป
                        $save['role_id'] = $request->post('register_status')->toInt();
                        
                    }

                    $permission = $isAdmin ? $request->post('register_permission', array())->topic() : array();
                    // table
                    $table_user = $this->getTableName('user');
                    // Database
                    $db = $this->db();
                    // ข้อมูลการเข้าระบบ
                    $checking = array();
                    foreach (Language::get('LOGIN_FIELDS') as $field => $label) {
                        $k = $field == 'username' || $field == 'email' ? 'username' : $field;
                        if ($request->post('register_'.$k)->exists()) {
                            if ($k == 'username') {
                                // อีเมล, username
                                $value = $request->post('register_'.$k)->username();
                            } else {
                                // อื่นๆ
                                $value = $request->post('register_'.$k)->toString();
                            }
                            if (empty($value)) {
                                // ไม่ได้กรอก
                                $ret['ret_register_'.$k] = 'Please fill in';
                            } elseif ($field == 'email' && isset(self::$cfg->login_fields['email']) && count(self::$cfg->login_fields) == 1 && !Validator::email($value)) {
                                // อีเมลไม่ถูกต้อง
                                $ret['ret_register_'.$k] = Language::replace('Invalid :name', array(':name' => $label));
                            } else {
                                unset($ret['ret_register_'.$k]);
                                // เก็บไว้ตรวจสอบข้อมูลซ้ำ
                                $checking[$k] = $value;
                            }
                        }
                    }
                    // ตรวจสอบข้อมูลซ้ำ
                    foreach ($checking as $k => $value) {
                        $search = $db->first($table_user, array($k, $value));
                        if ($search) {
                            $ret['ret_register_'.$k] = Language::replace('This :name already exist', array(':name' => Language::find('LOGIN_FIELDS', '', $k)));
                        } else {
                            $save[$k] = $value;
                        }
                    }
                    // password
                    $password = $request->post('register_password')->password();
                    $repassword = $request->post('register_repassword')->password();
                    if (mb_strlen($password) < 4) {
                        // รหัสผ่านต้องไม่น้อยกว่า 4 ตัวอักษร
                        $ret['ret_register_password'] = 'this';
                    } elseif ($repassword != $password) {
                        // กรอกรหัสผ่านสองช่องให้ตรงกัน
                        $ret['ret_register_repassword'] = 'this';
                    } else {
                        $save['password'] = $password;
                    }
                    // name
                    if (empty($save['name'])) {
                        $ret['ret_register_name'] = 'Please fill in';
                    }


                    if ($isAdmin) {
                        // หมวดหมู่ สำหรับการลงทะเบียนโดยแอดมิน
                        // $category = \Index\Category\Model::init();
                        // foreach ($category->items() as $k => $label) {
                        //     $save[$k] = $category->save($k, $request->post('register_'.$k.'_text')->topic());
                        //     if (empty($save[$k]) && in_array($k, self::$cfg->categories_required)) {
                        //         $ret['ret_register_'.$k] = 'Please fill in';
                        //     }
                        // }
                    } elseif (!empty(self::$cfg->activate_user)) {
                        // activate
                        $save['activatecode'] = md5($save['username'].uniqid());
                    }
                    if (empty($ret)) {
                        // สมัครโดยแอดมินเข้าระบบทันที อื่นๆ ตามที่ตั้งค่า
                        $save['active'] = $isAdmin ? 1 : self::$cfg->new_members_active;
                        // ลงทะเบียนสมาชิกใหม่

                        $save = self::execute($this, $save, $permission);
                        // log
                        $member_id = $isAdmin ? $isAdmin['id'] : $save['id'];
                        \Index\Log\Model::add($member_id, 'index', 'User', '{LNG_Create new account} ID : '.$save['id'], $member_id);
                        if ($isAdmin) {
                            // คืนค่า
                            $ret['alert'] = Language::get('Saved successfully');
                            // ไปหน้าสมาชิก
                            $ret['location'] = 'index.php?module=member';
                        } elseif ($save['active'] == 0) {
                            // ส่งข้อความแจ้งเตือนการสมัครสมาชิกของ user
                            $ret['alert'] = \Index\Email\Model::sendApprove();
                        } elseif (!empty(self::$cfg->welcome_email) || !empty(self::$cfg->activate_user)) {
                            // ส่งอีเมล แจ้งลงทะเบียนสมาชิกใหม่
                            $err = \Index\Email\Model::send($save, $password);
                            if ($err != '') {
                                // คืนค่า error
                                $ret['alert'] = $err;
                            } elseif (!empty($save['activatecode'])) {
                                // activate
                                $ret['alert'] = Language::replace('Registered successfully Please check your email :email and click the link to verify your email.', array(':email' => $save['username']));
                            } else {
                                // no activate
                                $ret['alert'] = Language::replace('Register successfully, We have sent complete registration information to :email', array(':email' => $save['username']));
                            }
                        } else {
                            // คืนค่า
                            $ret['alert'] = Language::get('Register successfully Please log in');
                        }
                        // ไปหน้าเข้าระบบ
                        $ret['location'] = $isAdmin ? 'index.php?module=member' : 'index.php?action=login';
                        // เคลียร์
                        $request->removeToken();
                    }
                } catch (\Kotchasan\InputItemException $e) {
                    $ret['alert'] = $e->getMessage();
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }

    /**
     * ลงทะเบียนสมาชิกใหม่
     * คืนค่าแอเรย์ของข้อมูลสมาชิกใหม่
     *
     * @param Model $model
     * @param array $save       ข้อมูลสมาชิก
     * @param array $permission
     *
     * @return array
     */
    public static function execute($model, $save, $permission = null)
    {
        if (!isset($save['username'])) {
            $save['username'] = null;
        }
        if (!isset($save['password'])) {
            $save['password'] = '';
        } else {
            $save['salt'] = \Kotchasan\Password::uniqid();
            $save['password'] = sha1(self::$cfg->password_key.$save['password'].$save['salt']);
        }
        $save['create_date'] = date('Y-m-d H:i:s');
        $save['session_id'] = null;
        if ($permission === null) {
            // permission ถ้าเป็น null สามารถทำได้ทั้งหมด
            $permission = array_keys(\Gcms\Controller::getPermissions());
        } else {
            // สมาชิกทั่วไปใช้ค่าเริ่มต้นของโมดูล
            $permission = \Gcms\Controller::initModule($permission, 'newRegister', $save);
        }
        $save['permission'] = empty($permission) ? '' : ','.implode(',', $permission).',';
        // บันทึกลงฐานข้อมูล

        $save['id'] = $model->db()->insert($model->getTableName('user'), $save);
        // คืนค่าแอเรย์ของข้อมูลสมาชิกใหม่
        $save['permission'] = array();
        foreach ($permission as $key => $value) {
            $save['permission'][] = $value;
        }
        return $save;
    }
}
