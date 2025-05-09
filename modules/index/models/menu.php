<?php
/**
 * @filesource modules/index/models/menu.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Index\Menu;

use Gcms\Login;
use Kotchasan\Language;
use Kotchasan\Http\Request;
use Kotchasan\Database\Sql;
/**
 * รายการเมนู
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model{
    /**
     * รายการเมนู
     *
     * @param array $login
     *
     * @return array
     */


    public static function getRole($id){
        return static::createQuery()
        ->select('T4.topic','T4.text','T3.function_name','T3.url')
        ->from('role T1')
        ->join('submenu T2','LEFT',array('T1.id','T2.role_id'))
        ->join('menu T3','LEFT',array('T2.menu_id','T3.id'))
        ->join('main_menu T4','LEFT',array('T3.function','T4.id'))
        ->where(array('T4.topic',$id))
        ->execute();
    }

    public static function getFunction($id){
        
        return static::createQuery()
        ->select('T4.topic','T4.text')
        ->from('role T1')
        ->join('submenu T2','LEFT',array('T1.id','T2.role_id'))
        ->join('menu T3','LEFT',array('T2.menu_id','T3.id'))
        ->join('main_menu T4','LEFT',array('T3.function_id','T4.id'))
        ->where(array('T1.id',$id))
        ->groupBy('T4.topic','T4.text')
        ->execute();

    }

    public static function roldId($id){

        return static:: createQuery()
        ->select('id','topic','text')
        ->from('main_menu')
        ->where(array('topic',$id))
        ->execute();
    }

    public static function get_main($id){

        return static:: createQuery()
        ->select('id','role_id','menu_id')
        ->from('submenu')
        ->where(array('role_id',$id))
        ->order('menu_id')
        ->execute();
    }
    
    public static function function_a($id){

        $where = array();
        $where[] = array(sql::create('T1.id IN'.$id));

        return static:: createQuery()
        ->select('T1.function_id','T2.topic','T2.text')
        ->from('menu T1')
        ->join('main_menu T2','LEFT',array('T1.function_id','T2.id'))
        ->where($where)
        ->groupBy('T1.function_id','T2.topic','T2.text')
        ->order('T1.function_id')
        ->execute();
    }


    public static function function_detail($id,$menuId){

        $where = array();
        $where[] = array(sql::create('function_id = '. $menuId .' AND T1.id IN'.$id));

        return static:: createQuery()
        ->select('T1.function_name','T1.url')
        ->from('menu T1')
        ->where($where)
        ->execute();
    }

    public static function getMenus($login)
    {
        $menu = array();

        
        if (isset($login['role_id'])) {
            $function_role = \Index\Menu\Model::getFunction($login['role_id']);
            
            $menu['home'] = array(
                'text' => '{LNG_Home}',
                'url' => 'index.php?module=home'
            );

            $get_main = \index\menu\Model::get_main($login['role_id']);

            $function_id = '';

            $submenu = array();

            foreach ($get_main as $row){
                $function_id = $function_id ."'". $row->menu_id."',";
            }

            $function_all = '('. $function_id ."'')";

            $get_topic = \index\menu\Model::function_a($function_all);
            
            foreach($get_topic as $row){

                $i = 0;
                $submenu = array();
                
                $getId = \index\menu\Model::roldId($row->topic);

                $role = \Index\Menu\Model::function_detail($function_all,$getId[0]->id);

                foreach ($role as $Rs){
                    $submenu[$i] = array(
                        'text' => $Rs->function_name,
                        'url' => $Rs->url
                    );
                    $i++;
                }

                $menu[$row->topic] = array(
                    'text' => $row->text,
                    'submenus' =>  $submenu
                );
            }

        $menu['logout'] = array(
                'text' => '{LNG_Logout}',
                'url' => 'index.php?action=logout'
        );
        }

        //$function_role = \Index\Menu\Model::getFunction($login['role_id']);

        $notDemoMode = Login::notDemoMode($login);
        // แอดมิน
        $isAdmin = $notDemoMode && Login::isAdmin();
        // สามารถตั้งค่าได้
        $can_config = Login::checkPermission($login, 'can_config');
        // เมนูตั้งค่า
        $settings = array();
        if ($can_config) {
            // สามารถตั้งค่าระบบได้
            $settings['system'] = array(
                'text' => '{LNG_Site settings}',
                'url' => 'index.php?module=system'
            );
            $settings['mailserver'] = array(
                'text' => '{LNG_Email settings}',
                'url' => 'index.php?module=mailserver'
            );
            $settings['loginpage'] = array(
                'text' => '{LNG_Login page}',
                'url' => 'index.php?module=loginpage'
            );
        }
        if ($isAdmin) {
            $settings['linesettings'] = array(
                'text' => '{LNG_LINE settings}',
                'url' => 'index.php?module=linesettings'
            );
            $settings['apis'] = array(
                'text' => 'API',
                'url' => 'index.php?module=apis'
            );
            $settings['modules'] = array(
                'text' => '{LNG_Module}',
                'url' => 'index.php?module=modules'
            );
        }
        if ($can_config) {
            $settings['language'] = array(
                'text' => '{LNG_Language}',
                'url' => 'index.php?module=language'
            );
            foreach (Language::get('CATEGORIES', array()) as $k => $label) {
                $settings[$k] = array(
                    'text' => $label,
                    'url' => 'index.php?module=categories&amp;type='.$k
                );
            }
            $settings['province'] = array(
                'text' => '{LNG_Province}',
                'url' => 'index.php?module=province'
            );
            $settings['amphur'] = array(
                'text' => '{LNG_Amphur}',
                'url' => 'index.php?module=amphur'
            );
            $settings['district'] = array(
                'text' => '{LNG_District}',
                'url' => 'index.php?module=district'
            );
        }
        if ($isAdmin) {
            foreach (Language::get('PAGES', array()) as $src => $label) {
                $settings['write'.$src] = array(
                    'text' => $label,
                    'url' => 'index.php?module=write&amp;src='.$src,
                    'target' => '_self'
                );
            }
            $settings['consentsettings'] = array(
                'text' => '{LNG_Cookie Policy}',
                'url' => 'index.php?module=consentsettings'
            );
        }
        if ($notDemoMode && Login::checkPermission($login, 'can_view_usage_history')) {
            $settings['usage'] = array(
                'text' => '{LNG_Usage history}',
                'url' => 'index.php?module=usage'
            );
        }


        if ($login) {

            return $menu;
            // return array(
            //     'home' => array(
            //         'text' => '{LNG_Home}',
            //         'url' => 'index.php?module=home'
            //     ),
                // 'system_data' => array(
                //     'text' => '{LNG_System Data}',
                //     'submenus' => array(
                //         array(
                //             'text' =>'{LNG_Vehicle Data}',
                //             'url' =>'index.php?module=gaoff'
                //         ),
                //         array(
                //             'text' => '{LNG_Vehicle Code}',
                //             'url' =>'index.php?module=vehiclecode'
                //         ),
                //         array(
                //             'text' => '{LNG_Vehicle Yard}',
                //             'url' =>'index.php?module=location'
                //         ),
                //         array(
                //             'text' => '{LNG_Allocate Location}',
                //             'url' => 'index.php?module=allocatelocation'
                //         ),
                //         array(
                //             'text' => '{LNG_Vehicle Driver}',
                //             'url' => 'index.php?module=driver'
                //         ),
                //         array(
                //             'text' => '{LNG_Dealer}',
                //             'url' => 'index.php?module=dealer'
                //         ),
                //         array(
                //             'text' => '{LNG_Settings}',
                //             'url' => 'index.php?module=VDC-setting'
                //         )
                //     )
                // ),
            //     'inbound' => array(
            //         'text' => '{LNG_Vehicle In}',
            //         'submenus' => array(
            //             array(
            //                 'text' => '{LNG_Vehicle Receive}',
            //                 'url' =>'index.php?module=pdiin'
            //             ),
            //             array(
            //                 'text' => '{LNG_Check In Driver}',
            //                 'url' =>'index.php?module=checkin'
            //             ),
            //             array(
            //                 'text' => '{LNG_Relocation}',
            //                 'url' =>'index.php?module=relocation'
            //             )
            //         )
            //     ),
            //     'inventory' => array(
            //         'text' => '{LNG_Inventory Management}',
            //         'submenus' => array(
            //             array(
            //                 'text' => '{LNG_Stock}',
            //                 'url' =>'index.php?module=stock'
            //             ),
            //             array(
            //                 'text' => '{LNG_Key Outbound}',
            //                 'url' =>'index.php?module=key'
            //             ),
            //             array(
            //                 'text' => '{LNG_Key Inbound}',
            //                 'url' => 'index.php?module=keyin'
            //             ),
            //             array(
            //                 'text' => '{LNG_Count Stock}',
            //                 'url' => 'index.php?module=count'
            //             ),
            //             array(
            //                 'text' => '{LNG_Transaction Report}',
            //                 'url' => 'index.php?module=VDC-transaction'
            //             )
            //         )
            //     ),
            //     'Outbound' => array(
            //         'text' => '{LNG_Outbound}',
            //         'submenus' => array(
            //             array(
            //                 'text' => '{LNG_Sale Order}',
            //                 'url' =>'index.php?module=saleorder'
            //             ),
            //             array(
            //                 'text' => '{LNG_Picking Key}',
            //                 'url' => 'index.php?module=keyout'
            //             ),
            //             array(
            //                 'text' => '{LNG_Vehicle Confirm}',
            //                 'url' =>'index.php?module=pdiout'
            //             ),
            //             array(
            //                 'text' => '{LNG_Delivery}',
            //                 'url' =>'index.php?module=VDC-delivery'
            //             )
            //         )
            //     ),

                // 'member' => array(
                //     'text' => '{LNG_Users}',
                //     'submenus' => array(
                //         array(
                //             'text' => '{LNG_Member list}',
                //             'url' => 'index.php?module=member'
                //         ),
                //         array(
                //             'text' => '{LNG_Permission}',
                //             'url' => 'index.php?module=permission'
                //         ),
                //         array(
                //             'text' => '{LNG_Member status}',
                //             'url' => 'index.php?module=memberstatus'
                //         )
                //     )
                // ),
                // 'report' => array(
                //     'text' => '{LNG_Report}',
                //     'url' => 'index.php?module=report',
                //     'submenus' => array()
                // ),
                // 'settings' => array(
                //     'text' => '{LNG_Settings}',
                //     'url' => 'index.php?module=settings',
                //     'submenus' => $settings
                // ),

            //     'logout' => array(
            //         'text' => '{LNG_Logout}',
            //         'url' => 'index.php?action=logout'
            //     )
            // );
        }
        // ไม่ได้ login
        return array(
            'home' => array(
                'text' => '{LNG_Home}'
            )
        );
    }
}
