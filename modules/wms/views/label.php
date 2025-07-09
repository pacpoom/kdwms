<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\label;

use Kotchasan\Language;
use Kotchasan\Currency;
use Gcms\Login;
use Kotchasan\Html;

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
    public function render($index, $login)
    {

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/label/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $form->add('header', array(
            'innerHTML' => '<h3 class=icon-product>{LNG_Print Label}</h3>'
        ));

        $fieldset = $form->add('fieldset');

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'material_number',
            'labelClass' => 'g-input icon-shipping',
            'itemClass' => 'width40',
            'label' => '{LNG_Material Number}',
            'readonly' => true,
            'value' => isset($index) ? $index->material_number : ''
        ));

        $groups->add('text', array(
            'id' => 'material_name',
            'labelClass' => 'g-input icon-shipping',
            'itemClass' => 'width60',
            'label' => '{LNG_Material Name}',
            'readonly' => true,
            'value' => isset($index) ? $index->material_name_en : ''
        ));

        $groups = $fieldset->add('groups');

        $groups->add('date', array(
            'id' => 'delivery',
            'labelClass' => 'g-input icon-shipping',
            'itemClass' => 'width30',
            'label' => '{LNG_Delivery Date}',
            'value' => date('Y-m-d')
        ));

        $groups->add('text', array(
            'id' => 'number',
            'labelClass' => 'g-input icon-shipping',
            'itemClass' => 'width30',
            'label' => '{LNG_Quantity}',
            'value' => 0
        ));

        $groups->add('number', array(
            'id' => 'page',
            'labelClass' => 'g-input icon-shipping',
            'itemClass' => 'width40',
            'label' => '{LNG_Page Quantity}',
            'value' => 1
        ));

        $fieldset->add('submit', array(
            'id' => 'save',
            'class' => 'button save icon-save',
            'value' => '{LNG_Save}'
        ));

        // คืนค่า HTML
        return $form->render();
    }
}
