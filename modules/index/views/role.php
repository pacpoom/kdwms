<?php

namespace index\role;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

class View extends \Gcms\View{
    private $category;
    public function render (Request $request){

        $params = array();

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        
        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \index\role\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','location_code'),
            'action' => 'index.php/wms/model/location/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(array(
                'id' => 'action',
                'class' => 'ok',
                'text' => '{LNG_With selected}',
                'options' => array(
                    'delete' => '{LNG_Delete}'
                )
            ),
                array(
                    'id' => 'addrole',
                    'class' => 'button green icon-new',
                    'text' => '{LNG_Add}{LNG_Role master}',
                )
            ),
            'headers' => array(
                'location_code' => array(
                    'text' => '{LNG_Location Type}'
                ),
                'location_type' => array(
                    'text' => '{LNG_Location Type}'
                ),
                'zone' => array(
                    'text' => '{LNG_Zone}'
                ),
                'area' => array(
                    'text' => '{LNG_Area}'
                ),
                'bin' => array(
                    'text' => '{LNG_Bin}'
                ),
                'description' => array(
                    'text' => '{LNG_Description}'
                ),
                'warehouse' => array(
                    'text' => '{LNG_Warehouse Zone}'
                ),
            ),
            'buttons' => array(
                'statusd' => array(
                    'class' => 'icon-list button orange',
                    'id' => ':id',
                    'title' => '{LNG_Description}'
                ))
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