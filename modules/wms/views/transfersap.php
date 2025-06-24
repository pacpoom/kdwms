<?php

namespace wms\transfersap;

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
            'action' => 'index.php/wms/model/transfersap/submit',
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
            'placeholder' => '{Upload CSV File} {ENCODE}',
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
            'href' => WEB_URL.'export.php?module=wms-export&amp;type=transfer_sap&amp;',
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


        $export = array();
        // ค่าที่ส่งมา
        $params = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
            'material_number' => $request->request('material_number')->toString()
        );

        $export['from']=$request->request('from')->date();
        $export['to']=$request->request('to')->date();
        $export['material_number']=$request->request('material_number')->toString();

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(

            'uri' => $uri,
            'model' => \wms\transfersap\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','material_number','source_location','receive_location'),
            'hideCheckbox' => false,
            'action' => 'index.php/wms/model/transfersap/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
                array(
                    'class' => 'button orange icon-excel',
                    'id' => 'export&'.http_build_query($export),
                    'text' => '{LNG_Download}'
                ),
            ),
            'filters' => array(
                array(
                'type' => 'date',
                'name' => 'from',
                'text' => '{LNG_From}',
                'value' => $params['from'],
                'placeholder' => 'วันเริ่ม'
                ),
                array(
                'type' => 'date',
                'name' => 'to',
                'text' => '{LNG_To}',
                'value' => $params['to'],
                'placeholder' => 'วันสิ้นสุด'
                ),
                array(
                    'type' => 'text',
                    'name' => 'material_number',
                    'value' => $params['material_number'],
                    'placeholder'=> '{LNG_Material Number}'
                ),
            ),
            'headers' => array(
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'source_location' => array(
                    'text' => '{LNG_Source Location}'
                ),
                'receive_location' => array(
                    'text' => '{LNG_Receive Location}'
                ),
                'qty' => array(
                    'text' => '{LNG_Quantity}'
                ),
                'tr_flg' => array(
                    'text' => '{LNG_Transfer Status}'
                ),
                'user_name' => array(
                    'text' => '{LNG_User Name}'
                ),
                'created_at' => array(
                    'text' => '{LNG_Created}',
                    'class' => 'date'
                ),
                'file_name' => array(
                    'text' => '{LNG_File Name}'
                ),
            )
        ));
                // save cookie
                setcookie('perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
                //setcookie('sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
                // คืนค่า HTML
                return $table->render();
    }

    public function onRow($item, $o, $prop)
    {
        return $item;
    }
}