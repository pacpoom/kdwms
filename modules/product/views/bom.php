<?php

namespace product\bom;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

class View extends \Gcms\View{

    private $category;

    public function render ($request,$model_no){

        $params = array();

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);

        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \product\bom\Model::toDataTable($model_no),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','material_number'),
            'action' => 'index.php/product/model/bom/action',
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
                    'text' => '{LNG_Add}{LNG_Bill of Material}',
                )
            ),
            'headers' => array(
                'model_no' => array(
                    'text' => '{LNG_Image}',
                    'sort' => 'id'
                ),
                'Model' => array(
                    'text' => '{LNG_Finished Goods}'
                ),
                'Model_Type' => array(
                    'text' => '{LNG_Finished Goods Type}'
                ),
                'Model Name' => array(
                    'text' => '{LNG_Finished Goods Name}'
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'material_name_en' => array(
                    'text' => '{LNG_Material Name Eng}'
                ),
                'usage' => array(
                    'text' => '{LNG_Usage}'
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
        $thumb = is_file(ROOT_PATH.DATA_FOLDER.'material/'.$item['model_no'].'.jpg') ? WEB_URL.DATA_FOLDER.'material/'.$item['model_no'].'.jpg' : WEB_URL.'skin/img/noicon.png';
        $item['model_no'] = '<img src="'.$thumb.'" style="max-height:50px;max-width:50px" alt=thumbnail>';
        $item['usage'] = currency::format($item['usage']);
        $item['Model_Type'] = $this->category->get('material_type', $item['Model_Type']);
        $item['material_type'] = $this->category->get('material_type', $item['material_type']);
        return $item;
    }
}