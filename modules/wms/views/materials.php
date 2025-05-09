<?php
/**
 * @filesource modules/repair/views/action.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\materials;

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
            'action' => 'index.php/wms/model/materials/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $form->add('header', array(
            'innerHTML' => '<h3 class=icon-product>{LNG_Add} {LNG_Material Master}</h3>'
        ));

        $fieldset = $form->add('fieldset');

        $img_dir = isset($index->id) ? $index->id : 0;

        if (is_file(ROOT_PATH.DATA_FOLDER.'material/'.$img_dir.'.jpg')) {
            $img = WEB_URL.DATA_FOLDER.'material/'.$index->id.'.jpg';
        } else {
            $img = WEB_URL.'skin/img/noicon.png';
        }

        $fieldset->add('file', array(
            'id' => 'image_upload',
            'itemClass' => 'item',
            'labelClass' => 'g-input icon-thumbnail',
            'label' => '{LNG_Image}{LNG_Material Number}',
            'dataPreview' => 'imgPicture',
            'previewSrc' => $img,
            'accept' => array('jpg', 'jpeg', 'png'),
        ));

        $fieldset->add('text', array(
            'id' => 'material_number',
            'labelClass' => 'g-input icon-index',
            'itemClass' => 'item',
            'label' => '{LNG_Material Number}',
            'value' => isset($index->material_number) ? $index->material_number : ''
        ));

        $groups = $fieldset->add('groups');

        $groups->add('text',array(
            'id' => 'material_name_en',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width50',
            'label' => '{LNG_Material Name Eng}',
            'value' => isset($index->material_name_en) ? $index->material_name_en : ''
        ));

        $groups->add('text',array(
            'id' => 'material_name_thai',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width50',
            'label' => '{LNG_Material Name Thai}',
            'value' => isset($index->material_name_thai) ? $index->material_name_thai : ''
        ));

        $groups = $fieldset->add('groups');

        $category = \wms\categoryunit\Model::init(false);

        $n = 0;
        foreach (Language::get('INVENTORY_UNIT', array()) as $key => $label) {
            $groups->add('text', array(
                'id' => 'unit',
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'width50',
                'label' => $label,
                'datalist' => $category->toSelect($key),
                'value' => isset($index->{$key}) ? $index->{$key} : 0,
            ));
            $n++;
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
        }

        //var_dump($index);

        $groups->add('text',array(
            'id' => 'net_weight',
            'labelClass' => 'g-input icon-tags',
            'itemClass' => 'width50',
            'label' => '{LNG_Net Weight}',
            'value' => isset($index->net_weight) ? $index->net_weight : 0
        ));

       // var_dump(Currency::format($index->net_weight));

        $groups = $fieldset->add('groups');

        $groups->add('text',array(
            'id' => 'unit_price',
            'labelClass' => 'g-input icon-tags',
            'itemClass' => 'width50',
            'label' => '{LNG_Prices}',
            'value' => isset($index->unit_price) ? $index->unit_price : 0
        ));

        $currency = \wms\materials\Model::get_currency();
        $result = array();
        foreach ($currency as $values){
            $result[$values->id] = $values->currency;
        }

        $groups->add('select',array(
            'id' => 'currency',
            'labelClass' => 'g-input icon-location',
            'itemClass' => 'width50',
            'label' => '{LNG_Currency unit}',
            'options' => $result
        ));

        $category = \index\Category\Model::init(false);

        $groups = $fieldset->add('groups');

        $n = 0;
        foreach (Language::get('Material_Type', array()) as $key => $label) {
            $groups->add('text', array(
                'id' => $key,
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'width50',
                //'comment' => '{LNG_The contact project}',
                'label' => $label,
                'datalist' => $category->toSelect($key),
                'value' => isset($index->material_type) ? $index->material_type : 0
            ));
            $n++;
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
        }

        $groups->add('text', array(
            'id' => 'qc',
            'labelClass' => 'g-input icon-world',
            'itemClass' => 'width50',
            'label' => '{LNG_Quality Control}',
            'datalist' => $inspection,
            'value' => isset($index->inspection_flg) ? $index->inspection_flg : 0
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
        
        $modules = \Gcms\Modules::create();
        $dir = $modules->getDir();
        $form->script(file_get_contents($dir.'wms/wms.js'));
        // คืนค่า HTML
        return $form->render();
    }
}
