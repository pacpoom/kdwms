<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace product\pulling;

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
    public function render($request,$rounting_id,$min,$max,$material,$material_name,$status)
    {
        // $ccl = \product\pulling\Model::getLine();

        // foreach ($ccl as $item){
        //     $Job[$item->id] = $item->job_no;
        // }
        $Job = array();

        if ($status == 1) {
            $readonly = true;
            $text = '{LNG_Pulling}';
        } else {
            $readonly = false;
            $text = '{LNG_Show Data}';
        }

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/product/model/pulling/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Pulling}',
            'titleClass' => 'icon-profile',
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'serial_number',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width60',
            'placeholder' => 'Scan Qr Code',
            'value' => '',
            'autofocus' => true
        ));

        $groups->add('text', array(
            'id' => 'rounting',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width40',
            'placeholder' => 'Routing No.',
            'datalist' => $Job,
            'value' => 1,
            'readonly' => $readonly
        ));
    
        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'material_number',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width40',
            'label' => '{LNG_Material Number}',
            'disabled' => true,
            'value' => isset($index->material_number) ? $index->material_number : '',
        ));

        $groups->add('text', array(
            'id' => 'material_name',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width60',
            'label' => '{LNG_Material Name Eng}',
            'disabled' => true,
            'value' => isset($index->material_name_en) ? $index->material_name_en : '',
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'min',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width30',
            'label' => '{LNG_Min}',
            'disabled' => true,
            'value' => isset($index->plan) ? $index->plan : 0,
        ));

        $groups->add('text', array(
            'id' => 'max',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width30',
            'label' => '{LNG_Max}',
            'disabled' => true,
            'value' => isset($index->plan) ? $index->plan : 0,
        ));

        $groups->add('text', array(
            'id' => 'pending',
            'labelClass' => 'g-input icon-next',
            'itemClass' => 'width40',
            'label' => '{LNG_Pending Quantity}',
            'disabled' => true,
            'value' => isset($index->plan) ? $index->plan : 0,
        ));

        $groups = $fieldset->add('groups');

        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));

        $fieldset->add('submit', array(
            'class' => 'button blue icon-news',
            'value' => $text
        ));

        $fieldset->add('a', array(
            'id' => 'clear',
            'class' => 'button red icon-document',
            'value' => 'Label',
            'href' => WEB_URL.'index.php#module=product-pulling',
        ));

        $fieldset->add('hidden', array(
            'id' => 'login_user',
            'value' => isset($login['id']) ? $login['id'] : 0
        ));

        $fieldset->add('hidden',array(
            'id' => 'status',
            'value' => isset($status) ? $status : 0,
        ));
    
        $modules = \Gcms\Modules::create();
        $dir = $modules->getDir();

        $fieldset->add('hidden',array(
            'id' => 'url',
            'value' => isset($dir) ? $dir : '',
        ));

        //$form->script(file_get_contents($dir.'product/transferpd.js'));

        return $form->render();
    }

    public function show_data($request,$index){

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');

        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \product\transferpd\Model::toDataTable2($index),
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
                'actual_quantity' => array(
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
