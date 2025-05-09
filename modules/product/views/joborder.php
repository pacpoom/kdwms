<?php

namespace product\joborder;

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
            'model' => \product\joborder\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','material_number'),
            'action' => 'index.php/product/model/joborder/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(array(
                'id' => 'action',
                'class' => 'ok',
                'text' => '{LNG_With selected}',
                'options' => array(
                    'production' => '{LNG_Production}',
                    'finished' => '{LNG_Finished}',
                    'cancel' => '{LNG_Cancel}',
                )
            ),
                array(
                    'id' => 'add',
                    'class' => 'button green icon-new',
                    'text' => '{LNG_Create Job Order}',
                )
            ),
            'headers' => array(
                'status' => array(
                    'text' => '{LNG_Status}',
                    'sort' => 'id'
                ),
                'purchase_order' => array(
                    'text' => '{LNG_Purchase Order}'
                ),
                'delivery_date' => array(
                    'text' => '{LNG_Delievry Date}'
                ),
                'job_no' => array(
                    'text' => '{LNG_Job No}'
                ),
                'material_number' => array(
                    'text' => '{LNG_Finished Goods}'
                ),
                'material_name_en' => array(
                    'text' => '{LNG_Finished Goods Name}'
                ),
                'material_type' => array(
                    'text' => '{LNG_Finished Goods Type}'
                ),
                'production_date' => array(
                    'text' => '{LNG_Production Date}'
                ),
                'plan' => array(
                    'text' => '{LNG_Plan Quantity}'
                ),
                'unit' => array(
                    'text' => '{LNG_Unit}'
                ),
                'total_production' => array(
                    'text' => '{LNG_Total Production}'
                ),
                'total_ng' => array(
                    'text' => '{LNG_NG}'
                ),
                'finished_date' => array(
                    'text' => '{LNG_Finished Date}'
                ),
                'created_at' => array(
                    'text' => '{LNG_Create Date}'
                ),
                'username' => array(
                    'text' => '{LNG_Create Name}'
                )
            ),
            'buttons' => array(
                'description' => array(
                    'class' => 'icon-world button blue',
                    'href' => $uri->createBackUri(array('module' => 'product-jobdetail', 'id' => ':id')),
                    'title' => '{LNG_Sale Order Detail}'
                ),
                'print' => array(
                    'class' => 'button print icon-print',
                    'href' => WEB_URL.'export.php?module=product-export&amp;type=picking&amp;job_id=:id&amp;',
                    'target' => 'download',
                    'text' => '{LNG_Print}'
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
        // $thumb = is_file(ROOT_PATH.DATA_FOLDER.'material/'.$item['model_no'].'.jpg') ? WEB_URL.DATA_FOLDER.'material/'.$item['model_no'].'.jpg' : WEB_URL.'skin/img/noicon.png';
        // $item['model_no'] = '<img src="'.$thumb.'" style="max-height:50px;max-width:50px" alt=thumbnail>';
        // $item['usage'] = currency::format($item['usage']);
        $item['status'] = $this->category->get('job_status', $item['status']);
        $item['material_type'] = $this->category->get('material_type', $item['material_type']);
        return $item;
    }
}