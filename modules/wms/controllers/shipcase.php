<?php

namespace wms\shipcase;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this-> title = Language::trans('{LNG_Confirm Picking By Case}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Confirm Picking By Case}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));
 
        $pallet = $request->post('pallet')->toString();
        $status = $request->post('status')->toInt();

        $section->appendChild(\wms\shipcase\View::create()->render($pallet,$status));

        $section->appendChild(\wms\shipcase\View::create()->show_data($request,$pallet));
 
        return $section->render();

    }
}