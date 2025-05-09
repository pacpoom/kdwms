<?php

namespace wms\case;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this-> title = Language::trans('{LNG_Receive Process(By Case)}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Receive Process(By Case)}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));

        $material_number = '';
        $reference = '';
        
        $total_box = $request->request('total_box')->toInt();
        $total_receive = $request->request('total_receive')->toInt();
        $status = $request->request('status')->toInt();
        $container = $request->request('container')->toString();
        $location_code = $request->request('location')->toString();
        $location_id = $request->request('location_id')->toInt();
        $container_no = \wms\case\Model::getContainerID($container);
        
        if ($container_no == false) {
            $section->appendChild(\wms\case\View::create()->render($total_box,$total_receive,0,$status,$location_code));
            $section->appendChild(\wms\case\View::create()->show_data($request,NULL,NULL));
            

        } else {
            $section->appendChild(\wms\case\View::create()->render($total_box,$total_receive,$container_no[0]->container,$status,$location_code));
            $section->appendChild(\wms\case\View::create()->show_data($request,$container_no[0]->container,$location_code));
          
        }

        return $section->render();

    }
}