<?php

namespace wms\shipdetail;

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
            'model' => \wms\sale\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','declaration_no','serial_number','material_number','location_code'),
            'hideCheckbox' => true,
            'action' => 'index.php/wms/model/inventory/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
                array(
                    'class' => 'button orange icon-excel',
                    'id' => 'export&'.http_build_query($export),
                    'text' => '{LNG_Download}'
                )
            ),
            'headers' => array(
                'status' => array(
                    'text' => '{LNG_Status}'
                ),
                'sale_order' => array(
                    'text' => '{LNG_Sale Order Number}'
                ),
                'delivery_date' => array(
                    'text' => '{LNG_Delivery Date}'
                ),
                'customer_code' => array(
                    'text' => '{LNG_Customer Code}'
                ),
                'customer_name' => array(
                    'text' => '{LNG_Customer Name}'
                ),
                'ship_type' => array(
                    'text' => '{LNG_Ship Type}'
                ),
                'delivery_type' => array(
                    'text' => '{LNG_Delivery Type}'
                ),
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
        // $item['material_type'] = $this->category->get('material_type', $item['material_type']);
        // $item['actual_quantity'] = currency::format($item['actual_quantity']);
        return $item;
    }
}