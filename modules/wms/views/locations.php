<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\locations;

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

       $category = \index\Category\Model::init(false);

        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/locations/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $form->add('header', array(
            'innerHTML' => '<h3 class=icon-product>{LNG_Add} {LNG_Location}</h3>'
        ));

        $fieldset = $form->add('fieldset');

        $fieldset->add('text', array(
            'id' => 'location_code',
            'labelClass' => 'g-input icon-shipping',
            'itemClass' => 'item',
            'label' => '{LNG_Location}',
            'readonly' => true,
            'value' => isset($index->location_code) ? $index->location_code : ''
        ));

        $groups = $fieldset->add('groups');

        $n = 0;
        foreach (Language::get('Location Zone', array()) as $key => $label) {
            $groups->add('text', array(
                'id' => $key,
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'width40',
                //'comment' => '{LNG_The contact project}',
                'label' => $label,
                'datalist' => $category->toSelect($key),
                'value' => isset($index->zone) ? $index->zone : 0
            ));
            $n++;
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
        }

        $n = 0;
        foreach (Language::get('Location Area', array()) as $key => $label) {
            $groups->add('text', array(
                'id' => $key,
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'width30',
                //'comment' => '{LNG_The contact project}',
                'label' => $label,
                'datalist' => $category->toSelect($key),
                'value' => isset($index->area) ? $index->area : 0
            ));
            $n++;
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
        }

        $n = 0;
        foreach (Language::get('Location Bin', array()) as $key => $label) {
            $groups->add('text', array(
                'id' => $key,
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'width30',
                //'comment' => '{LNG_The contact project}',
                'label' => $label,
                'datalist' => $category->toSelect($key),
                'value' => isset($index->bin) ? $index->bin : 0
            ));
            $n++;
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
        }

        $groups = $fieldset->add('groups');

        $n = 0;
        foreach (Language::get('Location_Type', array()) as $key => $label) {
            $groups->add('text', array(
                'id' => $key,
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'width50',
                //'comment' => '{LNG_The contact project}',
                'label' => $label,
                'datalist' => $category->toSelect($key),
                'value' => isset($index->location_type) ? $index->location_type : 0
            ));
            $n++;
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
        }

        $n = 0;
        foreach (Language::get('Warehouse', array()) as $key => $label) {
            $groups->add('text', array(
                'id' => $key,
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'width50',
                //'comment' => '{LNG_The contact project}',
                'label' => $label,
                'datalist' => $category->toSelect($key),
                'value' => isset($index->warehouse) ? $index->warehouse : 0
            ));
            $n++;
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
        }


        $fieldset->add('text', array(
            'id' => 'description',
            'labelClass' => 'g-input icon-shipping',
            'itemClass' => 'item',
            'label' => '{LNG_Description}',
            'maxlength' =>60,
            'value' => isset($index->description) ? $index->description : ''
        ));

        $fieldset->add('submit', array(
            'id' => 'save',
            'class' => 'button save icon-save',
            'value' => '{LNG_Save}'
        ));

        $fieldset->add('hidden', array(
            'id' => 'location_id',
            'value' => isset($index->id) ? $index->id : 0
        ));
        
        $modules = \Gcms\Modules::create();
        $dir = $modules->getDir();
        $form->script(file_get_contents($dir.'wms/wms.js'));
        // คืนค่า HTML
        return $form->render();
    }
}
