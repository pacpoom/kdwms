<?php

namespace wms\shipstock;

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
            'model' => \wms\shipstock\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('sale_order','customer_code','customer_name','serial_number','material_number'),
            'hideCheckbox' => true,
            'action' => 'index.php/wms/model/shipstock/action',
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
                    'text' => '{LNG_Sale Order}',
                ),
                'customer_code' => array(
                    'text' => '{LNG_Customer Code}',
                ),
                'customer_name' => array(
                    'text' => '{LNG_Customer Name}',
                ),
                'serial_number' => array(
                    'text' => '{LNG_serial Number}',
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}',
                ),
                'quantity' => array(
                    'text' => '{LNG_Quantity}',
                ),
                'ship_date' => array(
                    'text' => '{LNG_Shipping Date}',
                ),
                'pallet_no' => array(
                    'text' => '{LNG_Pallet No}',
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