<?php
/**
 * @filesource modules/school/views/export.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace product\picking;

use Kotchasan\Language;
use Kotchasan\Template;

/**
 * แสดงหน้าสำหรับพิมพ์
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * พิมพ์เกรด
     *
     * @param object $student
     * @param array  $header
     * @param array  $datas
     * @param float  $credit
     * @param float  $grade
     */
    public static function render($header, $datas, $job_no)
    {
        $thead = '';
        foreach ($header as $item) {
            $thead .= '<th class="tg-jnby">'.$item.'</th>';
        }

        $content = '';
        foreach ($datas as $items) {
            $content .= '<tr>';
            foreach ($items as $k => $item) {
                $class = $k == 1 ? '' : ' class=center';
                $content .= '<td'.$class.'>'.$item.'</td>';
            }
            $content .= '</tr>';
        }

        $order = \product\export\Model::getJob($job_no);
        if ($order == true) {
            $job_no = $order->job_no;
            $model_no = $order->material_number;
            $model_name = $order->material_name_en;
            $plan = $order->plan;
            $production_date = $order->production_date;
            $PO = $order->purchase_order;
            $Delivery = $order->delivery_date;
        } else {
            $model_no = 'N/A';
            $model_name = 'N/A';
            $plan = 'N/A';
            $production_date = 'N/A';
        }
        // template
        $template = Template::createFromFile(ROOT_PATH.'modules/product/template/picking.html');
        $template->add(array(
            '/%JOB_NO%/' => $job_no,
            '/%DATE%/' => $production_date,
            '/%PO%/' => $PO,
            '/%DELIVERY%/' => $Delivery,
            '/%ORDER%/' => $plan,
            '/%MODEL%/' => $model_no,
            '/%NAME%/' => $model_name,
            '/%THEAD%/' => $thead,
            '/%TBODY%/' => $content,
            '/{LANGUAGE}/' => Language::name(),
            '/{WEBURL}/' => WEB_URL
        ));
        echo Language::trans($template->render());
        return true;
    }
}
