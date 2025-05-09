<?php

namespace wms\detail;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

class View extends \Gcms\View{
    private $category;

    public function render (Request $request,$sale_order,$material_number){

        $params = array(
            'sale_order' => $sale_order,
            'material_number' => $material_number
        );
        $export = array(
            'sale_order' => $sale_order
        );
        
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\detail\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','serial_number','material_number','location_code'),
            'hideCheckbox' => true,
            'action' => 'index.php/wms/model/detail/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
                array(
                    'class' => 'button orange icon-excel',
                    'id' => 'export&'.http_build_query($export),
                    'text' => '{LNG_Download}'
                )
            ),
            'headers' => array(
                'sale_order' => array(
                    'text' => '{LNG_Sale Order Number}'
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'serial_number' => array(
                    'text' => '{LNG_Box ID}'
                ),
                'quantity' => array(
                    'text' => '{LNG_Quantity}'
                ),
                'location_code' => array(
                    'text' => '{LNG_Location}'
                ),
                'pick' => array(
                    'text' => '{LNG_Pick Box}'
                ),
                'ship_date' => array(
                    'text' => '{LNG_Ship Date}'
                ),
                'pallet_no' => array(
                    'text' => '{LNG_Pallet No}'
                ),
                'truck_id' => array(
                    'text' => '{LNG_Truck ID}'
                ),
                'confirm_flg' => array(
                    'text' => '{LNG_Print Truck}'
                ),
                'confirm_date' => array(
                    'text' => '{LNG_Print Date}'
                ),
                'file_name' => array(
                    'text' => '{LNG_File Name}'
                ),
            )
        ));
                // save cookie
                setcookie('perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
                setcookie('sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
                // คืนค่า HTML
                return $table->render();
    }

    public function onRow($item, $o, $prop)
    {
        return $item;
    }
}