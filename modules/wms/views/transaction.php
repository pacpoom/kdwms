<?php

namespace wms\transaction;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

class View extends \Gcms\View{
    private $category;
    public function render (Request $request){

        $params = array();
        $export = array();
        
        $params = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
        );

        $export['from']=$request->request('from')->date();
        $export['to']=$request->request('to')->date();

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\transaction\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'transaction_date')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','transaction_type','container','case_number','serial_number','material_number','material_name_en','quantity','unit','from_location','location_code','username'),
            'hideCheckbox' => true,
            'action' => 'index.php/wms/model/transaction/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
                array(
                    'class' => 'button orange icon-excel',
                    'id' => 'export&'.http_build_query($export),
                    'text' => '{LNG_Download}'
                )
            ),
            'filters' => array(
                array(
                'type' => 'date',
                'name' => 'from',
                'text' => '{LNG_from}',
                'value' => $params['from'],
                'placeholder' => 'วันเริ่ม'
                ),
                array(
                'type' => 'date',
                'name' => 'to',
                'text' => '{LNG_to}',
                'value' => $params['to'],
                'placeholder' => 'วันสิ้นสุด'
                ),
            ),
            'headers' => array(
                'transaction_date' => array(
                    'text' => '{LNG_Transection Date}'
                ),
                'transaction_type' => array(
                    'text' => '{LNG_Transection Type}'
                ),
                'container' => array(
                    'text' => '{LNG_Container Number}'
                ),
                'case_number' => array(
                    'text' => '{LNG_Case Number}'
                ),
                'serial_number' => array(
                    'text' => '{LNG_Box ID}'
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'material_name_en' => array(
                    'text' => '{LNG_Material Name Eng}'
                ),
                'quantity' => array(
                    'text' => '{LNG_Quantity}'
                ),
                'unit' => array(
                    'text' => '{LNG_Unit}'
                ),
                'from_location' =>array(
                    'text' => '{LNG_From Location}'
                ),
                'location_code' => array(
                    'text' => '{LNG_Location}'
                ),
                'username' => array(
                    'text' => '{LNG_Username}'
                )
            ),
        ));
                // save cookie
                setcookie('perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
                setcookie('sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
                // คืนค่า HTML
                return $table->render();
    }

    public function onRow($item, $o, $prop)
    {
        $item['quantity'] = currency::format($item['quantity']);
        return $item;
    }
}