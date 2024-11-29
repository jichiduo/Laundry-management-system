<?php

use App\Models\Customer;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\AppGroup;
use App\Models\Product;
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
    public array $Item_sortBy = ['column' => 'id', 'direction' => 'asc'];

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
            ['key' => 'name', 'label' => __('Name'), 'class' => 'w-36'],
            ['key' => 'tel', 'label' => __('Tel')],
            ['key' => 'email', 'label' => __('Email') ],

        ];
    }
    // Table headers
    public function WOItemHeaders(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'barcode', 'label' => __('Barcode'), 'class' => 'w-24'],
            ['key' => 'name', 'label' => __('Name')],
            ['key' => 'price', 'label' => __('Price') ],
            ['key' => 'unit', 'label' => __('Unit') ],
            ['key' => 'quantity', 'label' => __('Quantity') ],
            ['key' => 'discount', 'label' => __('Discount') ],
            ['key' => 'tax', 'label' => __('Tax') ],
            ['key' => 'sub_total', 'label' => __('Sub Total') ],
            ['key' => 'turnover', 'label' => __('Turnover') ],
            ['key' => 'remark', 'label' => __('Remark') ],
            ['key' => 'location', 'label' => __('Location') ],

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

    public function WOItems(): LengthAwarePaginator
    {
         return WorkOrderItem::query()
            ->where('wo_no', $this->wo_no)
            ->orderBy(...array_values($this->Item_sortBy))
            ->paginate(10); 

    }


    public function with(): array
    {
        return [
            'Customers' => $this->Customers(),
            'CustomerHeaders' => $this->CustomerHeaders(),
            'WOItems' => $this->WOItems(),
            'WOItemHeaders' => $this->WOItemHeaders(),
            'products' => Product::all(),
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
        $this->success(__('Work Order Confirmed'));
    }


};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Work Order')}}" subtitle="{{__('Work order number')}}:{{$wo_no}}" separator
        progress-indicator />

    <!-- TABLE  -->
    <x-card title="{{__('Customer')}}" separator>
        <x-button label="{{__('Choose Customer')}}" icon="o-user-plus" wire:click="selectItem(0,'new')"
            class="btn-ghost btn-xs text-blue-500" tooltip="{{__('Choose Customer')}}" />
        <div class="grid grid-cols-3 gap-2  mt-4">
            <x-input label="{{__('Customer Name')}}" wire:model="customer_name" disabled />
            <x-input label="{{__('Customer Tel')}}" wire:model="customer_tel" disabled />
            <x-input label="{{__('Customer Email')}}" wire:model="customer_email" disabled />
        </div>
    </x-card>
    <x-card title="{{__('Basic Information')}}" separator>
        <x-button label="Download" icon="o-arrow-down-tray" wire:click="getDownload()"
            class="btn-ghost btn-xs text-blue-500" tooltip="Download" />
        <x-button label="Print" icon="o-printer" wire:click="getDownload()" class="btn-ghost btn-xs text-blue-500"
            tooltip="Print" />
    </x-card>
    <x-card title="{{__('Details')}}" separator>
        <div class="flex justify-end mr-4">
            <x-button label="{{__('New Item')}}" icon="o-inbox-arrow-down" wire:click="getDownload()"
                class="btn-ghost btn-xs text-blue-500" tooltip="{{_('New Item')}}" />
        </div>
        <x-table :headers="$WOItemHeaders" :rows="$WOItems" :sort-by="$Item_sortBy" with-pagination show-empty-text>
            @scope('actions', $WOItem)
            <div class="flex justify-end">
                <x-button icon="o-trash" wire:click="selectItem({{ $WOItem['id'] }},'remove')"
                    class="btn-ghost btn-xs text-red-500" tooltip="{{__('Remove')}}" />
            </div>
            @endscope
        </x-table>
    </x-card>
    <div class="flex justify-center mt-4">
        <x-button label="{{__('Confirm Work Order')}}" class="btn-primary" wire:click="selectItem(0,'new')" />

    </div>

    <!-- New/Edit Customer modal -->
    <x-modal wire:model="myCustomerModal" separator>
        <div>
            <x-input placeholder="{{__('Search')}}..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" class="mt-4" />
            <x-table :headers="$CustomerHeaders" :rows="$Customers" :sort-by="$sortBy" with-pagination show-empty-text>
                @scope('actions', $Customer)
                <div class="flex justify-end">
                    <x-button icon="o-user-plus" wire:click="selectItem({{ $Customer['id'] }},'choose')"
                        class="btn-ghost btn-xs text-blue-500" tooltip="{{__('Choose')}}" />
                </div>
                @endscope
            </x-table>
        </div>
    </x-modal>
    <x-modal wire:model="myItemModal" separator>
        <div class="grid grid-cols-2 gap-2">
            <x-input label="{{__('Barcode')}}" wire:model="ItemBarcode" />
            <x-select label="{{__('Select Product')}}" wire:model="role" :options="$products" option-value="name"
                option-label="name" placeholder="{{__('Select Product')}}" />

        </div>
    </x-modal>
</div>