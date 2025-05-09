<?php

namespace wms\inventory;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

class View extends \Gcms\View{
    private $category;
    public function render (Request $request){

        $params = array();
        $export = array();
        
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\inventory\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','serial_number','material_number','location_code','case_number'),
            'hideCheckbox' => false,
            'action' => 'index.php/wms/model/inventory/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
                array(
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => array(
                        'print' => '{LNG_Print}',
                    )
                ),
                array(
                    'class' => 'button orange icon-excel',
                    'id' => 'export&'.http_build_query($export),
                    'text' => '{LNG_Download}'
                )
            ),
            'headers' => array(
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
                'material_type' => array(
                    'text' => '{LNG_Material Type}'
                ),
                'actual_quantity' => array(
                    'text' => '{LNG_Quantity}'
                ),
                'unit' => array(
                    'text' => '{LNG_Unit}'
                ),
                'inbound_date' => array(
                    'text' => '{LNG_Received Date}'
                ),
                'location_code' => array(
                    'text' => '{LNG_Location}'
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
        $item['material_type'] = $this->category->get('material_type', $item['material_type']);
        $item['actual_quantity'] = currency::format($item['actual_quantity']);
        return $item;
    }
}