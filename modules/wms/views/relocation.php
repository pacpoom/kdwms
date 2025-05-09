<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\relocation;

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
    public function render($request,$material_number,$material_name,$total_box,$total_qty,$qty,$location_code,$status)
    {

        if ($status == 1) {
            $readonly = true;
            $scan_box = false;
            $location_rd = true;
        } else {
            $readonly = false;
            $scan_box = true;
            $location_rd = false;
        }

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/relocation/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Put Away}',
            'titleClass' => 'icon-profile',
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'serial_number',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width60',
            'placeholder' => 'Scan Qr Code',
            'label' => '{LNG_Box ID}',
            'value' => '',
            'readOnly' => $scan_box,
            'autofocus' => $readonly 
        ));
      
        $groups->add('text', array(
            'id' => 'location_code',
            'labelClass' => 'g-input icon-category',
            'itemClass' => 'width40',
            'label' => '{LNG_Location}',
            'readOnly' => $location_rd,
            'value' => isset($location_code) ? $location_code : '',
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'material_number',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width70',
            'label' => '{LNG_Material Number}',
            'disabled' => true,
            'value' => isset($material_number) ? $material_number : '',
        ));

        $groups->add('text', array(
            'id' => 'qty',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width30',
            'label' => '{LNG_Quantity}',
            'disabled' => true,
            'value' => isset($qty) ? $qty : 0,
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'material_name',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width100',
            'label' => '{LNG_Material Name Eng}',
            'disabled' => true,
            'value' => isset($material_name) ? $material_name : '',
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'total_box',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width50',
            'label' => '{LNG_Total Box}',
            'disabled' => true,
            'value' => isset($total_box) ? $total_box : 0,
        ));

        $groups->add('text', array(
            'id' => 'total_qty',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width50',
            'label' => '{LNG_Total Qty}',
            'disabled' => true,
            'value' => isset($total_qty) ? $total_qty : 0,
        ));

        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));

        $fieldset->add('submit', array(
            'class' => 'button save icon-verfied',
            'value' => '{LNG_Show Data}'
        ));

        $fieldset->add('hidden',array(
            'id' => 'status',
            'value' => isset($status) ? $status : 0,
        ));

        $fieldset->add('a', array(
            'id' => 'clear',
            'class' => 'button red icon-document',
            'value' => 'Label',
            'href' => WEB_URL.'index.php?module=wms-relocation&amp;time='. date('His') .'&amp;',
        ));

        $fieldset->add('hidden', array(
            'id' => 'login_user',
            'value' => isset($login['id']) ? $login['id'] : 0
        ));
    
        return $form->render();
    }

    public function show_data($request,$location_code){

        //var_dump($index);

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');

        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\relocation\Model::getContainer($location_code),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'hideCheckbox' => true,
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'actionCallback' =>'dataTableActionCallback',
            'headers' => array(
                'serial_number' => array(
                    'text' => '{LNG_Box ID}'
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'quantity' => array(
                    'text' => '{LNG_Quantity}'
                ),
                'location_code' => array(
                    'text' => '{LNG_Location}'
                )
            )
        ));

        setcookie('perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);

        return $table->render();
    }

    public function onRow($item, $o, $prop)
    {
         return $item;
    }

}
?>
