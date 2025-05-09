<?php
/**
 * @filesource modules/index/views/register.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Index\Register;

use Kotchasan\Language;
use Kotchasan\Currency;
use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\DataTable;

/**
 * module=register
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ลงทะเบียนสมาชิกใหม่
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/index/model/register/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $get_so = \Index\Register\Model::role_id();

        foreach ($get_so As $item) {
            $role_data[$item->id] = $item->role_name;
        }

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Details of} {LNG_User}'
        ));
        $groups = $fieldset->add('groups');
        // username
        $groups->add('text', array(
            'id' => 'register_username',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-email',
            'label' => '{LNG_Email}/{LNG_Username}',
            'comment' => '{LNG_Email address used for login or request a new password}',
            'maxlength' => 255,
            'validator' => array('keyup,change', 'checkUsername', 'index.php/index/model/checker/username')
        ));
        // name
        $groups->add('text', array(
            'id' => 'register_name',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Name}',
            'comment' => '{LNG_Please fill in} {LNG_Name}'
        ));
        $groups = $fieldset->add('groups');
        // password
        $groups->add('password', array(
            'id' => 'register_password',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-password',
            'label' => '{LNG_Password}',
            'comment' => '{LNG_Passwords must be at least four characters}',
            'maxlength' => 50,
            'showpassword' => true,
            'validator' => array('keyup,change', 'checkPassword')
        ));
        // repassword
        $groups->add('password', array(
            'id' => 'register_repassword',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-password',
            'label' => '{LNG_Confirm password}',
            'comment' => '{LNG_Enter your password again}',
            'maxlength' => 50,
            'showpassword' => true,
            'validator' => array('keyup,change', 'checkPassword')
        ));
        // status
        $fieldset->add('text', array(
            'id' => 'register_status',
            'itemClass' => 'item',
            'label' => '{LNG_Role ID}',
            'labelClass' => 'g-input icon-star0',
            'datalist' => $role_data,
            'value' => 0
        ));

        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-register',
            'value' => '{LNG_Register}'
        ));
        $fieldset->add('hidden', array(
            'id' => 'register_id',
            'value' => 0
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
