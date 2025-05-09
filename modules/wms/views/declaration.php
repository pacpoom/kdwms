<?php

namespace wms\declaration;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\UploadedFile;
use Kotchasan\Language;

class View extends \Gcms\View{
    private $category;

    public function import(Request $request)
    {
        // form
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/declaration/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset');

        // import
        $fieldset->add('file',array(
            'id' => 'import',
            'labelClass' => 'g-input icon-excel',
            'itemClass' => 'item',
            'label' => '{LNG_Browse file}',
            'placeholder' => 'Import CCL List {ENCODE}',
            'comment' => '{LNG_File size is less than :size}',
            'accept' => array('csv')
        ));

        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save icon-import',
            'value' => '{LNG_Import}'
        ));

        $fieldset->add('a',array(
            'id' => 'export',
            'class' => 'button icon-book',
            'href' => WEB_URL.'export.php?module=wms-export&amp;type=ccl&amp;',
            'target' => '_blank'
        ));

        // type
        $fieldset->add('hidden', array(
            'id' => 'type',
            'value' => 'grade'
        ));
        \Gcms\Controller::$view->setContentsAfter(array(
            '/:size/' => UploadedFile::getUploadSize(),
            '/{ENCODE}/' => Language::find('CSV_ENCODING', '', self::$cfg->csv_language)
        ));
        // คืนค่า HTML Form
        return $form->render();
    }

    public function render (Request $request){

        $params = array();
        $export = array();
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(

            'uri' => $uri,
            'model' => \wms\declaration\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','material_number','invoice_no'),
            'hideCheckbox' => true,
            'action' => 'index.php/wms/model/declaration/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
                array(
                    'id' => 'add',
                    'class' => 'button green icon-new',
                    'text' => '{LNG_Add}{LNG_Declaration}',
                ),
                array(
                    'class' => 'button orange icon-excel',
                    'id' => 'export&'.http_build_query($export),
                    'text' => '{LNG_Download}'
                )
            ),
            'headers' => array(
                'declaration_no' => array(
                    'text' => '{LNG_Declaration No}'
                ),
                'invoice_no' => array(
                    'text' => '{LNG_Invoice No}'
                ),
                'date_transmit' => array(
                    'text' => '{LNG_Date Transmit}'
                ),
                'item_no' => array(
                    'text' => 'Item No'
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'material_name_en' => array(
                    'text' => '{LNG_Material Name Eng}'
                ),
                'material_name_thai' => array(
                    'text' => '{LNG_Material Name Thai}'
                ),
                'unit' => array(
                    'text' => '{LNG_Unit}'
                ),
                'quantity' => array(
                    'text' => '{LNG_Quantity}'
                ),
                'actual_quantity' => array(
                    'text' => '{LNG_Received Quantity}'
                ),
                'net_weight' => array(
                    'text' => '{LNG_Net Weight}'
                ),
                'sum_weight' => array(
                    'text' => '{LNG_Sum Net Weight}'
                ),
                'unit_price' => array(
                    'text' => '{LNG_Prices}'
                ),
                'sum_price' => array(
                    'text' => '{LNG_Amount}'
                ),
                'currency' => array(
                    'text' => '{LNG_Currency unit}'
                )
            ),
            'buttons' => array(
                'statusd' => array(
                    'class' => 'icon-list button orange',
                    'id' => ':id',
                    'title' => '{LNG_Description}'
                ))
        ));
                // save cookie
                setcookie('perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
                //setcookie('sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
                // คืนค่า HTML
                return $table->render();
    }

    public function onRow($item, $o, $prop)
    {
        $sumQty = \wms\declaration\Model::sumQty($item['actual_quantity']);

        $item['sum_price'] = $item['quantity'] * $item['unit_price'];
        $item['sum_weight'] = $item['quantity'] * $item['net_weight'] .' KGM';
        $item['actual_quantity'] = currency::format($sumQty[0]->qty);
        return $item;
    }
}