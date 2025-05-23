<?php
/**
 * @filesource Gcms/Config.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Gcms;

/**
 * Config Class สำหรับ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Config extends \Kotchasan\Config
{
    /**
     * กำหนดอายุของแคช (วินาที)
     * 0 หมายถึงไม่มีการใช้งานแคช
     *
     * @var int
     */
    public $cache_expire = 5;
    /**
     * สีของสมาชิกตามสถานะ
     *
     * @var array
     */
    public $color_status = array(
        0 => '#259B24',
        1 => '#FF0000',
        2 => '#FF6600',
        3 => '#3366FF',
        4 => '#902AFF',
        5 => '#660000',
        6 => '#336600'
    );
    /**
     * ถ้ากำหนดเป็น true บัญชี Facebook จะเป็นบัญชีตัวอย่าง
     * ได้รับสถานะแอดมิน (สมาชิกใหม่) แต่อ่านได้อย่างเดียว
     *
     * @var bool
     */
    public $demo_mode = false;
    /**
     * App ID สำหรับการเข้าระบบด้วย Facebook https://gcms.in.th/howto/การขอ_app_id_จาก_facebook.html
     *
     * @var string
     */
    public $facebook_appId = '';
    /**
     * Client ID สำหรับการเข้าระบบโดย Google
     *
     * @var string
     */
    public $google_client_id = '';
    /**
     * รายชื่อฟิลด์จากตารางสมาชิก สำหรับตรวจสอบการ login
     *
     * @var array
     */
    public $login_fields = array('username');
    /**
     * สถานะสมาชิก
     * 0 สมาชิกทั่วไป
     * 1 ผู้ดูแลระบบ
     *
     * @var array
     */
    public $member_status = array(
        0 => 'สมาชิก',
        1 => 'ผู้ดูแลระบบ'
    );
    /**
     * คีย์สำหรับการเข้ารหัส ควรแก้ไขให้เป็นรหัสของตัวเอง
     * ตัวเลขหรือภาษาอังกฤษเท่านั้น ไม่น้อยกว่า 10 ตัว
     *
     * @var string
     */
    public $password_key = '1234567890';
    /**
     * ไดเร็คทอรี่ template ที่ใช้งานอยู่ ตั้งแต่ DOCUMENT_ROOT
     * ไม่ต้องมี / ทั้งเริ่มต้นและปิดท้าย
     * เช่น skin/default
     *
     * @var string
     */
    public $skin = 'skin/default';
    /**
     * ไอคอนเริ่มต้นของไซต์ (โลโก)
     *
     * @var string
     */
    public $default_icon = 'icon-office';
    /**
     * สีส่วนหัว
     *
     * @var string
     */
    public $bg_color = '#3498DB';
    /**
     * สีหลักของเว็บไซต์
     *
     * @var string
     */
    public $warpper_bg_color = '#F9F9F9';
    /**
     * สีตัวอักษรของเมนูบนสุด
     *
     * @var string
     */
    public $color = '#FFFFFF';
    /**
     * สีตัวอักษรของ ส่วนหัว ก่อนเข้าระบบ
     *
     * @var string
     */
    public $login_header_color = '#FFFFFF';
    /**
     * สีตัวอักษรของ footer ก่อนเข้าระบบ
     *
     * @var string
     */
    public $login_footer_color = '#FFFFFF';
    /**
     * ขึ้นบรรทัดใหม่ชื่อเว็บ
     *
     * @var bool
     */
    public $new_line_title = false;
    /**
     * สามารถขอรหัสผ่านในหน้าเข้าระบบได้
     *
     * @var bool
     */
    public $user_forgot = true;
    /**
     * บุคคลทั่วไป สามารถสมัครสมาชิกได้
     *
     * @var bool
     */
    public $user_register = true;
    /**
     * ตั้งค่าการเข้าระบบของสมาชิกใหม่
     * 1 สมัครสมาชิกแล้วเข้าระบบได้ทันที (ค่าเริ่มต้น)
     * 0 สมัครสมาชิกแล้วยังไม่สามารถเข้าระบบได้ ต้องรอแอดมินอนุมัติ
     *
     * @var int
     */
    public $new_members_active = 1;
    /**
     * ส่งอีเมลต้อนรับ เมื่อบุคคลทั่วไปสมัครสมาชิก
     *
     * @var bool
     */
    public $welcome_email = true;
    /**
     * การเข้าระบบต่อ 1 user
     * ค่าเริ่มต้น true (แนะนำ) สามารถเข้าระบบได้เพียงคนเดียวต่อ 1 user คนที่อยู่ในระบบก่อนหน้าจะถูกบังคับให้ออกจากระบบ
     *
     * @var bool
     */
    public $member_only = true;
    /**
     * ข้อความแสดงในหน้า login
     *
     * @var string
     */
    public $login_message = '';
    /**
     * ชื่อคลาสของข้อความแสดงในหน้า login warning,tip,message
     *
     * @var string
     */
    public $login_message_style = 'hidden';
    /**
     * ช่วงเวลาจำการเข้าระบบ
     * 86400 = 1 วัน
     *
     * @var int
     */
    public $remember_expired = 2592000;
    /**
     * Channel ID
     * จาก Line Login
     *
     * @var string
     */
    public $line_channel_id = '';
    /**
     * Channel secret
     * จาก Line Login
     *
     * @var string
     */
    public $line_channel_secret = '';
    /**
     * Bot basic ID
     * จาก Messaging API
     *
     * @var string
     */
    public $line_official_account = '';
    /**
     * Channel access token (long-lived)
     * จาก Messaging API
     *
     * @var string
     */
    public $line_channel_access_token = '';
    /**
     * รายการหมวดหมู่ของสมาชิก ที่ต้องระบุ
     *
     * @var array
     */
    public $categories_required = array();
    /**
     * รายการหมวดหมู่ที่สมาชิกไม่สามารถแก้ไขได้
     *
     * @var array
     */
    public $categories_disabled = array();
    /**
     * ชนิดของไฟล์รูปภาพของสมาชิกที่รองรับ
     *
     * @var array
     */
    public $member_img_typies = array('jpg', 'jpeg', 'png');
    /**
     * ขนาดรูปภาพสมาชิกที่จัดเก็บ (พิกเซล)
     *
     * @var int
     */
    public $member_img_size = 250;
    /**
     * VAT
     *
     * @var int
     */
    public $vat = 7;

    /**
     * สถานะซื้อ
     *
     * @var array
     */
    public $buy_status = array('PO', 'RET', 'IN');

    /**
     * รายการรับเข้า Stock
     *
     * @var array
     */
    public $in_stock_status = array('IN', 'RET');
    /**
     * สถานะขาย
     *
     * @var array
     */
    public $sell_status = array('OUT', 'QUO');

    /**
     * รายการตัด Stock
     *
     * @var array
     */
    public $out_stock_status = array('OUT');

    /**
     * @var string
     */
    public $authorized = '';
    /**
     * @var string
     */
    public $email = '';
    /**
     * รหัสสินค้า
     *
     * @var string
     */
    public $product_no = 'P%04d';
    /**
     * รหัสลูกค้า
     *
     * @var string
     */
    public $customer_no = 'CU%04d';
    /**
     * @var string
     */
    public $currency_unit = 'THB';
    /**
     * @var string
     */
    public $bank = '';
    /**
     * @var string
     */
    public $bank_name = '';
    /**
     * @var string
     */
    public $bank_no = '';
    /**
     * @var int
     */
    public $inventory_w = 500;
    /**
     * ชนิดของไฟล์รูปภาพของ Inventory ที่รองรับ
     *
     * @var array
     */
    public $inventory_img_typies = array('jpg', 'jpeg', 'png');
}
