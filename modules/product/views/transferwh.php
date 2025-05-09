<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace product\transferwh;

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
    public function render($job_order,$po,$delivery,$material_name,$location_code,$status)
    {

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/product/model/transferwh/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Transfer Material}',
            'titleClass' => 'icon-profile',
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'serial_number',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'placeholder' => 'Scan Qr Code',
            'value' => '',
            'autofocus' => true
        ));

        if ($status == 1) {
            $readonly = true;
        } else {
            $readonly = false;
        }

        $groups->add('text', array(
            'id' => 'job_order',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'placeholder' => 'Production No.',
            'value' => '',
            'autofocus' => true
        ));


        $category = \wms\locationdata\Model::init(false);

        $groups = $fieldset->add('groups');
        
        $n = 0;
        foreach (Language::get('INVENTORY_LOCATION', array()) as $key => $label) {
            $groups->add('text', array(
                'id' => 'location_code',
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'width30',
                'label' => $label,
                'datalist' => $category->toSelect($key),
                'value' => 513,
            ));
            $n++;
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
        }

        $groups->add('text', array(
            'id' => 'po',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width40',
            'label' => '{LNG_Purchase Order}',
            'disabled' => true,
            'value' => isset($po) ? $po : '',
        ));

        $groups->add('date', array(
            'id' => 'delivery',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width30',
            'label' => '{LNG_Delievry Date}',
            'disabled' => true,
            'value' => isset($delivery) ? $delivery : 0,
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
            'href' => WEB_URL.'index.php#module=product-transferwh',
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
            'model' => \product\transferwh\Model::toDataTable($index),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'hideCheckbox' => true,
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'actionCallback' =>'dataTableActionCallback',
            'headers' => array(
                'material_number' => array(
                    'text' => '{LNG_Finished Goods}'
                ),
                'plan' => array(
                    'text' => '{LNG_Plan Quantity}'
                ), 
                'total_production' => array(
                    'text' => '{LNG_Total Production}'
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
