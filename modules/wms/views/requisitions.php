<?php

namespace wms\requisitions;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

class View extends \Gcms\View{
    private $category;
    public function render (Request $request){
        
        $params = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
        );

        $export['from']=$request->request('from')->date();
        $export['to']=$request->request('to')->date();

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\requisitions\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'sort' => $request->cookie('sort', 'id desc')->toString(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id'),
            'hideCheckbox' => true,
            'action' => 'index.php/wms/model/requisitions/action',
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
            ),
            'headers' => array(
                'img1_name' => array(
                    'text' => '{LNG_Picture}'
                ),
                'tag_no' => array(
                    'text' => '{LNG_Tag No.}'
                ),
                'reason' => array(
                    'text' => '{LNG_Reason}'
                ),
                'req_by' => array(
                    'text' => '{LNG_Request By}'
                ),
                'serial_number' => array(
                    'text' => '{LNG_Box ID}'
                ),
                'material_number' => array(
                    'text' => '{LNG_Material Number}'
                ),
                'quantity' => array(
                    'text' => '{LNG_Quantity}'
                ),
                'issue_qty' => array(
                    'text' => '{LNG_Issue Quantity}'
                ),
                'created_at' => array(
                    'text' => '{LNG_Transection Date}'
                ),
                'username' => array(
                    'text' => '{LNG_Username}'
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
        $thumb = is_file(ROOT_PATH.DATA_FOLDER.'requisition/'.$item['img1_name']) ? WEB_URL.DATA_FOLDER.'requisition/'.$item['img1_name'] : WEB_URL.'skin/img/noicon.png';
        $item['img1_name'] = '<a href="'. $thumb .'" target=_blank><img src="'.$thumb.'" style="max-height:100px;max-width:100px" alt=thumbnail></a>';
        
        if ($item['reason'] == 0) {
            $item['reason'] = 'Scrap';
        } elseif ($item['reason'] == 1) {
            $item['reason'] = 'Replacement';
        } elseif ($item['reason'] == 2) {
            $item['reason'] = 'Rework';
        }

        return $item;
    }
}