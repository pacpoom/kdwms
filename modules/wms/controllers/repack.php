<?php

namespace wms\repack;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this-> title = Language::trans('{LNG_Repackage}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Repackage}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));
 
        $material = $request->post('material')->toString();
        $status = $request->post('status')->toInt();
        $quantity = $request->post('quantity')->toInt();
        $box = $request->post('box')->toString();
        $destination = $request->post('destination')->toString();

        $section->appendChild(\wms\repack\View::create()->render($material,$status,$quantity,$box,$destination));

        return $section->render();

    }
}