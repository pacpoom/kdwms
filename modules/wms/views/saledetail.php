<?php

namespace wms\saledetail;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

class View extends \Gcms\View{

    private $category;

    public function render (Request $request){

        $params = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
            'sale_order' => $request->request('sale_order')->toString(),
            'customer' => $request->request('customer')->toString(),
            'status' => $request->request('status')->toInt(),
        );

        $export = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
            'sale_order' => $request->request('sale_order')->toString(),
            'customer' => $request->request('customer')->toString(),
            'status' => $request->request('status')->toInt(),
        );

        $status = array(
            0 => 'Pending',
            1 => 'Complete',
            2 => 'All'
        );
        
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(

            'uri' => $uri,
            'model' => \wms\saledetail\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('sale_order','material_number'),
            'hideCheckbox' => true,
            'action' => 'index.php/wms/model/saledetail/action',
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
                    array(
                        'name' => 'status',
                        'text' => '{LNG_Status}',
                        'options' => $status,
                        'value' => $params['status']
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'sale_order',
                        'value' => $params['sale_order'],
                        'placeholder' => '{LNG_Sale Order}'
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'customer',
                        'value' => $params['customer'],
                        'placeholder' => '{LNG_Customer No.}'
                    )
            ),
            'headers' => array(
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
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'planed_quantity' => array(
                    'text' => '{LNG_Order Qty}'
                ),
                'ship_qty' => array(
                    'text' => '{LNG_Ship Quantity}'
                ),
                'diff_qty' => array(
                    'text' => '{LNG_Difference Quantity}'
                ),
            ),
            'buttons' => array (
                'description' => array(
                    'class' => 'icon-world button blue',
                    'href' => $uri->createBackUri(array('module' => 'wms-detail', 'sale_order' => ':sale_order', 'material_number' => ':material_number')),
                    'title' => '{LNG_Detail}'
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

        $item['ship_qty'] = number_format((float)$item['ship_qty'], 1, '.', '');
        $item['planed_quantity'] = number_format((float)$item['planed_quantity'], 1, '.', '');
        $item['diff_qty'] = number_format((float)$item['planed_quantity'], 1, '.', '') - number_format((float)$item['ship_qty'], 1, '.', '');

        return $item;
    }
}