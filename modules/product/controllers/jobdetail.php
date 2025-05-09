<?php
/**
 * @filesource modules/repair/controllers/detail.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace product\jobdetail;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=repair-detail
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * รายละเอียดการซ่อม
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // อ่านข้อมูลรายการที่ต้องการ
        $index = \product\jobdetail\Model::getJob_h($request->request('id')->toInt());

        // ข้อความ title bar
        $this->title = Language::get('Job Order');
        // สมาชิก
        $login = Login::isMember();
        // ผู้ส่งซ่อม หรือ สามารถรับเครื่องซ่อมได้
        if ($index) {
            // ข้อความ title bar
            $this->title .= ' '.$index->job_no;
            // แสดงผล
            $section = Html::create('section', array(
                'class' => 'content_bg'
            ));
            // breadcrumbs
            $breadcrumbs = $section->add('div', array(
                'class' => 'breadcrumbs'
            ));
            $ul = $breadcrumbs->add('ul');
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><span class="icon-tools">{LNG_Production}</span></li>');
            $ul->appendChild('<li><a href="{BACKURL?module=product-joborder&id=0}">{LNG_Create Job Order}</a></li>');
            $ul->appendChild('<li><span>'.$index->job_no.'</span></li>');
            // $section->add('header', array(
            //     'innerHTML' => '<h2 class="icon-file">'.$this->title.'</h2>'
            // ));
            // แสดงฟอร์ม
            $section->appendChild(\product\jobdetail\View::create()->render($request,$index, $login,$request->request('id')->toInt()));
            // คืนค่า HTML
            return $section->render();
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }
}
