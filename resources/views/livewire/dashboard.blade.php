<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;

new class extends Component {
    use Toast;
    //
    public string $division_name = '';
    public $pickup = 0;
    public $inprogress = 0;
    public $sales = 0;
    public $month_sales = 0;
    public string $search = '';


    //get division name by current user id 
    public function mount(): void
    {
        $user_id = Auth::user()->id;
        $division_id = Auth::user()->division_id;
        $group_id = Auth::user()->group_id;
        $my_id = 0;
        //check if division_id is null
        if ($division_id == null || $group_id == null) {
            $this->error(__("Fetal Err, cannot find basic info for the current user."), position: 'toast-top');
            return;
        }

        if (Auth::user()->role == 'user') {
            $my_id = $division_id;
        } else {
            $my_id = $group_id;
        }
        //get 4pickup job count from work_order 
        /*
        if( Auth::user()->role == 'user'){
            $sql = "select count(*) as cnt from work_orders where division_id = ? and status = '4pickup'";
        } else {
            $sql = "select count(*) as cnt from work_orders where group_id = ? and status = '4pickup'";
        }
        $cnt = DB::select($sql, [$my_id]);
        foreach ($cnt as $c) {
            $this->pickup = $c->cnt;
            break;
        }
        */
        if (Auth::user()->role == 'user') {
            $sql = "select count(*) as cnt from work_orders where division_id = ? and status = 'pending'";
        } else {
            $sql = "select count(*) as cnt from work_orders where group_id = ? and status = 'pending'";
        }
        $cnt = DB::select($sql, [$my_id]);
        foreach ($cnt as $c) {
            $this->inprogress = $c->cnt;
            break;
        }
        //get today sales from work_order
        if (Auth::user()->role == 'user') {
            $sql = "select ifnull(sum(grand_total),0) as total from work_orders where division_id = ? and status not in ('draft' , 'cancel') and date(created_at) = CURDATE()";
        } else {
            $sql = "select ifnull(sum(grand_total),0) as total from work_orders where group_id = ? and status not in ('draft' , 'cancel') and date(created_at) = CURDATE()";
        }
        $cnt = DB::select($sql, [$my_id]);
        foreach ($cnt as $c) {
            $this->sales = $c->total;
            break;
        }
        //get this month sales from work_order
        if (Auth::user()->role == 'user') {
            //chang from the month to year and month
            $sql = "select ifnull(sum(grand_total),0) as total from work_orders where division_id = ? and status not in ('draft' , 'cancel') and YEAR(created_at) = YEAR(CURDATE()) and MONTH(created_at) = MONTH(CURDATE())";
        } else {
            $sql = "select ifnull(sum(grand_total),0) as total from work_orders where group_id = ? and status not in ('draft' , 'cancel') and YEAR(created_at) = YEAR(CURDATE()) and MONTH(created_at) = MONTH(CURDATE())";
        }
        $cnt = DB::select($sql, [$my_id]);
        foreach ($cnt as $c) {
            $this->month_sales = $c->total;
            break;
        }
    }

    public function findItem()
    {
        if ($this->search != '') {
            //check the length of the search
            if (strlen($this->search) == 9) {
                $wo = WorkOrder::where('wo_no', $this->search)->first();
                //check if get data from DB
                if ($wo) {
                    //redirect to wo_view
                    return redirect()->route('wo_view', ['id' => $wo->id, 'action' => 'show']);
                }
                $this->warning(__("Can not find this Work Order Number"), position: 'toast-top');
                $this->search = '';
                return;
            }
            //$woi = DB::table('work_orders_item')->where('barcode', $this->search)->first();
            $woi = WorkOrderItem::where('barcode', $this->search)->first();
            //check if get data from DB
            if ($woi) {
                //$wo = DB::table('work_orders')->where('wo_no', $woi->wo_no)->first();
                $wo = WorkOrder::where('wo_no', $woi->wo_no)->first();
                //check if get data from DB
                if ($wo) {
                    //redirect to wo_view
                    return redirect()->route('wo_view', ['id' => $wo->id, 'action' => 'show']);
                }
            }
            //show err message
            $this->warning(__("Can't find the tracing number/barcode."), position: 'toast-top');
            $this->search = '';
        }
    }
}; ?>

<div>

    <!-- HEADER -->
    <x-header title="{{__('Home')}}" separator progress-indicator>
    </x-header>

    <!-- TABLE  -->
    <x-card title="{{__('Welcome')}}, {{ Auth::user()->name }}"
        subtitle="{{__('Current shop')}}: {{ Auth::user()->division_name }}" separator>
        <div class="p-4 rounded-xl grid lg:grid-cols-3 gap-4 bg-base-200">
            {{-- <x-stat title="{{__('Ready for Pickup')}}" value="{{ $pickup }}" icon="o-truck" /> --}}
            <a href="/workorder/list"><x-stat title="{{__('In Progress')}}" value="{{ $inprogress }}" icon="o-bolt" /></a>
            <a href="/report/daily"><x-stat title="{{__('Today Sales')}}" value="{{ number_format($sales,0,',','.') }}" icon="o-banknotes" /></a>
            <a href="/report/monthly"><x-stat title="{{__('Month Sales')}}" value="{{ number_format($month_sales,0,',','.') }}" icon="o-chart-bar" /></a>
        </div>
        <div class="p-4 mt-4 rounded-xl grid lg:grid-cols-2 gap-4 bg-base-200">
            <x-card title="{{__('New Work Order')}}"
                subtitle="{{__('if the customer is new, please create new customer first')}}" separator>
                <x-button label="{{__('New Work Order')}}" link="/workorder/new" class="btn-primary"
                    icon="o-rocket-launch" />
                <x-button label="{{__('New Customer')}}" link="/customer" class="btn-primary" icon="o-user-plus" />
            </x-card>
            <x-card title="{{__('Find Item')}}" subtitle="{{__('please enter/scan tracing number/barcode')}}" separator>
                <x-input placeholder="{{__('Search')}}..." wire:model.live.debounce="search" clearable
                    icon="o-magnifying-glass" autocomplete="off" wire:keydown.enter='findItem()' />
            </x-card>
        </div>



    </x-card>

</div>