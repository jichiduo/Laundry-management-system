<?php

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\AppGroup;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
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

    public bool $myModal = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public WorkOrder $myWorkOrder; //new user


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
            //check if the staus not in draft 
            if ($this->myWorkOrder->status == 'draft' ) {
                return redirect()->route('workorderupdate', $id);
            } else {
                $this->error(__('You can only edit draft work orders.'), position: 'toast-top');
                return;
            }
        } elseif ($action == 'delete'){
            //check if the work order status is draft and created by current user
            $this->myWorkOrder = WorkOrder::find($id);
            if ($this->myWorkOrder->status == 'draft' && ($this->myWorkOrder->user_id == auth()->user()->id || auth()-user()->role != 'user')) {
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
            } else {
                $this->error(__("You can only delete draft work orders created by you."), position: 'toast-top');
                return;
            }
        } elseif ($action == 'print'){
            $this->myWorkOrder = WorkOrder::find($id);
            //status can not be draft
            if ($this->myWorkOrder->status == 'draft') {
                $this->error(__("You can only print confirmed work orders."), position: 'toast-top');
                return;
            } else {
                $woc = new WorkOrderController();
                $this->print = $woc->getReceipt($this->myWorkOrder->wo_no);
                $filename = $this->myWorkOrder->wo_no.'.txt';
                return response()->streamDownload(function () {
                    echo $this->print;
                }, $filename);
            }
        }
    }


    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'wo_no', 'label' => 'WO No', 'class' => 'w-24'],
            ['key' => 'created_at', 'label' => __('WO Date'), 'format' => ['date', 'd/m/Y'], 'class' => 'w-24'],
            ['key' => 'customer_name', 'label' => __('Cust Name')],
            ['key' => 'customer_tel', 'label' => __('Cust Tel')],
            ['key' => 'grand_total', 'label' => __('Total')],
            ['key' => 'piece', 'label' => __('Piece')],
            ['key' => 'status', 'label' => __('Status')],
            ['key' => 'pickup_date', 'label' => __('Pickup Date'), 'format' => ['date', 'd/m/Y'], 'class' => 'w-24'],
        ];
    }

    // get all data from table
    public function allData(): LengthAwarePaginator
    {
         return WorkOrder::query()
            ->when($this->search, fn(Builder $q) => $q->where('wo_no', 'like', "%$this->search%")->orwhere('customer_name','like', "%$this->search%")->orwhere('customer_tel','like', "%$this->search%"))
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
        <x-table :headers="$headers" :rows="$allData" :sort-by="$sortBy" with-pagination show-empty-text
            link="/workorder/view/{id}">
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
                <x-button icon="o-printer" wire:click="selectItem({{ $data['id'] }},'print')" spinner
                    class="btn-ghost btn-xs text-yellow-500" tooltip="{{__('Print')}}" />
            </div>
            @endscope
        </x-table>
    </x-card>

</div>