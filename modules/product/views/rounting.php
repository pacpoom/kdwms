<?php

namespace product\rounting;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

class View extends \Gcms\View{

    private $category;

    public function render ($request){

        $params = array();

        $export = array();

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);

        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \product\rounting\Model::toDataTable(),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array(),
            'searchColumns' => array('id','material_number'),
            'action' => 'index.php/product/model/rounting/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(array(
                'id' => 'action',
                'class' => 'ok',
                'text' => '{LNG_With selected}',
                'options' => array(
                    'delete' => '{LNG_Delete}',
                )
            ),
                array(
                    'id' => 'add',
                    'class' => 'button green icon-new',
                    'text' => '{LNG_Add}{LNG_Routing}',
                ),
                array(
                    'class' => 'button orange icon-excel',
                    'id' => 'export&'.http_build_query($export),
                    'text' => '{LNG_Download}'
                ),
            ),
            'headers' => array(
                'id' => array(
                    'text' => '{LNG_Image}',
                    'sort' => 'id'
                ),
                'routing_id' => array(
                    'text' => '{LNG_Routing}',
                    'sort' => 'id'
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'material_name_en' => array(
                    'text' => '{LNG_Material Name Eng}'
                ),
                'min' => array(
                    'text' => '{LNG_Min}'
                ),
                'max' => array(
                    'text' => '{LNG_Max}'
                ),
                'unit' => array(
                    'text' => '{LNG_Unit}'
                ),
                'material_type' => array(
                    'text' => '{LNG_Material Type}'
                )
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
        $thumb = is_file(ROOT_PATH.DATA_FOLDER.'material/'.$item['id'].'.jpg') ? WEB_URL.DATA_FOLDER.'material/'.$item['id'].'.jpg' : WEB_URL.'skin/img/noicon.png';
        $item['id'] = '<img src="'.$thumb.'" style="max-height:50px;max-width:50px" alt=thumbnail>';
        $item['routing_id'] = $this->category->get('routing', $item['routing_id']);
        $item['material_type'] = $this->category->get('material_type', $item['material_type']);
        return $item;
    }
}