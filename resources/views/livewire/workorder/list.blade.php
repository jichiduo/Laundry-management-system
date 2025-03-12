<?php

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\AppGroup;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Validate;
use App\Http\Controllers\WorkOrderController;


new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';
    public string $start_date = '';
    public string $end_date = '';

    public bool $myModal = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public WorkOrder $myWorkOrder; //new user

    public string $content = '';

    public $action = "new";


    //close Modal
    public function closeModal(): void
    {
        $this->reset();
        $this->resetPage();
        $this->myModal = false;
    }
    //select Item
    public function selectItem($id, $action)
    {

        $this->selectedItemID = $id;
        $this->action = $action;

        if ($action == 'new') {
            $this->redirect('/workorder/new');
        } elseif ($action == 'edit') {
            $this->myWorkOrder = WorkOrder::find($id);
            //check if the work order created_at is not today
            if (date('Y-m-d', strtotime($this->myWorkOrder->created_at)) != date('Y-m-d')) {
                $this->error(__('You can only edit work orders created today.'), position: 'toast-top');
                return;
            }
            //check if the staus not in draft 
            if ($this->myWorkOrder->status == 'draft') {
                return redirect()->route('wo_update', $id);
            } else {
                $this->error(__('You can only edit draft work orders.'), position: 'toast-top');
                return;
            }
        } elseif ($action == 'delete') {
            //check if the work order status is draft and created by current user
            $this->myWorkOrder = WorkOrder::find($id);
            if ($this->myWorkOrder->status == 'draft' && $this->myWorkOrder->user_id == Auth::user()->id) {
                WorkOrder::destroy($id);
                $sql = "delete from work_order_items where wo_no = ?";
                $rc = DB::update($sql, [$this->myWorkOrder->wo_no]);
                if ($rc < 0) {
                    $this->error(__("Work Order Items data not deleted."), position: 'toast-top');
                    return;
                }
                $this->success("Data deleted.", position: 'toast-top');
                $this->reset();
                $this->resetPage();
            } elseif (($this->myWorkOrder->status == 'draft' || $this->myWorkOrder->status == 'pending') && Auth::user()->role != 'user') {
                //update work order status to cancel
                $this->myWorkOrder->status = 'cancel';
                $this->myWorkOrder->save();
                $sql = "update work_order_items set status='cancel' where wo_no = ?";
                $rc = DB::update($sql, [$this->myWorkOrder->wo_no]);
                if ($rc < 0) {
                    $this->error(__("Work Order Items data did not updated."), position: 'toast-top');
                    return;
                }
            } else {
                $this->error(__("You can only delete draft work orders created by you."), position: 'toast-top');
                return;
            }
        } elseif ($action == 'collect') {
            //check if the work order status is draft and created by current user
            $this->myWorkOrder = WorkOrder::find($id);
            if (($this->myWorkOrder->status == '4pickup' || $this->myWorkOrder->status == 'pending') && ($this->myWorkOrder->user_id == Auth::user()->id || Auth::user()->role != 'user')) {
                //collect_date set to today
                $this->myWorkOrder->collect_date = now();
                $this->myWorkOrder->status = 'complete';
                $this->myWorkOrder->save();
                $sql = "update work_order_items set status='complete' where wo_no = ?";
                $rc = DB::update($sql, [$this->myWorkOrder->wo_no]);
                if ($rc < 0) {
                    $this->error(__("Work Order Items data did not updated."), position: 'toast-top');
                    return;
                }
                $this->success("Work Order collected , the collection date is today", position: 'toast-top');
                $this->reset();
                $this->resetPage();
            } else {
                $this->warning(__("You can only collect 4pickup work orders."), position: 'toast-top');
                return;
            }
        }
    }


    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'wo_no', 'label' => __('WO No'), 'class' => 'w-24'],
            ['key' => 'created_at', 'label' => __('WO Date'), 'format' => ['date', 'd/m/Y'], 'class' => 'w-24'],
            ['key' => 'customer_name', 'label' => __('Cust Name')],
            ['key' => 'customer_tel', 'label' => __('Cust Tel')],
            ['key' => 'grand_total', 'label' => __('Total')],
            ['key' => 'piece', 'label' => __('Piece'), 'format' => ['currency', '0,.']],
            ['key' => 'status', 'label' => __('Status')],
            ['key' => 'pickup_date', 'label' => __('Pickup Date'), 'format' => ['date', 'd/m/Y'], 'class' => 'w-24'],
        ];
    }

    // get all data from table
    public function allData(): LengthAwarePaginator
    {
        //set the end date equal user input date +1
        $enddate = date('Y-m-d', strtotime($this->end_date . ' +1 day'));
        if ($this->search) {
            $condition = "created_at between '$this->start_date' and '$enddate' and (wo_no like '%$this->search%' or customer_name like '%$this->search%' or customer_tel like '%$this->search%')";
        } else {

            $condition = "created_at between '$this->start_date' and '$enddate'";
        }
        //dd($this->end_date);
        return WorkOrder::query()
            ->whereRaw($condition)
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }


    public function with(): array
    {

        return [
            'allData' => $this->allData(),
            'headers' => $this->headers(),
        ];
    }

    public function mount(): void
    {
        //set start date the today minus 30 days ,end date is today
        $this->start_date = date('Y-m-d', strtotime('-30 days'));
        $this->end_date = date('Y-m-d');
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Work Order List')}}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="{{__('Search')}}..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="{{__('New')}}" class="btn-primary" wire:click="selectItem(0,'new')" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <div class="grid grid-cols-4 gap-2 mt-4 mb-4">
            <x-datetime label="{{__('Start date')}}" wire:model.live.debounce="start_date" icon="o-calendar" />
            <x-datetime label="{{__('End date')}}" wire:model.live.debounce="end_date" icon="o-calendar" />
        </div>
        <x-table :headers="$headers" :rows="$allData" :sort-by="$sortBy" class="table-xs" with-pagination
            show-empty-text link="/workorder/view/{id}/show">
            @scope('cell_status', $data)
            @if($data->status == 'draft')
            <x-badge :value="$data->status" />
            @elseif ($data->status == 'pending')
            <x-badge :value="$data->status" class="text-yellow-500" />
            @elseif ($data->status == '4pickup')
            <x-badge :value="$data->status" class="text-blue-500" />
            @elseif ($data->status == 'complete')
            <x-badge :value="$data->status" class="text-lime-500" />
            @else
            <x-badge :value="$data->status" class="text-red-500" />
            @endif
            @endscope
            @scope('actions', $data)
            <div class="w-36 flex justify-end">
                <x-button icon="o-pencil-square" wire:click="selectItem({{ $data['id'] }},'edit')"
                    class="btn-ghost btn-xs text-blue-500" tooltip="{{__('Edit')}}" />
                <x-button icon="o-trash" wire:click="selectItem({{ $data['id'] }},'delete')"
                    wire:confirm="{{__('Are you sure?')}}" spinner class="btn-ghost btn-xs text-red-500"
                    tooltip="{{__('Delete')}}" />
                <x-button icon="o-truck" wire:click="selectItem({{ $data['id'] }},'collect')" spinner
                    wire:confirm="{{__('This job will set to complete, the collect date will set to today, are you sure?')}}"
                    class="btn-ghost btn-xs text-yellow-500" tooltip="{{__('Collect')}}" />
            </div>
            @endscope
        </x-table>
    </x-card>

</div>