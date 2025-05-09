<?php
/**
 * @filesource modules/school/controllers/export.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace product\export;

use Gcms\Login;
use Kotchasan\Currency;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Number;

/**
 * module=school-export
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{
    /**
     * ส่งออกไฟล์ csv หรือ การพิมพ์
     *
     * @param Request $request
     */
    public function export(Request $request)
    {
        switch ($request->get('type')->toString()) {
            case 'picking':
                $this->picking($request);
                break;
        }
    }

    /**
     * ส่งออกเกรดของนักเรียนที่เลือก
     */
    private function picking(Request $request)
    {

        $header = \product\csv\Model::picking();
        $datas = array();
        $query = \product\export\Model::getPicking($request->get('job_id')->toInt());
        $job_no = $request->get('job_id')->toInt();
        $i = 0;
        if ( COUNT($query) > 0) {
            foreach ($query as $item) {
                $datas[] = array(
                    $item['id'] => ++$i,
                    $item['declaration_no'],
                    $item['serial_number'],
                    $item['material_number'],
                    $item['material_name_en'],
                    $item['actual_quantity'],
                    $item['location_code'],
                    $item['remark'] => ''
                );
            }
            return \product\picking\View::render($header, $datas, $job_no);
        } else {
            return false;
        }
    }

    /**
     * ส่งออกข้อมูลตัวอย่าง grade เป็นไฟล์ CSV (grade.csv)
     */
    private function grade(Request $request)
    {
        // ค่าที่ส่งมา
        $course = $request->get('course')->topic();
        $room = $request->get('room')->toInt();
        $year = $request->get('year')->toInt();
        $term = $request->get('term')->toInt();
        // ไม่ต้องคำนวณเกรด
        $gradeOnly = empty(self::$cfg->school_grade_caculations);
        // header
        $header = \School\Csv\Model::grade();
        $datas = array();
        foreach (\School\Students\Model::lists($course, $room) as $item) {
            if ($gradeOnly) {
                $datas[] = array($course, $item['number'], $item['student_id'], '', $room, $year, $term);
            } else {
                $datas[] = array($course, $item['number'], $item['student_id'], '', '', '', $room, $year, $term);
            }
        }
        if (empty($datas)) {
            if ($gradeOnly) {
                $datas = array(
                    array($course, 1, 1000, 4, $room, $year, $term),
                    array($course, 2, 1001, 'ร.', $room, $year, $term)
                );
            } else {
                $datas = array(
                    array($course, 1, 1000, 50, 50, 4, $room, $year, $term),
                    array($course, 2, 1001, 0, 0, 'ร.', $room, $year, $term)
                );
            }
        }
        // ส่งออกไฟล์ grade.csv
        return \Kotchasan\Csv::send('grade', $header, $datas, self::$cfg->csv_language);
    }

    /**
     * ส่งออกข้อมูลตัวอย่าง นักเรียน เป็นไฟล์ CSV (student.csv)
     */
    private function student(Request $request)
    {
        // header
        $header = \School\Csv\Model::student();
        $birthday = ((int) date('Y') + (int) Language::get('YEAR_OFFSET')).'-01-31';
        $datas = array(
            array(1, '1000', 'นาย สมชาย มาดแมน', '', $birthday, '0123456789', 'f', '', '', ''),
            array(2, '1001', 'นางสาว สมหญิง สวยงาม', '', $birthday, '0123456788', 'm', '', '', '')
        );
        foreach (Language::get('SCHOOL_CATEGORY', array()) as $k => $v) {
            $datas[0][] = $request->get($k)->toInt();
            $datas[1][] = $request->get($k)->toInt();
        }
        // ส่งออกไฟล์ student.csv
        return \Kotchasan\Csv::send('student', $header, $datas, self::$cfg->csv_language);
    }

    /**
     * ส่งออกข้อมูลตัวอย่าง course เป็นไฟล์ CSV (course.csv)
     */
    private function course(Request $request)
    {
        // header
        $header = \School\Csv\Model::course();
        // ข้อมูล
        $teacher_id = $request->get('teacher_id')->toInt();
        $datas = array(
            '',
            '',
            '',
            '',
            $request->get('typ')->toInt(),
            $request->get('class')->toInt(),
            $request->get('year')->toInt(),
            $request->get('term')->toInt(),
            $teacher_id == 0 ? '' : $teacher_id
        );
        // ส่งออกไฟล์ course.csv
        return \Kotchasan\Csv::send('course', $header, array($datas), self::$cfg->csv_language);
    }
}
