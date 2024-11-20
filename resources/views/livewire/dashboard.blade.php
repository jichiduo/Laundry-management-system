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
        $user_id = auth()->user()->id;
        //get division_id from app_users by user_id
        $division_id = DB::table('app_users')->select('division_id')->where('user_id', $user_id)->first()->division_id;
        //check if division_id is null
        if($division_id == null){
            $this->error("Fetal Err, cannot find Division for the current user.", position: 'toast-top');
            return;
        }
        $this->division_name = DB::table('divisions')->select('name')->where('id', $division_id)->first()->name;
        if($this->division_name == null){
            $this->error("Fetal Err, cannot find Division for the current user.", position: 'toast-top');
            return;
        }
        //get job count from work_order 
        $sql = "select count(*) as cnt from work_orders where division_id = ? and status = '4pickup'";
        $cnt = DB::select($sql, [$division_id]);
        foreach ($cnt as $c) {
            $this->pickup = $c->cnt;
            break;
        }
        $sql = "select count(*) as cnt from work_orders where division_id = ? and status = 'pending'";
        $cnt = DB::select($sql, [$division_id]);
        foreach ($cnt as $c) {
            $this->inprogress = $c->cnt;
            break;
        }
        //get today sales from work_order
        $sql = "select ifnull(sum(total),0) as total from work_orders where division_id = ? and status = 'pending' and created_at >= CURDATE()";
        $cnt = DB::select($sql, [$division_id]);
        foreach ($cnt as $c) {
            $this->sales = $c->total;
            break;
        }
    }    
}; ?>

<div>

    <!-- HEADER -->
    <x-header title="Home" separator progress-indicator>
    </x-header>

    <!-- TABLE  -->
    <x-card title="Welcome, {{ auth()->user()->name }}" subtitle="Current shop: {{ $division_name }}" separator>
        <div class="p-4 rounded-xl grid lg:grid-cols-3 gap-4 bg-base-200">
            <x-stat title="Ready for Pickup" value="{{ $pickup }}" icon="o-truck" />
            <x-stat title="In Progress" value="{{ $inprogress }}" icon="o-play-circle" />
            <x-stat title="Today Sales" value="{{ $sales }}" icon="o-banknotes" />
        </div>
        <div class="p-4 mt-4 rounded-xl grid lg:grid-cols-2 gap-4 bg-base-200">
            <x-card title="New Job" subtitle="create a new job" separator>
                <x-button label="New Job" wire:click="newJob" class="btn-primary" icon="o-rocket-launch" />
            </x-card>
            <x-card title="Find Item" subtitle="please enter/scan tracing number/barcode" separator>
                <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"
                    autocomplete="off" />
            </x-card>
        </div>



    </x-card>

</div>