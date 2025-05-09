<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\truck;

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
    public function render($so,$status,$truck)
    {


        $get_so = \wms\truck\Model::get_so();
        $sale_order = array();

        foreach ($get_so As $item) {
            $sale_order[$item['sale_order']] = $item['sale_order'];
        }

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/truck/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Truck Confirm}',
            'titleClass' => 'icon-profile',
        ));

        $groups = $fieldset->add('groups');

        if($status == 0) {
            $read = false;
            $ship = true;
            $truck_read = true;
        } elseif ($status == 1) {
            $read = true;
            $truck_read = false;
            $ship = true;
        } elseif ($status == 2) {
            $read = true;
            $truck_read = true;
            $ship = false;
        }

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'pallet',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Pallet No}',
            'value' => '',
            'autofocus' => true,
            'readonly' => $ship
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'truck',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Truck ID}',
            'value' => isset($truck) ? $truck : '',
            'autofocus' => true,
            'readonly' => $truck_read
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'so',
            'labelClass' => 'g-input icon-world',
            'itemClass' => 'width50',
            'label' => '{LNG_Sale Order Number}',
            'datalist' => $sale_order,
            'value' => isset($so) ? $so : '',
            'readonly' => $read
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
            'href' => WEB_URL.'index.php#module=wms-truck&amp;time='. date('His') .'&amp;',
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
            'model' => \wms\truck\Model::toDataTable($so),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'perPage' => $request->cookie('inventoryProject_perPage',10)->toInt(),
            'sort' => $request->cookie('inventoryProject_sort', 'id desc')->toString(),
            'actionCallback' =>'dataTableActionCallback',
            'headers' => array(
                'sale_order' => array(
                    'text' => '{LNG_Sale Order Number}'
                ),
                'pallet_no' => array(
                    'text' => '{LNG_Pallet No}'
                )
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
