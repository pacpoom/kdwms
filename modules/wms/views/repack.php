<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\repack;

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
    public function render($material,$status,$quantity,$box,$destination)
    {

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/repack/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Repackage}',
            'titleClass' => 'icon-profile',
        ));

        $groups = $fieldset->add('groups');

        if($status == 0) {
            $des = true;
            $read = true;
            $location = false;
        } elseif ($status == 1) {
            $des = false;
            $read = false;
            $location = true;
        } elseif ($status == 2) {
            $des = true;
        }

        $groups->add('text', array(
            'id' => 'serial_number',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width70',
            'placeholder' => 'Source Box ID',
            'label' => '{LNG_Source Box ID}',
            'value' => '',
            'autofocus' => $read,
            'readonly' => $location,
            'value' => isset($box) ? $box : '',
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'material_number',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width70',
            'label' => '{LNG_Material Number}',
            'readonly' => true,
            'placeholder' => '{LNG_Material Number}',
            'value' => isset($material) ? $material : '',
        ));

        $groups->add('text', array(
            'id' => 'qty',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width70',
            'label' => '{LNG_Quantity}',
            'readonly' => true,
            'placeholder' => '{LNG_Quantity}',
            'value' => isset($quantity) ? $quantity : 0,
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'destination',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width70',
            'placeholder' => 'Destination Box ID',
            'label' => '{LNG_Destination Box ID}',
            'value' => '',
            'autofocus' => $location,
            'readonly' => $des,
            'value' => isset($destination) ? $destination : '',
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'des_material_number',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width70',
            'label' => '{LNG_Destination Material Number}',
            'readonly' => true,
            'placeholder' => '{LNG_Destination Material Number}',
            'value' => isset($material_number) ? $material_number : '',
        ));

        $groups->add('number', array(
            'id' => 'des_qty',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width70',
            'label' => '{LNG_Quantity}',
            'readonly' => true,
            'placeholder' => '{LNG_Quantity}',
            'value' => isset($qty) ? $qty : 0,
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
            'href' => WEB_URL.'index.php#module=wms-repack',
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
