<?php

namespace wms\pallets;

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
            'sale_order' => $request->request('sale_order')->toString(),
            'customer' => $request->request('customer')->toString(),
        );

        $export = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
            'sale_order' => $request->request('sale_order')->toString(),
            'customer' => $request->request('customer')->toString(),
        );
        
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\pallets\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','sale_order','customer','location_code'),
            'hideCheckbox' => false,
            'action' => 'index.php/wms/model/pallets/action',
            'actionCallback' =>'dataTableActionCallback',
             'actions' => array(
                array(
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => array(
                        'print' => '{LNG_Print}',
                    )
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
                    'text' => '{LNG_sale order}'
                ),
                'delivery_date' => array(
                    'text' => '{LNG_Delivery Date}',
                ),
                'customer' => array(
                    'text' => '{LNG_Customer No.}',
                ),
                'location_code' => array(
                    'text' => '{LNG_Location Code}'
                ),
                'truck_id' => array(
                    'text' => '{LNG_Truck ID}'
                ),
                'truck_flg' => array(
                    'text' => '{LNG_Truck Confirm}',
                ),
                'truck_date' => array(
                    'text' => '{LNG_Truck Date}',
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
        if ($item['truck_flg'] == 1) {
            $item['truck_flg'] = '<center><p class=bg-green>Confirmed</p></center>';
        } else {
            $item['truck_flg'] = '<center>Not Confirmed</center>';
        }
        
        return $item;
    }
}