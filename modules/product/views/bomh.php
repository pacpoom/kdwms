<?php

namespace product\bomh;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

class View extends \Gcms\View{

    private $category;

    public function render (Request $request){

        $params = array();

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \product\bomh\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'onRow' =>array($this,'onRow'),
            'hideCheckbox' => true,
            'searchColumns' => array('material_number'),
            'action' => 'index.php/product/model/bomh/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
                array(
                    'id' => 'add',
                    'class' => 'button green icon-new',
                    'text' => '{LNG_Add}{LNG_Bill of Material}',
                )
            ),
            'headers' => array(
                'model_no' => array(
                    'text' => '{LNG_Image}',
                    'sort' => 'id'
                ),
                'material_number' => array(
                    'text' => '{LNG_Finished Goods}'
                ),
                'material_type' => array(
                    'text' => '{LNG_Finished Goods Type}'
                ),
                'material_name_en' => array(
                    'text' => '{LNG_Finished Goods Name}'
                ),
                'unit' => array(
                    'text' => '{LNG_Unit}'
                )
            ),
            'buttons' => array(
                'statusd' => array(
                    'class' => 'icon-list button orange',
                    'id' => ':material_number',
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
        $thumb = is_file(ROOT_PATH.DATA_FOLDER.'material/'.$item['model_no'].'.jpg') ? WEB_URL.DATA_FOLDER.'material/'.$item['model_no'].'.jpg' : WEB_URL.'skin/img/noicon.png';
        $item['model_no'] = '<img src="'.$thumb.'" style="max-height:50px;max-width:50px" alt=thumbnail>';
        $item['material_type'] = $this->category->get('material_type', $item['material_type']);
        return $item;
    }
}