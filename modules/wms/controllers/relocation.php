<?php

namespace wms\relocation;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this-> title = Language::trans('{LNG_Put Away}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Move}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));
 

        $material_number = $request->request('material_number')->toString();
        $material_name = $request->request('material_name')->toString();
        $total_box = $request->request('total_box')->toInt();
        $total_qty = $request->request('total_qty')->toInt();
        $qty = $request->request('qty')->toString();
        $location_code = $request->request('location')->toString();
        $status = $request->request('status')->toInt();

        $section->appendChild(\wms\relocation\View::create()->render($request,$material_number,$material_name,$total_box,$total_qty,$qty,$location_code,$status));
        $section->appendChild(\wms\relocation\View::create()->show_data($request,$location_code));
        return $section->render();

    }
}