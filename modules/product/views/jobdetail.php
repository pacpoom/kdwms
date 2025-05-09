<?php
/**
 * @filesource modules/repair/views/detail.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace product\jobdetail;

use Kotchasan\Currency;
use Gcms\Login;
use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Template;

/**
 * module=repair-detail
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * @var mixed
     */
    private $statuses;

    private $category;

    /**
     * แสดงรายละเอียดการซ่อม
     *
     * @param object $index
     * @param array $login
     *
     * @return string
     */
    public function render($request,$index, $login,$id)
    {
     
        // ตาราง
        $params = array();
        // URL สำหรับส่งให้ตาราง
        $uri = self::$request->createUriWithGlobals(WEB_URL.'index.php');
   
        $table = new DataTable(array(
            'uri' => $uri,
            'model' => \product\jobdetail\Model::getJob_d($id),
            'perPage' => $request->cookie('perPage',10)->toInt(),
            'onRow' =>array($this,'onRow'),
            'hideColumns' => array('id'),
            'searchColumns' => array('id','material_number'),
            'action' => 'index.php/product/model/joborder/action',
            'actionCallback' =>'dataTableActionCallback',
            'headers' => array(
                'material_number' => array(
                    'text' => '{LNG_Finished Goods}'
                ),
                'material_name_en' => array(
                    'text' => '{LNG_Finished Goods Name}'
                ),
                'quantity_req' => array(
                    'text' => '{LNG_Quantity Required}'
                ),
                'quantity_stock' => array(
                    'text' => '{LNG_Quantity Stock}'
                ),
                'quantity_pick' => array(
                    'text' => '{LNG_Picking}'
                )
            ),
        ));
        setcookie('perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        // template
        $template = Template::createFromFile(ROOT_PATH.'modules/product/template/detail.html');
        $template->add(array(
            '/%JOB_NO%/' => $index->job_no,
            '/%FG%/' => $index->material_number,
            '/%NAME%/' => $index->material_name_en,
            '/%PLAN%/' => $index->plan,
            '/%PO%/' => $index->purchase_order,
            '/%DELIEVRY%/' => $index->delivery_date,
            '/%DETAILS%/' => $table->render()
        ));
        // คืนค่า HTML
        return $template->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว
     *
     * @param array  $item ข้อมูลแถว
     * @param int    $o    ID ของข้อมูล
     * @param object $prop กำหนด properties ของ TR
     *
     * @return array คืนค่า $item กลับไป
     */
    public function onRow($item, $o, $prop)
    {
              return $item;
    }
}
