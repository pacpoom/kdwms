<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace product\boms;

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
    public function render($index,$login,$status)
    {

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/product/model/boms/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $form->add('header', array(
            'innerHTML' => '<h2 class=icon-product>{LNG_Add} {LNG_Bill of Material}</h2>'
        ));

        $fieldset = $form->add('fieldset');

        $get_item = \product\boms\Model::get(1);

        foreach ($get_item as $item) {
            $model[$item->id] = $item->material_number .' / '. $item->material_name_en;
        }

        if ($status == 1) {
            $realOnly = true;
        } else {
            $realOnly = false;
        }

        $fieldset->add('text', array(
            'id' => 'model',
            'labelClass' => 'g-input icon-category',
            'itemClass' => 'item',
            'label' => '{LNG_Finished Goods}',
            'datalist' => $model,
            'readonly' => $realOnly,
            'value' => isset($index->model_no) ? $index->model_no : 0
        ));

        $rm = $get_item = \product\boms\Model::get(3);

        foreach ($rm as $item){
            $raw[$item->id] = $item->material_number .' / '. $item->material_name_en .' / '.$item->unit;
        }

        $fieldset->add('text', array(
            'id' => 'raw',
            'labelClass' => 'g-input icon-category',
            'itemClass' => 'item',
            'label' => '{LNG_Component}',
            'datalist' => $raw,
            'readonly' => $realOnly,
            'value' => isset($index->material_id) ? $index->material_id : 0
        ));

        $groups = $fieldset->add('groups');

        $groups->add('currency',array(
            'id' => 'usage',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width50',
            'label' => '{LNG_Usage}',
            'value' => isset($index->usage) ? currency::format($index->usage) : Currency::format(0)
        ));

        $groups->add('text',array(
            'id' => 'unit',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width50',
            'label' => '{LNG_Unit}',
            'value' => ''
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
