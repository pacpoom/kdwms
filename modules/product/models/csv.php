<?php
/**
 * @filesource modules/school/models/csv.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace product\csv;

use Kotchasan\Language;

/**
 * CSV Header
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * คืนค่า CSV Header ของ student
     *
     * @return array
     */
    public static function student()
    {
        $header = array(
            Language::get('Number'),
            Language::trans('{LNG_Student ID} **'),
            Language::trans('{LNG_Name} *'),
            Language::trans('{LNG_Identification No.} **'),
            Language::get('Birthday'),
            Language::get('Phone'),
            Language::get('Sex'),
            Language::get('Address'),
            Language::trans('{LNG_Name} ({LNG_Parent})'),
            Language::trans('{LNG_Phone} ({LNG_Parent})')
        );
        foreach (Language::get('SCHOOL_CATEGORY', array()) as $key => $label) {
            $header[] = $label;
        }
        return $header;
    }

    public static function importsale(){
        $header = array(
            Language::trans('Vehicle Number')
        );
        return $header;
    }

    public static function import_trip(){
        $header = array(
            Language::trans('trip'),
            Language::trans('vin_number')
        );
        return $header;
    }

    public static function importccl(){
        $header = array(
            Language::trans('Declaration NO'),
            Language::trans('Invoice No'),
            Language::trans('Item No'),
            Language::trans('Part No'),
            Language::trans('Quantity')
        );
        return $header;
    }

    public static function picking(){
        $header = array(
            Language::trans('No.'),
            Language::trans('Declaration NO'),
            Language::trans('Box ID'),
            Language::trans('Material Number'),
            Language::trans('Material Name'),
            Language::trans('Quantity'),
            Language::trans('Location'),
            Language::trans('Remark')
        );

        return $header;
    }

    /**
     * คืนค่า CSV Header ของ grade
     *
     * @return array
     */
    public static function grade()
    {
        if (empty(self::$cfg->school_grade_caculations)) {
            return array(
                Language::get('Course'),
                Language::get('Number'),
                Language::get('Student ID'),
                Language::get('Grade'),
                Language::get('Room'),
                Language::get('Academic year'),
                Language::get('Term')
            );
        } else {
            return array(
                Language::get('Course'),
                Language::get('Number'),
                Language::get('Student ID'),
                Language::get('Midterm'),
                Language::get('Final'),
                Language::get('Grade'),
                Language::get('Room'),
                Language::get('Academic year'),
                Language::get('Term')
            );
        }
    }
    /**
     * คืนค่า CSV Header ของ course
     *
     * @return array
     */
    public static function course()
    {
        return array(
            Language::trans('{LNG_Course Code} **'),
            Language::trans('{LNG_Course Name} *'),
            Language::trans('{LNG_Credit} *'),
            Language::get('Period'),
            Language::get('Type'),
            Language::get('Class'),
            Language::get('Academic year'),
            Language::get('Term'),
            Language::get('Teacher')
        );
    }
}
