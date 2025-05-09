<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace product\rountings;

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
    public function render($request,$status,$index)
    {

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/product/model/rountings/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $form->add('header', array(
            'innerHTML' => '<h2 class=icon-product>{LNG_Add}{LNG_Routing}</h2>'
        ));

        $fieldset = $form->add('fieldset');

        $get_item = array();
        $rounting = array();
        
        $get_item = \product\rountings\Model::get();

        $rounting = \product\rountings\Model::getRounting();

        foreach ($get_item as $item) {
            $model[$item->id] = $item->material_number .' / '. $item->material_name_en;
        }

        foreach ($rounting as $item) {
            $line[$item->category_id] = $item->topic;
        }

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'rounting',
            'labelClass' => 'g-input icon-category',
            'itemClass' => 'width50',
            'label' => '{LNG_Routing}',
            'datalist' => $line,
            //'readonly' => $realOnly,
            'value' => isset($index->routing_id) ? $index->routing_id : 0
        ));

        $groups->add('text', array(
            'id' => 'material',
            'labelClass' => 'g-input icon-category',
            'itemClass' => 'width50',
            'label' => '{LNG_Material Number}',
            'datalist' => $model,
            //'readonly' => $realOnly,
            'value' => isset($index->material_id) ? $index->material_id : 0
        ));

        $groups = $fieldset->add('groups');

        $groups->add('number',array(
            'id' => 'min',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width50',
            'label' => '{LNG_Min}',
            'value' => isset($index->min) ? $index->min : 0
        ));

        $groups->add('number',array(
            'id' => 'max',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width50',
            'label' => '{LNG_Max}',
            'value' => isset($index->max) ? $index->max : 0
        ));

        $fieldset->add('submit', array(
            'id' => 'save',
            'class' => 'button save icon-save',
            'value' => '{LNG_Save}'
        ));

        $fieldset->add('hidden', array(
            'id' => 'status',
            'value' => isset($status) ? $status : 0
        ));

        $fieldset->add('hidden',array(
            'id' => 'id',
            'value' => isset($index->id) ? $index->id : 0
        ));
        
                
        $modules = \Gcms\Modules::create();
        $dir = $modules->getDir();
        $form->script(file_get_contents($dir.'product/boms.js'));

        // คืนค่า HTML
        return $form->render();
    }
}
