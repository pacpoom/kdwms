<?php

namespace wms\pickcase;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this->title = Language::trans('{LNG_Picking By Case} {LNG_Transfer Out}');
        $this->menu = 'wms';

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Picking By Case}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));
 
        //var_dump($request->request('id')->toString());

        $so = $request->request('so')->toString();
        $status = $request->request('status')->toInt();
        $total_quantity = $request->request('total_quantity')->toInt();
        $actual_quantity = $request->request('actual_quantity')->toInt();
        $pallet = $request->request('pallet')->toString();
        $pallets = $request->request('pallets')->toString();

        $section->appendChild(\wms\pickcase\View::create()->render($so,$status,$total_quantity,$actual_quantity,$pallet,$pallets));
 
        $section->appendChild(\wms\pickcase\View::create()->show_data($request,$so));

        return $section->render();

    }
}