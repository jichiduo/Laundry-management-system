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


new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';

    public bool $myItemModal = false;
    public bool $myCustomerModal = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public Customer $myCustomer; //new Customer

    public string $uname = '';
    public string $code = '';
    public string $email = '';
    public string $tel = '';
    public string $address = '';
    public string $remark = '';
    public string $print = '';
    public $action = "new";
    


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


};
?>

<div>
    <!-- HEADER -->
    <x-header title="Work Order" separator progress-indicator>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-button icon="o-magnifying-glass" wire:click="getDownload" class="btn-ghost btn-xs text-blue-500"
            tooltip="Search" />


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
                    <x-button icon="o-user-plus" wire:click="selectItem({{ $Customer['id'] }},'edit')"
                        class="btn-ghost btn-xs text-blue-500" tooltip="Choose" />
                </div>
                @endscope
            </x-table>
        </div>
    </x-modal>
</div>