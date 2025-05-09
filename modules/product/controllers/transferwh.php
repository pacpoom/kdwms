<?php

namespace product\transferwh;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this-> title = Language::trans('{LNG_Transfer To Warehouse(FG)}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Transfer To Warehouse(FG)}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));

        $material_number = '';
        $reference = '';
        
        $po = $request->request('po')->toString();
        $delivery = $request->request('delivery')->date();
        $material_name = $request->request('material_name')->toString();
        $location_code = $request->request('location_code')->toInt();
        $status = $request->request('status')->toInt();
        $job_order = $request->request('job')->toInt();

        $section->appendChild(\product\transferwh\View::create()->render($job_order,$po,$delivery,$material_name,$location_code,$status));

        $section->appendChild(\product\transferwh\View::create()->show_data($request,$job_order));

 
        return $section->render();

    }
}