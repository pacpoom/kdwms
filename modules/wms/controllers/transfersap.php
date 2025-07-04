<?php

namespace wms\transfersap;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    public function render(request $request){

        $this-> title = Language::trans('{LNG_List of} {LNG_Transfer SAP Report}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Transfer SAP Report}</span></li>');
        $ul->appendChild('<li class="active"><span>'.$this->title.'</span></li>');
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));
        
        $section->appendChild(\wms\transfersap\View::create()->import($request));

        $section->appendChild(\wms\transfersap\View::create()->render($request));
 
        return $section->render();

    }
}