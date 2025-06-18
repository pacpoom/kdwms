<?php

namespace wms\container;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\UploadedFile;
use Kotchasan\Language;


class View extends \Gcms\View{
    private $category;

    public function import(Request $request)
    {
        // form
        $form = Html::create('form', array(
            'id' => 'setup_frm_',
            'class' => 'setup_frm_',
            'autocomplete' => 'off',
            'action' => 'index.php/wms/model/container/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));

        $fieldset = $form->add('fieldset');

        // import
        $fieldset->add('file',array(
            'id' => 'import',
            'labelClass' => 'g-input icon-excel',
            'itemClass' => 'item',
            'label' => '{LNG_Browse file}',
            'placeholder' => 'Import CCL List {ENCODE}',
            'comment' => '{LNG_File size is less than :size}',
            'accept' => array('csv')
        ));

        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save icon-import',
            'value' => '{LNG_Import}'
        ));

        $fieldset->add('a',array(
            'id' => 'export',
            'class' => 'button icon-book',
            'href' => WEB_URL.'export.php?module=wms-export&amp;type=container&amp;',
            'target' => '_blank'
        ));

        // type
        $fieldset->add('hidden', array(
            'id' => 'type',
            'value' => 'grade'
        ));
        \Gcms\Controller::$view->setContentsAfter(array(
            '/:size/' => UploadedFile::getUploadSize(),
            '/{ENCODE}/' => Language::find('CSV_ENCODING', '', self::$cfg->csv_language)
        ));
        // คืนค่า HTML Form
        return $form->render();
    }

    public function render (Request $request){

        $params = array();
        $export = array();

        $params = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
            'container' => $request->request('container')->toString(),
            'status' => $request->request('status')->toInt(),
        );

        $export = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
            'container' => $request->request('container')->toString(),
            'status' => $request->request('status')->toInt(),
        );

        $status = array(
            0 => 'Waiting Receive',
            1 => 'Received',
            2 => 'All',
        );

        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        $this->category = \index\category\Model::init(false);
        
        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \wms\container\Model::toDataTable($params),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'onRow' =>array($this,'onRow'),
            'hideCheckbox' => true,
            'hideColumns' => array('id'),
            'searchColumns' => array('id','container'),
            'action' => 'index.php/wms/model/container/action',
            'actionCallback' =>'dataTableActionCallback',
            'actions' => array(
                array(
                    'id' => 'export',
                    'class' => 'button green icon-new',
                    'text' => '{LNG_Download}',
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
                        'name' => 'status',
                        'text' => '{LNG_Status}',
                        'options' => $status,
                        'value' => $params['status']
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'container',
                        'value' => $params['container'],
                        'placeholder' => '{LNG_Container}',
                    ),
            ),
            'headers' => array(
                'status' => array(
                    'text' => '{LNG_Status}'
                ),
                'receive_date' => array(
                    'text' => '{LNG_Receive Date}'
                ),
                'year_lot' => array(
                    'text' => '{LNG_Year Lot}'
                ),
                'week_lot' => array(
                    'text' => '{LNG_Week Lot}'
                ),
                'lot_no' => array(
                    'text' => '{LNG_Lot Number}'
                ),
                'container_size' => array(
                    'text' => '{LNG_Container Size}'
                ),
                'model' => array(
                    'text' => '{LNG_Model}'
                ),
                'delivery_date' => array(
                    'text' => '{LNG_Delivery Date}'
                ),
                'eta_date' => array(
                    'text' => '{LNG_ETA Date}'
                ),
                'ata_date' => array(
                    'text' => '{LNG_ATA Date}'
                ),
                'container_type' => array(
                    'text' => '{LNG_Container Type}'
                ),
                'container' => array(
                    'text' => '{LNG_Container}'
                ),
                'container_bl' => array(
                    'text' => '{LNG_Container BL}'
                ),
                'total_material' => array(
                    'text' => '{LNG_Total Material}'
                ),
                'total_case' => array(
                    'text' => '{LNG_Total Case}'
                ),
                'total_box' => array(
                    'text' => '{LNG_Total Box}'
                ),
                'total_quantity' => array(
                    'text' => '{LNG_Total Quantity}'
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
                //setcookie('sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
                // คืนค่า HTML
                return $table->render();
    }

    public function onRow($item, $o, $prop)
    {
        if ($item['status'] == 0){
            $item['status'] = "<center><mark class=term2>{LNG_Waiting Receive}</mark></center>";
        } elseif ($item['status'] == 1) {
            $item['status'] = "<center><mark class=term6>Received</mark></center>";
        }

        // $item['zone'] = $this->category->get('zone', 1);
        // $thumb = is_file(ROOT_PATH.DATA_FOLDER.'material/'.$item['id'].'.jpg') ? WEB_URL.DATA_FOLDER.'material/'.$item['id'].'.jpg' : WEB_URL.'skin/img/noicon.png';
        // $item['id'] = '<img src="'.$thumb.'" style="max-height:50px;max-width:50px" alt=thumbnail>';
        // $item['material_type'] = $this->category->get('material_type', $item['material_type']);
        return $item;
    }
}