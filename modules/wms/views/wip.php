<?php

namespace wms\wip;

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
            'model' => \wms\wip\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','material_number'),
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
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'material_name_en' => array(
                    'text' => '{LNG_Material Name Eng}'
                ),
                'material_name_thai' => array(
                    'text' => '{LNG_Material Name Thai}'
                ),
                'quantity' => array(
                    'text' => '{LNG_Quantity}'
                ),
                'unit' => array(
                    'text' => '{LNG_Unit}'
                ),
                'unit_price' => array(
                    'text' => '{LNG_Prices}'
                ),
                'currency' => array(
                    'text' => '{LNG_Currency unit}'
                ),
                'Amount' => array(
                    'text' => '{LNG_Amount}'
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
        $item['Amount'] = currency::format((int)$item['quantity'] * (int)$item['unit_price']);
        $item['quantity'] = currency::format($item['quantity']);
        return $item;
    }
}