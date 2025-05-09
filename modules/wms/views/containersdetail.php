<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\containersdetail;

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
    public function render($index,$login)
    {

        $inspection = array(
            0 => 'No',
            1 => 'Yes'
        );

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/containersdetail/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $status = $index[0]->status;
        
        if ($status == 0) {
            $readOnly = false ;
        } else {
            $readOnly = true ;
        }

        $size = array(
            1 => '20',
            2 => '40',
        );

        $form->add('header', array(
            'innerHTML' => '<h3 class=icon-product>{LNG_Container Receive}</h3>'
        ));

        $fieldset = $form->add('fieldset');

        $groups = $fieldset->add('groups');

        $groups->add('text',array(
            'id' => 'container',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width40',
            'label' => '{LNG_Container}',
            'readonly' => true,
           'value' => isset($index[0]->container) ? $index[0]->container : ''
        ));

        $groups->add('date',array(
            'id' => 'receive_date',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width30',
            'label' => '{LNG_Receive Date}',
            'readonly' => $readOnly,
            'value' => isset($index[0]->receive_date) ? $index[0]->receive_date : date('Y-m-d')
        ));

        $groups->add('text',array(
            'id' => 'bl',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width30',
            'label' => '{LNG_Container BL}',
            'value' => isset($index[0]->container_bl) ? $index[0]->container_bl : ''
        ));
        
        $groups = $fieldset->add('groups');

        $groups->add('number',array(
            'id' => 'year',
            'labelClass' => 'g-input icon-tags',
            'itemClass' => 'width30',
            'label' => '{LNG_Year Lot}',
            'value' => isset($index[0]->year_lot) ? $index[0]->year_lot : date('Y')
        ));

        $groups->add('number',array(
            'id' => 'week',
            'labelClass' => 'g-input icon-tags',
            'itemClass' => 'width30',
            'label' => '{LNG_Week Lot}',
            'value' =>  isset($index[0]->week_lot) ? $index[0]->week_lot : date('W')
        ));

        $groups->add('text',array(
            'id' => 'lot',
            'labelClass' => 'g-input icon-tags',
            'itemClass' => 'width40',
            'label' => '{LNG_Lot Number}',
            'value' =>  isset($index[0]->lot_no) ? $index[0]->lot_no : ''
        ));

        $groups = $fieldset->add('groups');

        $groups->add('date',array(
            'id' => 'eta',
            'labelClass' => 'g-input icon-tags',
            'itemClass' => 'width50',
            'label' => '{LNG_ETA Date}',
            'value' => isset($index[0]->eta_date) ? $index[0]->eta_date : date('Y-m-d')
        ));

        $groups->add('date',array(
            'id' => 'ata',
            'labelClass' => 'g-input icon-tags',
            'itemClass' => 'width50',
            'label' => '{LNG_ATA Date}',
            'value' =>  isset($index[0]->ata_date) ? $index[0]->ata_date : date('Y-m-d')
        ));

       $groups = $fieldset->add('groups');

        $groups->add('text',array(
            'id' => 'size',
            'labelClass' => 'g-input icon-tags',
            'itemClass' => 'width50',
            'label' => '{LNG_Container Size}',
            'datalist' => $size,
            'value' => isset($index[0]->container_size) ? $index[0]->container_size : 2
        ));

        $groups->add('text',array(
            'id' => 'model',
            'labelClass' => 'g-input icon-tags',
            'itemClass' => 'width50',
            'label' => '{LNG_Model}',
            'value' =>  isset($index[0]->model) ? $index[0]->model : ''
        ));

        $fieldset->add('submit', array(
            'id' => 'save',
            'class' => 'button save icon-save',
            'value' => '{LNG_Save}'
        ));

        $fieldset->add('hidden', array(
            'id' => 'material_id',
            'value' => isset($index->id) ? $index->id : 0
        ));
        
        // คืนค่า HTML
        return $form->render();
    }
}
