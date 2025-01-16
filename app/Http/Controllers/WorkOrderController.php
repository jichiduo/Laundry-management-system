<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\Division;
use App\Models\Transaction;

class WorkOrderController extends Controller
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
        ];
    }
    //generate work order number
    public function get_wo_no($division_id): string
    {
        //get the last work order number
        //crate a new wo_no , the WO rule: 2 digi division id+yymm+001(seq no,start from 0001 every month 1st day)
        //how to create a sequence in mariadb, CREATE SEQUENCE seq START WITH 1 INCREMENT BY 1;
        //how to reset the sequence , ALTER SEQUENCE seq restart = 1 ;
        //event scheduler , enable it by add this to my.ini [mysqld] , event_scheduler=ON
        /*
            CREATE EVENT event1
            ON SCHEDULE EVERY '1' MONTH
            STARTS '2021-11-01 00:00:00'
            DO 
            ALTER SEQUENCE seq restart = 1 ;
         */
        $sql = "select nextval(seq_job) as sn";
        $sn  = DB::select($sql);
        $sn  = (int) $sn[0]->sn;
        $wo_no = sprintf("%02d", $division_id) . date('ym') . sprintf("%03d", $sn);
        return $wo_no;
    }


    //get receipt print content by work order number
    public function getReceipt($workOrderNumber)
    {
        $workOrder = WorkOrder::where('wo_no', $workOrderNumber)->first();
        $workOrderItems = WorkOrderItem::where('wo_no', $workOrderNumber)->get();
        $txn = Transaction::where('wo_no', $workOrderNumber)->get();
        $division = Division::where('id', $workOrder->division_id)->first();
        $content  = "\t" . $division->name . "\n\n";
        $content .= $division->address . "\n";
        $content .= __('Tel') . ':' . $division->tel . "\n\n";
        $content .= __('WO No:') . $workOrder->wo_no . "\n";
        $content .= __('WO Date:') . date_format($workOrder->created_at, 'd-m-Y H:i:s') . "\n";
        $content .= __('Customer Name') . ":" . $workOrder->customer_name . "\n";
        $content .= __('Customer Tel') . ":" . $workOrder->customer_tel . "\n";
        $content .= "---------------------------------\n";
        $content .= "Qty\t" . __('Item Name') . "\t" . __('Amount') . "\n";
        $content .= "---------------------------------\n";
        foreach ($workOrderItems as $woi) {
            $content .= $woi->quantity . "\t" . str_pad(substr($woi->name, 0, 14), 14) . "\t" . $woi->sub_total . "\n";
        }
        $content .= "---------------------------------\n";
        $content .= __('Total') . "\t" . $workOrder->total . "\n";
        $content .= "---------------------------------\n";
        $content .= __('Included:') .  "\n";
        $content .= __('-Tax') . "\t" . $workOrder->tax . "\n";
        $content .= __('-Discount') . "\t" . $workOrder->discount . "\n";
        $content .= "---------------------------------\n";
        $content .= __('Grand Total') . "\t" . $workOrder->grand_total . "\n";
        $content .= "---------------------------------\n";
        $content .= __('Payments') . "\t" . $workOrder->payment_method . "\n";
        foreach ($txn as $t) {
            $content .= "-" . $t->payment_type . "\t" . $t->amount . "\n";
            if ($t->payment_type == 'Member Card') {
                $content .= __("-Card No:")  . $t->card_no . "\n";
            }
        }

        $content .= "\n\n\n\n";
        $content .= "\t" . __('Explain') . "\n";
        $content .= "---------------------------------\n";
        $content .= __("Collect Date:") . date_format($workOrder->pickup_date, 'd-m-Y') . "\n\n";
        $content .= $workOrder->explain . "\n\n\n\n";
        $content .= "---------------------------------\n";
        $content .= "\t" . __('Thank You') . "\n";
        $content .= "\t" . __('See You Again') . "\n";
        $content .= "---------------------------------\n";
        $content .= __('Powered By:') . $workOrder->group_name . "\n\n\n";
        $content .= "\t" . __('Attention') . "\n";
        $content .= "---------------------------------\n";
        $content .= __('1. Damage/fade/tear/shrinkage of clothing in the washing and drying process caused by the basic nature of the clothing material is not our responsibility, it is the consumer risk') . "\n";
        $content .= __('2. The loss of valuables left in clothes is not our responsibility.') . "\n";
        $content .= __('3. If the amount of clothing has been filled in accurately and correctly, then the amount we calculated is considered correct') . "\n";
        $content .= __('4. Complaints occur 1x24 hours after the laundry is picked up') . "\n";

        $content .= "\n\n\n\n\n\n\n\n";
        //write content to a file
        $filename = substr($workOrderNumber, 0, 4) . "/receipt/" . $workOrderNumber . ".txt";
        Storage::disk('public')->put($filename, $content);
        return $content;
    }

    private function getExternalRate($from_CY, $to_CY)
    {
        //loading jquery https://gasparesganga.com/labs/jquery-loading-overlay/
        //composer remove vendor/package
        // Get the latest rate
        $apikey = env("EXCHANGE_RATE_API_KEY", "a52226c49dc5cb895f7c");

        $from_Currency = urlencode($from_CY);
        $to_Currency = urlencode($to_CY);
        $query = "{$from_Currency}_{$to_Currency}";
        // change to the free URL if you're using the free version
        $json = file_get_contents("http://free.currencyconverterapi.com/api/v5/convert?q={$query}&compact=y&apiKey={$apikey}");
        //Log::info($json);
        /* register a API KEY here: https://free.currencyconverterapi.com/
         * json:
        {"CNY_SGD":{"val":0.206911}}
        obj
        array (
              'CNY_SGD' =>
              array (
                'val' => 0.206911,
              ),
            )
         * */
        $obj = json_decode($json, true);
        $val = $obj["$query"];
        $rate = $val['val'] * 1;

        return $rate;
    }
    private function getInternalRate($from_CY, $to_CY)
    {
        //find the id

        if (DB::table('exchange_rates')->where('from_ccy', $from_CY)->where('to_ccy', $to_CY)->exists()) {
            $rates = DB::table('exchange_rates')->where('from_ccy', $from_CY)->where('to_ccy', $to_CY)->pluck('rate');
            foreach ($rates as $r) {
                if ($r > 0) {
                    $rate = $r;
                    break;
                }
            }
        } else {
            if (DB::table('exchange_rates')->where('from_ccy', $to_CY)->where('to_ccy', $from_CY)->exists()) {
                $rates = DB::table('exchange_rates')->where('from_ccy', $to_CY)->where('to_ccy', $from_CY)->pluck('rate');
                foreach ($rates as $r) {
                    if ($r > 0) {
                        $rate = $r;
                        break;
                    }
                }
                if ($rate != 0) {
                    $rate = 1 / $rate;
                }
            } else {
                //didn't find
                $rate = 0;
            }
        }

        /*
        $sql = "select rate from exchange_rate where from_ccy = ? and to_ccy = ?";
        //Log::info($sql);
        $rates = DB::select($sql, [$from_CY, $to_CY]);
        foreach ($rates as $r) {
            if ($r->rate > 0) {
                $rate = $r->rate;
            }
        }
        */
        return $rate;
    }

    //get exchange rate
    public function getExchangeRate($from_CY, $to_CY)
    {
        if (!isset($from_CY)) {
            $from_CY = "SGD";
        }
        if (!isset($to_CY)) {
            $to_CY = "SGD";
        }
        if ($from_CY == $to_CY) {
            return 1;
        }
        $method = env("EXCHANGE_RATE", "INTERNAL");
        if ($method == "EXTERNAL") {
            return $this->getExternalRate($from_CY, $to_CY);
        }
        return $this->getInternalRate($from_CY, $to_CY);
    }
}
