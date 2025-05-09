<?php

namespace wms\truck;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this-> title = Language::trans('{LNG_Truck Confirm}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Truck Confirm}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));
 
        $so = $request->request('so')->toString();
        $status = $request->request('status')->toInt();
        $truck = $request->request('truck')->toString();

        $section->appendChild(\wms\truck\View::create()->render($so,$status,$truck));
 
        $section->appendChild(\wms\truck\View::create()->show_data($request,$so));

        return $section->render();

    }
}