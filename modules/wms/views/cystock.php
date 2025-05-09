<?php

namespace wms\cystock;

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
            'model' => \wms\cystock\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','temp_material','container','case_number','box_id','storage_location'),
            'hideCheckbox' => true,
            'action' => 'index.php/wms/model/cystock/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
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
                'box_id' => array(
                    'text' => '{LNG_Box ID}'
                ),
                'temp_material' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'quantity' => array(
                    'text' => '{LNG_Quantity}'
                ),
                'delivery_order' => array(
                    'text' => '{LNG_Delivery Type}'
                ),
                'container_received' => array(
                    'text' => '{LNG_Received Date}'
                ),
                'storage_location' => array(
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
        $item['quantity'] = currency::format($item['quantity']);
        return $item;
    }
}