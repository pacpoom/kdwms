<?php
/**
 * @filesource modules/inventory/models/category.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\locationdata;

use Kotchasan\Language;

/**
 * คลาสสำหรับอ่านข้อมูลหมวดหมู่
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Gcms\location
{
    /**
     * init Class
     */
    public function __construct()
    {
        // ชื่อหมวดหมู่
        $this->categories = Language::get('INVENTORY_LOCATION', array());
    }
}
