<?php

namespace index\role;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this-> title = Language::trans('{LNG_List of} {LNG_Role master}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Role master}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));
 
        $section->appendChild(\index\role\View::create()->render($request));
 
        return $section->render();

    }
}