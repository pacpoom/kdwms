<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\pallet;

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
    public function render($so)
    {

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/pallet/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $form->add('header', array(
            'innerHTML' => '<h3 class=icon-product>{LNG_Add} {LNG_Pallet No}</h3>'
        ));

        $fieldset = $form->add('fieldset');

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'so',
            'labelClass' => 'g-input icon-shipping',
            'itemClass' => 'width50',
            'label' => '{LNG_Sale Order Number}',
            'readonly' => true,
            'value' => isset($so) ? $so : ''
        ));

        $groups = $fieldset->add('groups');

        $groups->add('date', array(
            'id' => 'delivery',
            'labelClass' => 'g-input icon-shipping',
            'itemClass' => 'width50',
            'label' => '{LNG_Delivery Date}',
            'value' => date('Y-m-d')
        ));

        $groups->add('number', array(
            'id' => 'number',
            'labelClass' => 'g-input icon-shipping',
            'itemClass' => 'width50',
            'label' => '{LNG_Quantity}',
            'value' => 0
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
