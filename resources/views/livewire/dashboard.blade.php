<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Mary\Traits\Toast;

new class extends Component {
    //
    public string $division_name = '';
    public $pickup = 0;
    public $inprogress = 0;
    public $sales = 0;
    public string $search = '';

    //get division name by current user id 
    public function mount(): void 
    {
        $user_id = Auth()->user()->id;
        $division_id = Auth()->user()->division_id;
        $group_id = Auth()->user()->group_id;
        $my_id = 0;
        //check if division_id is null
        if($division_id == null || $group_id == null){
            $this->error("Fetal Err, cannot find basic info for the current user.", position: 'toast-top');
            return;
        }

        if( Auth()->user()->role == 'user'){
            $my_id = $division_id;
        } else {
            $my_id = $group_id;
        }
        //get job count from work_order 
        if( Auth()->user()->role == 'user'){
            $sql = "select count(*) as cnt from work_orders where division_id = ? and status = '4pickup'";
        } else {
            $sql = "select count(*) as cnt from work_orders where group_id = ? and status = '4pickup'";
        }
        $cnt = DB::select($sql, [$my_id]);
        foreach ($cnt as $c) {
            $this->pickup = $c->cnt;
            break;
        }
        if( Auth()->user()->role == 'user'){
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
        if( Auth()->user()->role == 'user'){
            $sql = "select ifnull(sum(grand_total),0) as total from work_orders where division_id = ? and status != 'draft' and created_at >= CURDATE()";
        } else {
            $sql = "select ifnull(sum(grand_total),0) as total from work_orders where group_id = ? and status != 'draft' and created_at >= CURDATE()";
        }
        $cnt = DB::select($sql, [$my_id]);
        foreach ($cnt as $c) {
            $this->sales = $c->total;
            break;
        }
    }

}; ?>

<div>

    <!-- HEADER -->
    <x-header title="{{__('Home')}}" separator progress-indicator>
    </x-header>

    <!-- TABLE  -->
    <x-card title="{{__('Welcome')}}, {{ Auth()->user()->name }}"
        subtitle="{{__('Current shop')}}: {{ Auth()->user()->division_name }}" separator>
        <div class="p-4 rounded-xl grid lg:grid-cols-3 gap-4 bg-base-200">
            <x-stat title="{{__('Ready for customer Pickup')}}" value="{{ $pickup }}" icon="o-truck" />
            <x-stat title="{{__('In Progress')}}" value="{{ $inprogress }}" icon="o-bolt" />
            <x-stat title="{{__('Today Sales')}}" value="{{ $sales }}" icon="o-banknotes" />
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
                    icon="o-magnifying-glass" autocomplete="off" />
            </x-card>
        </div>



    </x-card>

</div>