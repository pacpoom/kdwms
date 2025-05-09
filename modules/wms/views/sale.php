<?php

namespace wms\sale;

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
            'so' => $request->request('so')->toString(),
            'customer' => $request->request('customer')->toString(),
            'status' => $request->request('status')->toInt(),
        );

        $export = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
            'so' => $request->request('so')->toString(),
            'customer' => $request->request('customer')->toString(),
            'status' => $request->request('status')->toInt(),
        );

        $status = array(
            0 => 'Release',
            1 => 'Print',
            2 => 'Picking',
            3 => 'Truck Confirm',
            4 => 'Delivery',
            5 => 'All'
        );
        
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\sale\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('sale_order','delivery_date'),
            'hideCheckbox' => true,
            'action' => 'index.php/wms/model/sale/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
                // array(
                //     'class' => 'button orange icon-excel',
                //     'id' => 'export&'.http_build_query($export),
                //     'text' => '{LNG_Download}'
                // )
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
                        'name' => 'so',
                        'value' => $params['so'],
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
            'buttons' => array (
                'tag' => array(
                    'class' => 'icon-list button Red',
                    'id' => ':sale_order',
                    'title' => '{LNG_Print Pallet}'
                ),
                'truck' => array(
                    'class' => 'icon-wallet button purple',
                    'id' => ':sale_order',
                    'title' => '{LNG_Print Truck}'
                ),
                'order' => array(
                    'class' => 'icon-menus button orange',
                    'target' => '_blank',
                    'href' => $uri->createBackUri(array('module' => 'wms-saledetail', 'sale_order' => ':sale_order')),
                    'title' => '{LNG_Order details}'
                ),
                'description' => array(
                    'class' => 'icon-world button blue',
                    'target' => '_blank',
                    'href' => $uri->createBackUri(array('module' => 'wms-detail', 'sale_order' => ':sale_order')),
                    'title' => '{LNG_Detail}'
                ),
                'picking' => array(
                    'class' => 'icon-list button green',
                    'target' => '_blank',
                    'href' => 'https://sail.anjinyk.co.th/pdf/kd-picking-order-sheet?sale_order=:sale_order',
                    'title' => '{LNG_Picking Sheet}'
                )
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
        if ($item['status'] == 0){
            $item['status'] = "<center><mark class=term2>Release</mark></center>";
        } elseif ($item['status'] == 1) {
            $item['status'] = "<center><mark class=term6>Print</mark></center>";
        } elseif ($item['status'] == 2) {
            $item['status'] = "<center><mark class=term3>Picking</mark></center>";
        } elseif ($item['status'] == 3) {
            $item['status'] = "<center><mark class=term6>Truck Confirm</mark></center>";
        } elseif ($item['status'] == 4) {
            $item['status'] = "<center><mark class=term6>Delivery</mark></center>";
        }
        
        return $item;
    }
}