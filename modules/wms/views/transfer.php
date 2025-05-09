<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\transfer;

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
    public function render($material_number,$material_name,$quantity,$location_code,$reference,$declaration_no,$status)
    {

        
        if ($status == 1) {
            $readonly = true;
            $scan_box = false;
        } else {
            $readonly = false;
            $scan_box = true;
        }

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/transfer/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Transfer FG}',
            'titleClass' => 'icon-profile',
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'serial_number',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width70',
            'placeholder' => 'Scan Qr Code',
            'value' => '',
            'autofocus' => true,
            'readOnly' => $scan_box
        ));
      
        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'job_order',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width50',
            'label' => '{LNG_Job No}',
            'itemClass' => 'width50',
            'value' => isset($reference) ? $reference : '',
            'readOnly' => $readonly
        ));

        $category = \wms\locationdata\Model::init(false);

        $n = 0;
        foreach (Language::get('INVENTORY_LOCATION', array()) as $key => $label) {
            $groups->add('text', array(
                'id' => 'location_code',
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'width50',
                'label' => $label,
                'datalist' => $category->toSelect($key),
                'value' => 585,
                'readOnly' => true
            ));
            $n++;
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
        }

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
            'value' => isset($quantity) ? $quantity : 0,
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

        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));

        $fieldset->add('submit', array(
            'class' => 'button save icon-verfied',
            'value' => '{LNG_Show Data}'
        ));

        $fieldset->add('a', array(
            'id' => 'clear',
            'class' => 'button red icon-document',
            'value' => 'Label',
            'href' => WEB_URL.'index.php#module=wms-transfer&amp;time='. date('His') .'&amp;',
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

    public function show_data($request,$index){

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');

        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\transfer\Model::toDataTable2($index),
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
