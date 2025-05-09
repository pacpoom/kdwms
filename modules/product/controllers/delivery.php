<?php

namespace product\delivery;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

class Controller extends \Gcms\Controller{
    
    public function render(request $request){

        $this-> title = Language::trans('{LNG_Transfer To Production}');

        $section = Html::create('section',array(
         'class' => 'content_bg'
        ));
 
        $breadcrumbs = $section->add('div',array(
         'class' => 'breadcrumbs'
        ));
 
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-fire" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Transfer To Production}</span></li>');
 
        $section->add('header',array(
         'innerHTML'=>'<h4 class="icon-config">'.$this->title.'</h4>'
        ));

        $job_id = $request->request('job_id')->toString();
        $index = \product\transferpd\Model::getJob_d($job_id);
        $status = $request->request('status')->toInt();
        $job = \product\transferpd\Model::getJOB_ID(isset($index->job_no) ? $index->job_no : '');
        //var_dump($index);

        $section->appendChild(\product\delivery\View::create()->render($request,$index,$status,isset($job[0]->id)?$job[0]->id:''));

        $section->appendChild(\product\delivery\View::create()->show_data($request,isset($job[0]->id)?$job[0]->id:''));

 
        return $section->render();

    }
}