<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\ship;

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
    public function render($pallet,$status)
    {

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/ship/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Confirm Picking}',
            'titleClass' => 'icon-profile',
        ));

        $groups = $fieldset->add('groups');

        if($status == 0) {
            $read = true;
            $location = false;
        } else {
            $read = false;
            $location = true;
        }

        $groups->add('text', array(
            'id' => 'serial_number',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width70',
            'placeholder' => 'Scan Qr Code',
            'value' => '',
            'autofocus' => $location,
            'readonly' => $read
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'location_code',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width30',
            'label' => '{LNG_Pallet No}',
            'readonly' => $location,
            'placeholder' => '{LNG_Pallet No}',
            'value' => isset($pallet) ? $pallet : '',
        ));

        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));

        $fieldset->add('submit', array(
            'class' => 'button save icon-verfied',
            'value' => '{LNG_Save}'
        ));

        $fieldset->add('a', array(
            'id' => 'clear',
            'class' => 'button red icon-document',
            'value' => 'Label',
            'href' => WEB_URL.'index.php#module=wms-ship',
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

    public function show_data($request,$so){

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');

        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\ship\Model::toDataTable($so),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'perPage' => $request->cookie('inventoryProject_perPage',10)->toInt(),
            'sort' => $request->cookie('inventoryProject_sort', 'id desc')->toString(),
            'actionCallback' =>'dataTableActionCallback',
            'headers' => array(
                'serial_number' => array(
                    'text' => '{LNG_Box ID}'
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
            )
        ));

        setcookie('inventoryProject_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('inventoryProject_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);

        return $table->render();
    }

    public function onRow($item, $o, $prop)
    {
         return $item;
    }

}
?>
