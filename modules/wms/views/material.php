<?php

namespace wms\material;

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
            'model' => \wms\material\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            //'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            //'hideColumns' => array('id'),
            'searchColumns' => array('id','material_number'),
            'action' => 'index.php/wms/model/material/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(array(
                'id' => 'action',
                'class' => 'ok',
                'text' => '{LNG_With selected}',
                'options' => array(
                    'delete' => '{LNG_Delete}',
                    'Active' => '{LNG_Active}',
                    'Non Active' => '{LNG_Non Active}'
                )
            ),
                array(
                    'id' => 'addlocation',
                    'class' => 'button green icon-new',
                    'text' => '{LNG_Add}{LNG_Material Master}',
                )
            ),
            'headers' => array(
                'id' => array(
                    'text' => '{LNG_Image}',
                    'sort' => 'id'
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'material_name_en' => array(
                    'text' => '{LNG_Material Name Eng}'
                ),
                'material_name_thai' => array(
                    'text' => '{LNG_Material Name Thai}'
                ),
                'inspection_flg' => array(
                    'text' => '{LNG_Quality Control}'
                ),
                'unit' => array(
                    'text' => '{LNG_Unit}'
                ),
                'net_weight' => array(
                    'text' => '{LNG_Net Weight}'
                ),
                'material_type' => array(
                    'text' => '{LNG_Material Type}'
                ),
                'currency' => array(
                    'text' => '{LNG_Currency unit}'
                ),
                'unit_price' => array(
                    'text' => '{LNG_Prices}'
                ),
            ),
            'buttons' => array(
                'statusd' => array(
                    'class' => 'icon-edit button orange',
                    'id' => ':id',
                    'title' => '{LNG_Edit}',
                ),
                'print' => array(
                    'class' => 'icon-print button blue',
                    'id' => ':id',
                    'title' => '{LNG_Print}'
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
        // $item['zone'] = $this->category->get('zone', 1);
        $thumb = is_file(ROOT_PATH.DATA_FOLDER.'material/'.$item['id'].'.jpg') ? WEB_URL.DATA_FOLDER.'material/'.$item['id'].'.jpg' : WEB_URL.'skin/img/noicon.png';
        $item['id'] = '<img src="'.$thumb.'" style="max-height:50px;max-width:50px" alt=thumbnail>';
        $item['material_type'] = $this->category->get('material_type', $item['material_type']);

        if ($item['inspection_flg'] == 1) {
            $item['inspection_flg'] = "<center><mark class=term3>QC</mark></center>";;
        } else {
            $item['inspection_flg'] = '';
        }

        return $item;
    }
}