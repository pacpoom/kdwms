<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\querymaterial;

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
    public function render($request,$material,$total_box,$total_qty)
    {

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/querymaterial/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Query Data}',
            'titleClass' => 'icon-profile',
        ));

        $fieldset->add('text', array(
            'id' => 'raw',
            'labelClass' => 'g-input icon-category',
            'itemClass' => 'item',
            'label' => '{LNG_Material Number}',
            'value' => ''
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

        $fieldset->add('a', array(
            'id' => 'clear',
            'class' => 'button red icon-document',
            'value' => 'Label',
            'href' => WEB_URL.'index.php?module=wms-querymaterial&amp;time='. date('His') .'&amp;',
        ));

        $fieldset->add('hidden', array(
            'id' => 'login_user',
            'value' => isset($login['id']) ? $login['id'] : 0
        ));
    
        return $form->render();
    }

    public function show_data($request,$raw){

        //var_dump($index);

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');

        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\querymaterial\Model::getContainer($raw),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'hideCheckbox' => true,
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'actionCallback' =>'dataTableActionCallback',
            'headers' => array(
                'serial_number' => array(
                    'text' => '{LNG_Box ID}'
                ),
                'inbound_date' => array(
                    'text' => '{LNG_Received Date}'
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
