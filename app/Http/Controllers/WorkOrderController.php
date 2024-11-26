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
use App\Models\Customer;

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

    //get receipt print content by work order number
    public function getReceipt($workOrderNumber)
    {
        $workOrder = WorkOrder::where('wo_no', $workOrderNumber)->first();
        $workOrderItems = WorkOrderItem::where('wo_no', $workOrderNumber)->get();
        $totalAmount = 0;
        $logs = Customer::all();
        $content = "Customers \n";
        foreach ($logs as $log) {
            $content .= $log->name;
            $content .= "\n";
        }
        $content .= "wo:" . $workOrderNumber;
        //$this->print = $content;
        return $content;
    }
}
