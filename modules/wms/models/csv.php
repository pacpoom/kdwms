<?php
/**
 * @filesource modules/school/models/csv.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\csv;

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

    public static function shipstock(){

        $header = array();
        $header['no'] = 'No';
        $header['sale_order'] = Language::trans('{LNG_Sale Order}');
        $header['customer_code'] = Language::trans('{LNG_Customer Code}');
        $header['customer_name'] = Language::trans('{LNG_Customer Name}');
        $header['serial_number'] = Language::trans('{LNG_serial Number}');
        $header['material_number'] = Language::trans('{LNG_Material Number}');
        $header['quantity'] = Language::trans('{LNG_Quantity}');
        $header['ship_date'] = Language::trans('{LNG_Shipping Date}');
        $header['pallet_no'] = Language::trans('{LNG_Pallet No}');
        return $header;
    }

    public static function stock(){

        $header = array();
        $header['no'] = 'No';
        $header['container'] = Language::trans('{LNG_Container Number}');
        $header['case'] = Language::trans('{LNG_Case Number}');
        $header['serial_number'] = Language::trans('{LNG_Box ID}');
        $header['material_number'] = Language::trans('{LNG_Material Number}');
        $header['material_name_en'] = Language::trans('{LNG_Material Name Eng}');
        $header['material_type'] = Language::trans('{LNG_Material Type}');
        $header['actual_quantity'] = Language::trans('{LNG_Quantity}');
        $header['unit'] = Language::trans('{LNG_Unit}');
        $header['inbound_date'] = Language::trans('{LNG_Received Date}');
        $header['location_code'] = Language::trans('{LNG_Location}');

        return $header;
    }

    public static function saledetail(){
        $header = array();
        $header['no'] = 'No';
        $header['sale_order'] = Language::trans('{LNG_Sale Order Number}');
        $header['delivery_date'] = Language::trans('{LNG_Delivery Date}');
        $header['customer_code'] = Language::trans('{LNG_Customer Code}');
        $header['customer_name'] = Language::trans('{LNG_Customer Name}}');
        $header['material_number'] = Language::trans('{LNG_Material Number}');
        $header['planed_quantity'] = Language::trans('{LNG_Order Qty}');
        $header['ship_qty'] = Language::trans('{LNG_Ship Quantity}');
        $header['diff_qty'] = Language::trans('{LNG_Difference Quantity}');
        return $header;
    }

    public static function packing(){

        $header = array();
        $header['no'] = 'No';
        $header['container'] = Language::trans('{LNG_Container Number}');
        $header['case'] = Language::trans('{LNG_Case Number}');
        $header['serial_number'] = Language::trans('{LNG_Box ID}');
        $header['material_number'] = Language::trans('{LNG_Material Number}');
        $header['material_name_en'] = Language::trans('{LNG_Material Name Eng}');
        $header['Quantity'] = Language::trans('{LNG_Quantity}');
        $header['unit'] = Language::trans('{LNG_Unit}');
        $header['status'] = Language::trans('{LNG_status}');

        return $header;
    }

     public static function containers(){

        $header = array();
        $header['no'] = 'No';
        $header['status'] = Language::trans('{LNG_Status}');
        $header['receive_date'] = Language::trans('{LNG_Receive Date}');
        $header['year_lot'] = Language::trans('{LNG_Year Lot}');
        $header['week_lot'] = Language::trans('{LNG_Week Lot}');
        $header['lot_no'] = Language::trans('{LNG_Lot Number}');
        $header['container_size'] = Language::trans('{LNG_Container Size}');
        $header['model'] = Language::trans('{LNG_Model}');
        $header['delivery_date'] = Language::trans('{LNG_Delivery Date}');
        $header['eta_date'] = Language::trans('{LNG_ETA Date}');
        $header['ata_date'] = Language::trans('{LNG_ATA Date}');
        $header['container_type'] = Language::trans('{LNG_Container Type}');
        $header['container'] = Language::trans('{LNG_Container}');
        $header['container_bl'] = Language::trans('{LNG_Container BL}');
        $header['total_material'] = Language::trans('{LNG_Total Material}');
        $header['total_case'] = Language::trans('{LNG_Total Case}');
        $header['total_box'] = Language::trans('{LNG_Total Box}');
        $header['total_quantity'] = Language::trans('{LNG_Total Quantity}');

        return $header;
    }

    public static function packing_list(){

        $header = array(
            Language::trans('{LNG_Container}'),
            Language::trans('{LNG_Case Number}'),
            Language::trans('{LNG_Box Number}'),
            Language::trans('{LNG_Part Number}'),
            Language::trans('{LNG_Part Name}'),
            Language::trans('Quantity'),
            Language::trans('Unit')
        );
        return $header;
    }

    
    public static function container(){

        $header = array(
            Language::trans('{LNG_Container}'),
            Language::trans('{LNG_Year Lot}'),
            Language::trans('{LNG_Week Lot}'),
            Language::trans('{LNG_Lot Number}'),
            Language::trans('{LNG_Container Size}'),
            Language::trans('{LNG_Model}'),
            Language::trans('{LNG_Container Type}'),
            Language::trans('{LNG_Container BL}')
        );
        return $header;
    }

    public static function detail(){

        $header = array();
        $header['no'] = 'No';
        $header['sale_order'] = Language::trans('{LNG_Sale Order Number}');
        $header['material_number'] = Language::trans('{LNG_Material Number}');
        $header['serial_number'] = Language::trans('{LNG_Box ID}');
        $header['original_location'] = Language::trans('{LNG_Original Location}');
        $header['quantity'] = Language::trans('{LNG_Quantity}');
        $header['location_code'] = Language::trans('{LNG_Location}');
        $header['pick'] = Language::trans('{LNG_Pick Box}');
        $header['ship_date'] = Language::trans('{LNG_Ship Date}');
        $header['truck_confirm_date'] = Language::trans('{LNG_Confirm Date}');
        $header['pallet_no'] = Language::trans('{LNG_Pallet No}');
        $header['truck_id'] = Language::trans('{LNG_Truck ID}');
        $header['confirm_flg'] = Language::trans('{LNG_Print Truck}');
        $header['confirm_date'] = Language::trans('{LNG_Print Date}');
        $header['file_name'] = Language::trans('{LNG_File Name}');
       
        
        return $header;
    }

    public static function transaction(){

        $header = array();
        $header['no'] = 'No';
        $header['transaction_date'] = Language::trans('{LNG_Transection Date}');
        $header['transaction_type'] = Language::trans('{LNG_Transection Type}');
        $header['container'] = Language::trans('{LNG_Container Number}');
        $header['case'] = Language::trans('{LNG_Case Number}');
        $header['serial_number'] = Language::trans('{LNG_Box ID}');
        $header['material_number'] = Language::trans('{LNG_Material Number}');
        $header['material_name_en'] = Language::trans('{LNG_Material Name Eng}');
        $header['quantity'] = Language::trans('{LNG_Quantity}');
        $header['unit'] = Language::trans('{LNG_Unit}');
        $header['location_code'] = Language::trans('{LNG_Location}');

        $header['sale_order'] = Language::trans('{LNG_Sale Order}');
        $header['pallet'] = Language::trans('{LNG_Pallet No}');
        $header['truck_id'] = Language::trans('{LNG_Truck ID}');
        $header['truck_date'] = Language::trans('{LNG_Truck Date}');

        $header['username'] = Language::trans('{LNG_Username}');

        return $header;
    }

    public static function requisition(){

        $header = array();
        $header['no'] = 'No';
        $header['tag_no'] = Language::trans('{LNG_Tag No.}');
        $header['reason'] = Language::trans('{LNG_Reason}');
        $header['req_by'] = Language::trans('{LNG_Request By}');
        $header['serial_number'] = Language::trans('{LNG_Box ID}');
        $header['material_number'] = Language::trans('{LNG_Material Number}');
        $header['quantity'] = Language::trans('{LNG_Quantity}');
        $header['issue_qty'] = Language::trans('{LNG_Issue Quantity}');
        $header['created_at'] = Language::trans('{LNG_Transection Date}');
        $header['username'] = Language::trans('{LNG_Username}');

        return $header;
    }

    public static function cystock(){

        $header = array();
        $header['no'] = 'No';
        $header['container'] = Language::trans('{LNG_Container Number}');
        $header['case_number'] = Language::trans('{LNG_Case Number}');
        $header['box_id'] = Language::trans('{LNG_Box ID}');
        $header['temp_material'] = Language::trans('{LNG_Material Number}');
        $header['quantity'] = Language::trans('{LNG_Quantity}');
        $header['delivery_order'] = Language::trans('{LNG_Delivery Type}');
        $header['container_received'] = Language::trans('{LNG_Received Date}');
        $header['storage_location'] = Language::trans('{LNG_Location}');

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
