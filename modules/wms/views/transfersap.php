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
            'status' => $request->request('status')->toInt(),
            'container' => $request->request('container')->toString()
        );

        $export['from']=$request->request('from')->date();
        $export['to']=$request->request('to')->date();
        $export['status']=$request->request('status')->toInt();
        $export['container']=$request->request('container')->toString();

        $status = array(
            0 => 'All',
            1 => 'Received',
            2 => 'Not Receive',
            3 => 'Short Ship',
        );

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(

            'uri' => $uri,
            'model' => \wms\packinglist\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','material_number','container','case_number','box_id'),
            'hideCheckbox' => false,
            'action' => 'index.php/wms/model/packinglist/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
                array(
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => array(
                        'print' => '{LNG_Print}',
                        'short' => '{LNG_Short Shipment}',
                    )
                ),
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
                    'name' => 'status',
                    'text' => '{LNG_Status}',
                    'options' => $status,
                    'value' => $params['status']
                ),
                array(
                    'type' => 'text',
                    'name' => 'container',
                    'value' => $params['container'],
                    'placeholder'=> '{LNG_Container Number}'
                ),
            ),
            'headers' => array(
                'container' => array(
                    'text' => '{LNG_Container}'
                ),
                'case_number' => array(
                    'text' => '{LNG_Case Number}'
                ),
                'storage_location' => array(
                    'text' => '{LNG_Storage Location}'
                ),
                'container_received' => array(
                    'text' => '{LNG_Container Received}'
                ),
                'box_id' => array(
                    'text' => '{LNG_Box Number}'
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'material_name_en' => array(
                    'text' => '{LNG_Material Name Eng}'
                ),
                'unit' => array(
                    'text' => '{LNG_Unit}'
                ),
                'quantity' => array(
                    'text' => '{LNG_Quantity}'
                ),
                'receive_flg' => array(
                    'text' => '{LNG_Status}'
                )
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

        if ($item['receive_flg'] == 0) {
            $item['receive_flg'] = "<center><mark class=term2>{LNG_Waiting Receive}</mark></center>";
        } elseif ($item['receive_flg'] == 1)  {
            $item['receive_flg'] = "<center><mark class=term6>Received</mark></center>";
        } elseif ($item['receive_flg'] == 2) {
            $item['receive_flg'] = "<center><mark class=term3>Short Ship</mark></center>";
        }

        if ($item['storage_location'] == 1094) {
            $item['storage_location'] = "<center><mark class=term3>CKD</mark></center>";
        } else {
            $item['storage_location'] = "<center><mark class=term4>KD</mark></center>";
        }

        return $item;
    }
}