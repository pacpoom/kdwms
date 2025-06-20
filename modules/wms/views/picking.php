<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\picking;

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
    public function render($so,$status,$total_quantity,$actual_quantity,$pallet,$pallets)
    {

        $get_so = \wms\picking\Model::get_so();
        $sale_order = array();

        foreach ($get_so As $item) {
            $sale_order[$item['sale_order']] = $item['sale_order'];
        }

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/picking/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Ship Out}',
            'titleClass' => 'icon-profile',
        ));

        $groups = $fieldset->add('groups');

        if ($so == '') {
            $status = 0;
            $pallet = '';
            $total_quantity = 0;
            $actual_quantity = 0;
        }

        if($status == 0) {
            $read = false;
            $ship = true;
        } elseif ($status == 1) {
            $read = true;
            $ship = true;
            $pallets = false;
        } elseif ($status == 2) {
            $read = true;
            $ship = false;
            $pallets = true;
        }

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

        $groups->add('text', array(
            'id' => 'pallet',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Pallet No}',
            'value' => isset($pallet) ? $pallet : '',
            'readonly' => $pallets
        ));

        
        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'serial_number',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Box ID}',
            'value' => '',
            'autofocus' => true,
            'readonly' => $ship
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'qty',
            'labelClass' => 'g-input icon-gps',
            'itemClass' => 'width50',
            'label' => '{LNG_Total Box}',
            'disabled' => true,
            'value' => isset($total_quantity) ? $total_quantity : 0,
        ));

        $groups->add('number', array(
            'id' => 'ship_qty',
            'labelClass' => 'g-input icon-info',
            'itemClass' => 'width50',
            'label' => '{LNG_Quantity}',
            'disabled' => true,
            'value' => isset($actual_quantity) ? $actual_quantity : 0,
        ));

        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));

        $fieldset->add('submit', array(
            'class' => 'button save icon-verfied',
            'value' => '{LNG_Show}'
        ));

        $fieldset->add('a', array(
            'id' => 'pallet',
            'class' => 'button orange icon-clip',
            'value' => 'Label',
            'href' => WEB_URL.'index.php#module=wms-picking&amp;time='. date('His') .'&amp;so='.$so.'&amp;pallets=1&amp;status=1&amp;',
        ));

        $fieldset->add('a', array(
            'id' => 'clear',
            'class' => 'button red icon-document',
            'value' => 'Label',
            'href' => WEB_URL.'index.php#module=wms-picking&amp;time='. date('His') .'&amp;',
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
            'model' => \wms\picking\Model::toDataTable($so),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('status'),
            'perPage' => $request->cookie('inventoryProject_perPage',10)->toInt(),
            'sort' => $request->cookie('inventoryProject_sort', 'id desc')->toString(),
            'actionCallback' =>'dataTableActionCallback',
            'headers' => array(
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'plan_qty' => array(
                    'text' => '{LNG_Plan Qty}',
                ), 
                'ship_qty' => array(
                    'text' => '{LNG_Ship Qty}',
                ),
                'diff' => array(
                    'text' => '{LNG_Difference}',
                ), 
            )
        ));

        setcookie('inventoryProject_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('inventoryProject_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);

        return $table->render();
    }

    public function onRow($item, $o, $prop)
    {
    
        $item['plan_qty'] = number_format((float)$item['plan_qty'], 1, '.', '');
        $item['ship_qty'] = number_format((float)$item['ship_qty'], 1, '.', '');
        $item['diff'] = $item['plan_qty'] - $item['ship_qty'];

        if ($item['diff'] == 0) {
        $item['material_number'] = ' <p class=bg-green>'. $item['material_number'] .'</p>';
        }

        return $item;
    }

}
?>
