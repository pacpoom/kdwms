<?php

namespace wms\querybox;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this-> title = Language::trans('{LNG_Query By Box}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Query By Box}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));
 
        $total_box = $request->request('total_box')->toInt();
        $total_qty = $request->request('total_qty')->toInt();
        $raw = $request->request('raw')->toString();

        $section->appendChild(\wms\querybox\View::create()->render($request,$raw,$total_box,$total_qty));
        $section->appendChild(\wms\querybox\View::create()->show_data($request,$raw));
        return $section->render();

    }
}