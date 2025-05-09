<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace product\joborders;

use Kotchasan\Language;
use Kotchasan\Currency;
use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;


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
            'action' => 'index.php/product/model/joborders/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $form->add('header', array(
            'innerHTML' => '<h2 class=icon-product>{LNG_Create Job Order}</h2>'
        ));

        $fieldset = $form->add('fieldset');

        $get_item = \product\joborders\Model::get(1);

        foreach ($get_item as $item) {
            $model[$item->id] = $item->material_number .' / '. $item->material_name_en .' / '. $item->unit;
        }

        if ($status == 1) {
            $realOnly = true;
        } else {
            $realOnly = false;
        }

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'model',
            'labelClass' => 'g-input icon-category',
            'itemClass' => 'width70',
            'label' => '{LNG_Finished Goods}',
            'datalist' => $model,
            'readonly' => $realOnly,
            'value' => isset($index->model_no) ? $index->model_no : 0
        ));

        $groups->add('text',array(
            'id' => 'unit',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width30',
            'label' => '{LNG_Unit}',
            'readonly' => true
        ));

        $groups = $fieldset->add('groups');

        $groups->add('date',array(
            'id' => 'production_date',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width60',
            'label' => '{LNG_Production Date}',
            'value' => date('Y-m-d')
        ));
     
        $groups->add('number',array(
            'id' => 'plan',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width40',
            'label' => '{LNG_Plan Quantity}',
            'value' => ''
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text', array(
            'id' => 'po',
            'labelClass' => 'g-input icon-category',
            'itemClass' => 'width60',
            'label' => '{LNG_Purchase Order}',
            'value' => ''
        ));

        $groups->add('date',array(
            'id' => 'delivery_date',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width40',
            'label' => '{LNG_Delievry Date}',
            'value' => date('Y-m-d')
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
        $form->script(file_get_contents($dir.'product/joborders.js'));

        // คืนค่า HTML
        return $form->render();
    }
}
