<?php
/**
 * @filesource modules/personnel/controllers/download.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace wms\export;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * export.php?module=personnel-download
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{
    /**
     * ส่งออกไฟล์ person.csv
     *
     * @param Request $request
     */
    private $category;

    public function export(Request $request)
    {

        // 'S1.id','S4.Purchase_order','S1.material_number','S2.material_name','S1.plan_qty','S1.ship_qty','S1.ship_date','S1.ship_flg','S3.box_id','S3.put_date'
    
        $this->category = \index\category\Model::init(false);

        switch ($request->get('type')->toString()){
            case 'ccl':
                $this->ccl($request);
                break;
            case 'stock':
                $this->stock($request);
                break;
            case 'transaction' :
                $this->transaction($request);
                break;
            case 'packing_list':
                $this->packing_list($request);
                break;
            case 'packing' :
                $this->packing($request);
                break;
            case 'detail' :
                $this->detail($request);
                break;
            case 'saledetail' :
                $this->saledetail($request);
                break;
            case 'requisition' :
                $this->requisition($request);
                break;
            case 'cystock' :
                $this->cystock($request);
                break;
            case 'container' :
                $this->container($request);
                break;
            case 'containers' :
                $this->containers($request);
                break;
            case 'shipstock' :
                $this->shipstock($request);
                break;
            case 'transfer_sap' :
                $this->transfer_sap($request);
                break;
        }

    }

    private function transfer_sap(Request $request){

        $header = \wms\csv\Model::transfer_sap();
        $data = array();
        $param = 'Template_Transfer_SAP';

        return \Kotchasan\Csv::send($param, $header, $data, self::$cfg->csv_language);
    }


    private function shipstock(Request $request){

        $params = array(
            'material_number' => $request->request('material_number')->toString(),
        );
        
        $header = \wms\csv\Model::shipstock();
        $datas = array();
        $data = \wms\export\Model::shipstock($params);
        $rs = array();
        $file_name = 'shipstock' . date('His');
        $i = 0;

        foreach ($data as $item){
        
            $rs['no'] = ++$i;
            $rs['sale_order'] = $item['sale_order'];
            $rs['customer_code'] = $item['customer_code'];
            $rs['customer_name'] = $item['customer_name'];
            $rs['serial_number'] = $item['serial_number'];
            $rs['material_number'] = $item['material_number'];
            $rs['quantity'] = $item['quantity'];
            $rs['ship_date'] = $item['ship_date'];
            $rs['ship_date'] = $item['ship_date'];
            $rs['pallet_no'] = $item['pallet_no'];
            $rs['pallet_no'] = $item['pallet_no'];
            $datas[] = $rs;
        }
        return \Kotchasan\Csv::send($file_name, $header, $datas, self::$cfg->csv_language);
    }

    private function containers(Request $request){

        $header = \wms\csv\Model::containers();
        $data = array();
        $datas = array();
        $param = 'containers'. date('His');

        $params = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
            'container' => $request->request('container')->toString(),
            'status' => $request->request('status')->toInt(),
        );

        $data = \wms\export\Model::containers($params);
        $i = 0;
        foreach ($data as $item){
        
            $rs['no'] = ++$i;
            $rs['status'] = $item['status'];
            $rs['receive_date'] = $item['receive_date'];
            $rs['year_lot'] = $item['year_lot'];
            $rs['week_lot'] = $item['week_lot'];
            $rs['lot_no'] = $item['lot_no'];
            $rs['container_size'] = $item['container_size'];
            $rs['model'] = $item['model'];
            $rs['delivery_date'] = $item['delivery_date'];
            $rs['eta_date'] = $item['eta_date'];
            $rs['ata_date'] = $item['ata_date'];
            $rs['container_type'] = $item['container_type'];
            $rs['container'] = $item['container'];
            $rs['container_bl'] = $item['container_bl'];
            $rs['total_material'] = $item['total_material'];
            $rs['total_case'] = $item['total_case'];
            $rs['total_box'] = $item['total_box'];
            $rs['total_quantity'] = $item['total_quantity'];

            $datas[] = $rs;
        }

        return \Kotchasan\Csv::send($param, $header, $datas, self::$cfg->csv_language);
    }


    private function container(Request $request){

        $header = \wms\csv\Model::container();
        $data = array();
        $param = 'Template_container';

        return \Kotchasan\Csv::send($param, $header, $data, self::$cfg->csv_language);
    }


    private function requisition(Request $request){

        $params = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
        );

        $header = \wms\csv\Model::requisition();
        $datas = array();
        $data = \wms\export\Model::requisition($params);
        $rs = array();
        $file_name = 'requisition' . date('His');
        $i = 0;

        foreach ($data as $item){
        
            $rs['no'] = ++$i;
            $rs['tag_no'] = $item['tag_no'];

            if ($item['reason'] == 0) {
                $rs['reason'] = 'Scrap';
            } elseif ($item['reason'] == 1) {
                $rs['reason'] = 'Replacement';
            } elseif ($item['reason'] == 2) {
                $rs['reason'] = 'Rework';
            }

            $rs['req_by'] = $item['req_by'];
            $rs['serial_number'] = $item['serial_number'];
            $rs['material_number'] = $item['material_number'];
            $rs['quantity'] = $item['quantity'];
            $rs['issue_qty'] = $item['issue_qty'];
            $rs['created_at'] = $item['created_at'];
            $rs['username'] = $item['username'];

            $datas[] = $rs;
        }
        return \Kotchasan\Csv::send($file_name, $header, $datas, self::$cfg->csv_language);
    }

    private function saledetail(Request $request){

        $params = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
            'so' => $request->request('so')->toString(),
            'customer' => $request->request('customer')->toString(),
            'status' => $request->request('status')->toInt(),
        );

        $header = \wms\csv\Model::saledetail();
        $datas = array();
        $data = \wms\export\Model::saledetail($params);
        $rs = array();
        $file_name = 'saledetail' . date('His');
        $i = 0;

        foreach ($data as $item){
        
            $rs['no'] = ++$i;
            $rs['sale_order'] = $item['sale_order'];
            $rs['delivery_date'] = $item['delivery_date'];
            $rs['customer_code'] = $item['customer_code'];
            $rs['customer_name'] = $item['customer_name'];
            $rs['material_number'] = $item['material_number'];
            $rs['planed_quantity'] = number_format((float)$item['planed_quantity'], 1, '.', '');
            $rs['ship_qty'] = number_format((float)$item['ship_qty'], 1, '.', '');
            $rs['diff_qty'] = number_format((float)$item['planed_quantity'], 1, '.', '') - number_format((float)$item['ship_qty'], 1, '.', '');

            $datas[] = $rs;
        }
        return \Kotchasan\Csv::send($file_name, $header, $datas, self::$cfg->csv_language);
    }

    private function detail(Request $request){
       
        $params = array(
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date(),
            'sale_order' => $request->request('sale_order')->toString(),
            'material_number' => $request->request('material_number')->toString(),
            'create_from' => $request->request('create_from')->date(),
            'create_to' => $request->request('create_to')->date(),
        );

        $header = \wms\csv\Model::detail();
        $datas = array();
        $data = \wms\export\Model::get_detail($params);
        $rs = array();
        $file_name = 'detail' . date('His');
        $i = 0;

        foreach ($data as $item){
        
            $rs['no'] = ++$i;
            $rs['sale_order'] = $item['sale_order'];
            $rs['material_number'] = $item['material_number'];
            $rs['serial_number'] = $item['serial_number'];
            $rs['original_location'] = $item['original_location'];
            $rs['quantity'] = $item['quantity'];
            $rs['location_code'] = $item['location_code'];
            $rs['pick'] = $item['pick'];
            $rs['ship_date'] = $item['ship_date'];
            $rs['truck_confirm_date'] = $item['truck_confirm_date'];
            $rs['pallet_no'] = $item['pallet_no'];
            $rs['truck_date'] = $item['truck_date'];
            $rs['truck_id'] = $item['truck_id'];
            $rs['confirm_flg'] = $item['confirm_flg'];
            $rs['confirm_date'] = $item['confirm_date'];
            $rs['file_name'] = $item['file_name'];
            $datas[] = $rs;
        }
        return \Kotchasan\Csv::send($file_name, $header, $datas, self::$cfg->csv_language);
    }

    private function packing_list(Request $request){

        $header = \wms\csv\Model::packing_list();
        $data = array();
        $param = 'Template_Packing_List';

        return \Kotchasan\Csv::send($param, $header, $data, self::$cfg->csv_language);
    }

    private function packing(Request $request){

        $params = array(
            'from' => $request->get('from')->date(),
            'to' => $request->get('to')->date(),
            'status' => $request->get('status')->toInt(),
            'container' => $request->request('container')->toString()
        );

        $header = \wms\csv\Model::packing();
        $datas = array();
        $data = \wms\export\Model::PackingList($params);
        $rs = array();
        $file_name = 'Packing List' . date('His');
        $i = 0;

        foreach ($data as $item){

            $rs['no'] = ++$i;
            $rs['container'] = $item['container'];
            $rs['case'] = $item['case_number'];
            $rs['serial_number'] = $item['box_id'];
            $rs['material_number'] = $item['material_number'];
            $rs['material_name_en'] = $item['material_name_en'];
            $rs['Quantity'] = $item['quantity'];
            $rs['unit'] = $item['unit'];

            if ($item['receive_flg'] == 0) {
                $item['receive_flg'] = "<center><mark class=term2>{LNG_Waiting Receive}</mark></center>";
                $rs['status'] = 'Waiting Receive';
            } elseif ($item['receive_flg'] == 1)  {
                $item['receive_flg'] = "<center><mark class=term6>Received</mark></center>";
                $rs['status'] = 'Received';
            } elseif ($item['receive_flg'] == 2) {
                $item['receive_flg'] = "<center><mark class=term3>Short Ship</mark></center>";
                $rs['status'] = 'Short Ship';
            }

            $datas[] = $rs;
        }

        return \Kotchasan\Csv::send($file_name, $header, $datas, self::$cfg->csv_language);

    }

    private function transaction(Request $request){

        $params = array(
            'from' => $request->get('from')->date(),
            'to' => $request->get('to')->date(),
        );

        $header = \wms\csv\Model::transaction();

        $datas = array();
        $data = \wms\export\Model::getTransaction($params);
        $rs = array();
        $file_name = 'Transaction' . date('His');
        $i = 0;

        foreach ($data as $item){
            
            $rs['no'] = ++$i;
            $rs['transaction_date'] = $item['transaction_date'];
            $rs['transaction_type'] = $item['transaction_type'];
            $rs['container'] = $item['container'];
            $rs['case'] = $item['case_number'];
            $rs['serial_number'] = $item['serial_number'];
            $rs['material_number'] = $item['material_number'];
            $rs['material_name_en'] = $item['material_name_en'];
            $rs['quantity'] = $item['quantity'];
            $rs['unit'] = $item['unit'];
            $rs['location_code'] = $item['location_code'];
            $rs['sale_order'] = $item['sale_order'];
            $rs['pallet'] = $item['pallet'];
            $rs['truck_id'] = $item['truck_id'];
            $rs['truck_date'] = $item['truck_date'];
            $rs['username'] = $item['username'];

            $datas[] = $rs;
        }
        return \Kotchasan\Csv::send($file_name, $header, $datas, self::$cfg->csv_language);
    }

    private function ccl(Request $request){

        $header = \wms\csv\Model::importccl();
        $data = array();
        $param = 'Import_CCL';

        return \Kotchasan\Csv::send($param, $header, $data, self::$cfg->csv_language);
    }

    private function stock(Request $request){

        $header = \wms\csv\Model::stock();

        $datas = array();
        $data = \wms\export\Model::getStock();
        $rs = array();
        $file_name = 'Stock' . date('His');
        $i = 0;

        foreach ($data as $item){
            
            $rs['no'] = ++$i;
            $rs['container'] = $item['container'];
            $rs['case'] = $item['case_number'];
            $rs['serial_number'] = $item['serial_number'];
            $rs['material_number'] = $item['material_number'];
            $rs['material_name_en'] = $item['material_name_en'];
            $rs['material_type'] = $this->category->get('material_type', $item['material_type']);
            $rs['actual_quantity'] = $item['actual_quantity'];
            $rs['unit'] = $item['unit'];
            $rs['inbound_date'] = $item['inbound_date'];
            $rs['location_code'] = $item['location_code'];

            $datas[] = $rs;
        }
        return \Kotchasan\Csv::send($file_name, $header, $datas, self::$cfg->csv_language);
    }

    private function cystock(Request $request){

        $header = \wms\csv\Model::cystock();

        $datas = array();
        $data = \wms\export\Model::cyStock();
        $rs = array();
        $file_name = 'Cy_Stock' . date('His');
        $i = 0;

        foreach ($data as $item){
            
            $rs['no'] = ++$i;
            $rs['container'] = $item['container'];
            $rs['case_number'] = $item['case_number'];
            $rs['box_id'] = $item['box_id'];
            $rs['temp_material'] = $item['temp_material'];
            $rs['quantity'] = $item['quantity'];
            $rs['delivery_order'] = $item['delivery_order'];
            $rs['container_received'] = $item['container_received'];
            $rs['storage_location'] = $item['storage_location'];

            $datas[] = $rs;
        }
        return \Kotchasan\Csv::send($file_name, $header, $datas, self::$cfg->csv_language);
    }

}
