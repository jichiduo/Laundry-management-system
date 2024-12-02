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
use Carbon\Carbon;
use function Livewire\Volt\{state};


new class extends Component {
    use Toast;
    use WithPagination;
    

    public string $search = '';

    public bool $myItemModal = false;
    public bool $myCustomerModal = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public array $Item_sortBy = ['column' => 'id', 'direction' => 'asc'];

    public Customer $myCustomer; 
    public WorkOrder $wo; 
    public WorkOrderItem $woi; 
    public Product $myProduct; 

    public string $uname = '';
    public string $code = '';
    public        $customer_id = 0;
    public string $customer_name = '';
    public string $customer_email = '';
    public string $customer_tel = '';
    public        $customer_discount = 0;
    public string $explain = '';
    public int    $is_express = 0;
    public string $print = '';
    public string $wo_no = '';
    public string $wo_status = '';
    public ?int $product_searchable_id = null;
    public Collection $productSearchable;
    #[Validate('unique:work_order_items')]
    public string $barcode = '';
    public string $ItemName = '';
    public string $ItemPrice = '';
    #[Validate('required|numeric|gt:0')]
    public string $ItemQty = '';
    public string $ItemTotal = '';
    public string $ItemSubTotal = '';
    public string $ItemPickup = '';
    public string $ItemTax = '';
    public string $ItemDiscount = '';
    public string $ItemUnit = '';
    public string $ItemTurnover = '';
    public string $ItemRemark = '';
    public string $ItemLocation = '';
    public $tax_rate = 0;

    public $action = "new";
    
    //mount
    public function mount($id): void
    {
        //get tax rate from AppGroup
        $this->tax_rate = AppGroup::where('id', auth()->user()->group_id)->first()->tax_rate;
        //get user name from session
        $this->wo = WorkOrder::findOrFail($id);
        // dd($this->wo);
        $this->wo_no = $this->wo->wo_no;
        $this->wo_status = $this->wo->status;
        if($this->wo->customer_id != null){

            $this->customer_id = $this->wo->customer_id;
            $this->customer_name = $this->wo->customer_name;
            $this->customer_email = $this->wo->customer_email;
            $this->customer_tel = $this->wo->customer_tel;
            $this->customer_discount = $this->wo->customer_discount;
        }
        if($this->wo->explain != null){
            $this->explain = $this->wo->explain;
        }
        $this->is_express = $this->wo->is_express;
        // Fill options when component first renders
        $this->ProductSearch();
    }

    public function ProductSearch(string $value = '')
    {
        // Besides the search results, you must include on demand selected option
        $selectedOption = Product::where('id', $this->product_searchable_id)->get();
 
        $this->productSearchable = Product::query()
            ->where('name', 'like', "%$value%")
            ->take(5)
            ->orderBy('name')
            ->get()
            ->merge($selectedOption);     // <-- Adds selected option
    }    

    public function chooseProduct(): void
    {
        //check if product_searchable_id is null
        if ($this->product_searchable_id == null) {
            $this->ItemName = '';
            $this->ItemPrice = '';
            $this->ItemUnit = '';
            $this->ItemTurnover = '';            
            return;
        }
        $this->myProduct = Product::find($this->product_searchable_id);
        
        if($this->is_express == 1){
            //dd($this->myProduct);
            //check if express_price is null or zero
            if(empty($this->myProduct->express_price)){
                $this->ItemPrice = $this->myProduct->price;
            }else{
                $this->ItemPrice = $this->myProduct->express_price;
            }
            if(empty($this->myProduct->express_turnover)){
                $this->ItemTurnover = $this->myProduct->turnover;
            }else{
                $this->ItemTurnover = $this->myProduct->express_turnover;
            }
        } else {
            $this->ItemPrice    = $this->myProduct->price;
            $this->ItemTurnover = $this->myProduct->turnover;
        }
        $this->ItemName = $this->myProduct->name;
        $this->ItemUnit = $this->myProduct->unit;
        
        unset($this->myProduct);
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

        if ($action == 'newCustomer') {
            $this->myCustomerModal = true;
        } elseif ($action == 'choose') {
            $this->myCustomer = Customer::find($id);
            $this->customer_name = $this->myCustomer->name;
            $this->customer_email = $this->myCustomer->email;
            $this->customer_tel = $this->myCustomer->tel;
            $this->customer_discount = $this->myCustomer->member_discount;
            $this->myCustomerModal = false;
        } 
        
    }
    // new Item
    public function newItem(){
        $this->myItemModal = true;

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
            ['key' => 'name', 'label' => __('Name'), 'class' => 'w-36'],
            ['key' => 'price', 'label' => __('Price'),'format' => ['currency', '0,.'] ],
            ['key' => 'unit', 'label' => __('Unit') ],
            ['key' => 'quantity', 'label' => __('Quantity') ],
            // ['key' => 'discount', 'label' => __('Discount') ],
            // ['key' => 'tax', 'label' => __('Tax') ],
            ['key' => 'sub_total', 'label' => __('SubTotal'),'format' => ['currency', '0,.'] ],
            ['key' => 'pickup_date', 'label' => __('Pickup'), 'format' => ['date', 'd/m/Y'] ],
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
        $this->print = $wo->getReceipt($this->wo_no);
        // unset($wo);
        $filename = $this->wo_no . '.txt';
        //download the file
        return response()->streamDownload(function () {
            echo $this->print;
        }, $filename);

    }

    public function addItem(): void{
        if($this->wo_status!='draft'){
            $this->error(__('Work Order already confirmed'));
            return;
        }
        $validatedData = $this->validate();
        //calc tax
        $this->ItemTotal    = $this->ItemPrice * $this->ItemQty;
        $this->ItemTax      = $this->ItemTotal * $this->tax_rate;
        $this->ItemDiscount = $this->ItemTotal * $this->customer_discount;
        $this->ItemSubTotal = $this->ItemTotal - $this->ItemDiscount + $this->ItemTax;
        //dd($this->ItemTurnover);
        //calc pickup date by today+turnover
        //$this->ItemPickup = date('Y-m-d', strtotime("+".$this->ItemTurnover." day"));
        $this->ItemPickup = Carbon::now()->addDays($this->ItemTurnover*1);
        //save to db
        $this->woi = new WorkOrderItem();
        $this->woi->wo_no = $this->wo_no;
        $this->woi->barcode = $this->barcode;
        $this->woi->name = $this->ItemName;
        $this->woi->price = $this->ItemPrice;
        $this->woi->unit = $this->ItemUnit;
        $this->woi->quantity = $this->ItemQty;
        $this->woi->discount = $this->ItemDiscount;
        $this->woi->tax_rate = $this->tax_rate;
        $this->woi->tax = $this->ItemTax;
        $this->woi->total = $this->ItemTotal;
        $this->woi->sub_total = $this->ItemSubTotal;
        $this->woi->pickup_date = $this->ItemPickup;
        $this->woi->remark = $this->ItemRemark;
        $this->woi->location = $this->ItemLocation;

        $this->woi->save();
        $this->success(__('Item added'));
        // //clear item value
        unset($this->woi);
        $this->barcode = '';
        $this->ItemName = '';
        $this->ItemUnit = '';
        $this->ItemPrice = 0;
        $this->ItemQty = 0;
        $this->ItemTurnover = 0;
        $this->ItemDiscount = 0;
        $this->ItemTax = 0;
        $this->ItemTotal = 0;
        $this->ItemSubTotal = 0;
        $this->ItemRemark = '';
        $this->ItemLocation = '';
        $this->product_searchable_id = null;
        //close modal
        $this->myItemModal = false;

    }

    public function ConfirmOrder() {
        if($this->wo_status!='draft'){
            $this->error(__('Work Order already confirmed'));
            return;
        }
        //check if myCustomer already choose 
        if(empty($this->myCustomer)){
            $this->warning(__('Please choose a customer'));
            return;
        }
        //get aggregate data
        $sql="select max(pickup_date) as pickup_date, count(pickup_date) as cnt, sum(discount) as discount, sum(tax) as tax, sum(total) as total, sum(sub_total) as sub_total from work_order_items where wo_no=?";
        $data = DB::select($sql, [$this->wo_no]);
        $this->wo->piece = $data[0]->cnt;
        if($data[0]->cnt==0){
            $this->warning(__('Please add at least one item'));
            return;
        }
        $this->wo->customer_id = $this->myCustomer->id;
        $this->wo->customer_name = $this->myCustomer->name;
        $this->wo->customer_tel = $this->myCustomer->tel;
        $this->wo->customer_email = $this->myCustomer->email;
        $this->wo->customer_discount = $this->myCustomer->member_discount;
        $this->wo->explain = $this->explain;
        $this->wo->is_express = $this->is_express;
        $this->wo->pickup_date = $data[0]->pickup_date;
        $this->wo->discount = $data[0]->discount;
        $this->wo->tax = $data[0]->tax;
        $this->wo->total = $data[0]->total;
        $this->wo->grand_total = $data[0]->sub_total;
        //status: draft->pending->4pickup->complete
        $this->wo->status = 'pending';
        $this->wo->save();
        $this->getDownload();
        // $this->success(__('Work Order Confirmed'));
        return redirect()->route('wo_view', $this->wo->id);
    }


};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Work Order')}}" subtitle="{{__('Work order number')}}:{{$wo_no}}" separator
        progress-indicator />

    <!-- TABLE  -->
    <x-card title="{{__('Customer')}}" separator>
        @if($wo_status=='draft')
        <x-button label="{{__('Choose Customer')}}" icon="o-user-plus" wire:click="selectItem(0,'newCustomer')"
            class="btn-ghost btn-xs text-blue-500" tooltip="{{__('Choose Customer')}}" />
        @endif
        <div class="grid grid-cols-3 gap-2  mt-4">
            <x-input label="{{__('Customer Name')}}" wire:model="customer_name" disabled />
            <x-input label="{{__('Customer Tel')}}" wire:model="customer_tel" disabled />
            <x-input label="{{__('Customer Email')}}" wire:model="customer_email" disabled />
        </div>
    </x-card>
    <x-card title="{{__('Basic Information')}}" separator>
        <x-textarea label="{{__('Explain')}}" wire:model="explain" placeholder="{{__('write explaination here ...')}}"
            rows="2" hint="{{__('Max 255 chars')}}" inline />
        <div class="mt-4">
            @if($is_express==1)
            <x-checkbox label="{{__('Express')}}" checked wire:model="is_express" />
            @else
            <x-checkbox label="{{__('Express')}}" wire:model="is_express" />
            @endif
        </div>

    </x-card>
    <x-card title="{{__('Details')}}" separator>
        <div class="flex justify-end mr-4">
            @if($wo_status=='draft')
            <x-button label="{{__('New Item')}}" icon="o-inbox-arrow-down" wire:click="newItem()"
                class="btn-ghost btn-xs text-blue-500" tooltip="{{_('New Item')}}" spinner="newItem()" />
            @endif
        </div>
        <x-table :headers="$WOItemHeaders" :rows="$WOItems" :sort-by="$Item_sortBy" with-pagination show-empty-text>
            @if($wo_status=='draft')
            @scope('actions', $WOItem)
            <div class="flex justify-end">
                <x-button icon="o-trash" wire:click="selectItem({{ $WOItem['id'] }},'remove')"
                    class="btn-ghost btn-xs text-red-500" tooltip="{{__('Remove')}}" />
            </div>
            @endscope
            @endif
        </x-table>
    </x-card>
    <div class="flex justify-center mt-4">
        @if($wo_status=='draft')
        <x-button label="{{__('Confirm Work Order')}}" class="btn-primary" wire:click="ConfirmOrder"
            spinner="ConfirmOrder" />
        @endif
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
        <x-input label="{{__('Barcode')}}" wire:model="barcode" />
        <x-choices label="{{__('Select Product')}}" wire:model="product_searchable_id" :options="$productSearchable"
            search-function="ProductSearch" placeholder="{{__('Select Product')}}" debounce="300ms" min-chars="2"
            @change-selection='$wire.chooseProduct()' single searchable />
        <x-input label="{{__('Name')}}" wire:model="ItemName" disabled />
        <div class="grid grid-cols-3 gap-2">
            <x-input label="{{__('Price')}}" wire:model="ItemPrice" disabled />
            <x-input label="{{__('Unit')}}" wire:model="ItemUnit" disabled />
            <x-input label="{{__('Turnover')}}" wire:model="ItemTurnover" disabled />
            <x-input label="{{__('Quantity')}}" wire:model="ItemQty" />
            <x-input label="{{__('Remark')}}" wire:model="ItemRemark" />
            <x-input label="{{__('Location')}}" wire:model="ItemLocation" />
        </div>
        <x-slot:actions>
            <x-button label="{{__('Add Item')}}" wire:click="addItem" spinner="addItem" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>