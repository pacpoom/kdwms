<?php

namespace product\pulling;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this-> title = Language::trans('{LNG_Pulling}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Pulling}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));

        $index = array();

        $rounting_id = $request->request('rounting_id')->toInt();
        $min = $request->request('min')->toInt();
        $max = $request->request('max')->toInt();
        $material = $request->request('material')->toString();
        $material_name = $request->request('material_name')->toString();
        $status = $request->request('status')->toInt();

        //var_dump($index);

        $section->appendChild(\product\pulling\View::create()->render($request,$rounting_id,$min,$max,$material,$material_name,$status));

        //$section->appendChild(\product\transferpd\View::create()->show_data($request,isset($job[0]->id)?$job[0]->id:''));

 
        return $section->render();

    }
}