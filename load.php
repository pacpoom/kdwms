<?php
/**
 * load.php
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */
/*
 * Site root
 */
define('ROOT_PATH', str_replace('\\', '/', dirname(__FILE__)).'/');
/*
 * โฟลเดอร์เก็บข้อมูล
 */
define('DATA_FOLDER', 'datas/');
/*
 * 0 (default) บันทึกเฉพาะข้อผิดพลาดร้ายแรงลง error_log.php
 * 1 บันทึกข้อผิดพลาดและคำเตือนลง error_log.php
 * 2 แสดงผลข้อผิดพลาดและคำเตือนออกทางหน้าจอ (ใช้เฉพาะตอนออกแบบเท่านั้น)
 */
define('DEBUG', 2);
/*
 * false (default)
 * true บันทึกการ query ฐานข้อมูลลง log (ใช้เฉพาะตอนออกแบบเท่านั้น)
 */
define('DB_LOG', false);
/*
 * ภาษาเริ่มต้น
 * auto = อัตโนมัติจากบราวเซอร์
 * th, en ตามภาษาที่เลือก
 */
define('INIT_LANGUAGE', 'th');
/**
 * เปิด/ปิดการใช้งาน Session บน Database
 * ต้องติดตั้งตาราง sessions ด้วย
 */
define('USE_SESSION_DATABASE', false);
/*
 * ระบุ SQL Mode ที่ต้องการ
 * หากพบปัญหาการใช้งาน
 */
//define('SQL_MODE', '');
/**
 * load Kotchasan
 */
include 'Kotchasan/load.php';
