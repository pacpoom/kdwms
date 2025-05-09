<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\requisition;

use Kotchasan\Language;
use Kotchasan\Currency;
use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\DataTable;

/**
 * module=repair-action
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * แสดงฟอร์ม Modal สำหรับการปรับสถานะการทำรายการ
     *
     * @param object $index
     * @param array  $login
     *
     * @return string
     */
    public function render($status,$quantity,$box)
    {

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/requisition/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Requisition Form}',
            'titleClass' => 'icon-profile',
        ));

        $groups = $fieldset->add('groups');

        if($status == 0) {
            $readonly = false;
            $detail = true;
        } else {
            $readonly = true;
            $detail = false;
        }

        $reason = array(
            0 => 'Scrap',
            1 => 'Replacement',
            2 => 'Rework'
        );

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'serial_number',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Box ID}',
            'autofocus' => true,
            'value' => isset($box) ? $box : '',
            'readonly' => $readonly
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'tag',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Tag No.}',
            'value' => '',
            'readonly' => $detail
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'qty',
            'labelClass' => 'g-input icon-world',
            'itemClass' => 'width50',
            'label' => '{LNG_Quantity}',
            'value' => isset($quantity) ? $quantity : 0,
            'readonly' => true
        ));

        $groups->add('number', array(
            'id' => 'issue_qty',
            'labelClass' => 'g-input icon-world',
            'itemClass' => 'width50',
            'label' => '{LNG_Issue Quantity}',
            'value' => 0,
            'readonly' => $detail
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'req',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Request By}',
            'value' => '',
            'readonly' => $detail
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'reason',
            'labelClass' => 'g-input icon-world',
            'itemClass' => 'width50',
            'label' => '{LNG_Reason}',
            'datalist' => $reason,
            'value' => 0,
            'readonly' => $detail
        ));

        $fieldset->add('hr', array(
            'innerHTML' => '<center><h4>รูปภาพ หรือภาพถ่าย Requisition</h4></center>'
        ));

        $groups = $fieldset->add('groups');

        $img1 = WEB_URL.'skin/img/noicon.png';

        $groups->add('file', array(
            'id' => 'image_upload1',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-thumbnail',
            'label' => '{LNG_Image}',
            'dataPreview' => 'imgPicture1',
            'previewSrc' => $img1,
            'accept' => array('jpg', 'jpeg', 'png'),
        ));

        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));

        $fieldset->add('submit', array(
            'class' => 'button save icon-verfied',
            'value' => '{LNG_Show}'
        ));

        $fieldset->add('a', array(
            'id' => 'clear',
            'class' => 'button red icon-document',
            'value' => 'Label',
            'href' => WEB_URL.'index.php#module=wms-requisition&amp;time='. date('His') .'&amp;',
        ));

        $fieldset->add('hidden', array(
            'id' => 'login_user',
            'value' => isset($login['id']) ? $login['id'] : 0
        ));

        $fieldset->add('hidden',array(
            'id' => 'status',
            'value' => isset($status) ? $status : 0,
        ));
        return $form->render();
    }

}
?>
