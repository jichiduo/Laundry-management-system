<?php

use App\Models\Customer;
use App\Models\WorkOrder;
use App\Models\AppGroup;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Validate;
use App\Http\Controllers\WorkOrderController;
use function Livewire\Volt\{state};


new class extends Component {
    use Toast;
    use WithPagination;
    

    public string $search = '';

    public bool $myItemModal = false;
    public bool $myCustomerModal = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public Customer $myCustomer; //new Customer
    public WorkOrder $wo; //new WorkOrder

    public string $uname = '';
    public string $code = '';
    public string $customer_name = '';
    public string $customer_email = '';
    public string $customer_tel = '';
    public string $address = '';
    public string $remark = '';
    public string $print = '';
    public string $wo_no = '';
    public $action = "new";
    
    //mount
    public function mount($id): void
    {
        $this->wo = WorkOrder::find($id);
        $this->wo_no = $this->wo->wo_no;
    }

    //close Modal
    public function closeModal($id): void
    {
        $this->reset();
        $this->resetPage();
        if($id==1){
            $this->myItemModal = false;
        } else {
            $this->myCustomerModal = false;
        }
    }
    //select Item
    public function selectItem($id, $action)
    {
        
        $this->selectedItemID = $id;
        $this->action = $action;

        if ($action == 'new') {
            $this->myCustomerModal = true;
        } elseif ($action == 'choose') {
            $myCustomer = Customer::find($id);
            $this->customer_name = $myCustomer->name;
            $this->customer_email = $myCustomer->email;
            $this->customer_tel = $myCustomer->tel;
            $this->myCustomerModal = false;
        }
        
    }


    // Table headers
    public function CustomerHeaders(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-36'],
            ['key' => 'tel', 'label' => 'Tel'],
            ['key' => 'email', 'label' => 'E-mail' ],

        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
    public function Customers(): LengthAwarePaginator
    {
         return Customer::query()
            ->where('is_active', 1)
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%")->orwhere('tel', 'like', "%$this->search%")->orWhere('email', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(3); 

    }


    public function with(): array
    {
        return [
            'Customers' => $this->Customers(),
            'CustomerHeaders' => $this->CustomerHeaders(),
        ];
    }

    public function getDownload() {
        // prepare content
        $wo = new WorkOrderController();
        $this->print = $wo->getReceipt("002");
        unset($wo);
        //download the file
        return response()->streamDownload(function () {
            echo $this->print;
        }, 'receipt.txt');

    }

    public function ConfirmOrder(): void {
        //confirm the order , change the status to pending
        //status: draft->pending->4pickup->complete
        $this->wo->status = 'pending';
        $this->wo->save();
        $this->success('Work Order Confirmed');
    }


};
?>

<div>
    <!-- HEADER -->
    <x-header title="Work Order" subtitle="Work order number:{{$wo_no}}" separator progress-indicator />

    <!-- TABLE  -->
    <x-card title="Customer" separator>
        <x-button label="Choose Customer" icon="o-user-plus" wire:click="selectItem(0,'new')"
            class="btn-ghost btn-xs text-blue-500" tooltip="Choose Customer" />
        <div class="grid grid-cols-3 gap-2  mt-4">
            <x-input label="Customer Name" wire:model="customer_name" disabled />
            <x-input label="Customer Tel" wire:model="customer_tel" disabled />
            <x-input label="Customer Email" wire:model="customer_email" disabled />
        </div>
    </x-card>
    <x-card title="Work Order" separator>
        <x-button label="Download" icon="o-arrow-down-tray" wire:click="getDownload()"
            class="btn-ghost btn-xs text-blue-500" tooltip="Download" />
        <x-button label="Print" icon="o-printer" wire:click="getDownload()" class="btn-ghost btn-xs text-blue-500"
            tooltip="Print" />
    </x-card>
    <div class="flex justify-center mt-4">
        <x-button label="Confirm Work Order" class="btn-primary" wire:click="selectItem(0,'new')" />

    </div>

    <!-- New/Edit Customer modal -->
    <x-modal wire:model="myCustomerModal" separator>
        <div>
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"
                class="mt-4" />
            <x-table :headers="$CustomerHeaders" :rows="$Customers" :sort-by="$sortBy" with-pagination show-empty-text>
                @scope('actions', $Customer)
                <div class="flex justify-end">
                    <x-button icon="o-user-plus" wire:click="selectItem({{ $Customer['id'] }},'choose')"
                        class="btn-ghost btn-xs text-blue-500" tooltip="Choose" />
                </div>
                @endscope
            </x-table>
        </div>
    </x-modal>
</div>