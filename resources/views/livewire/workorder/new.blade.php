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



new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';

    public bool $myModal = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public Customer $myCustomer; //new Customer

    #[Validate('required')]
    public string $uname = '';
    public string $code = '';
    public string $email = '';
    #[Validate('required')]
    public string $tel = '';
    public string $address = '';
    public string $remark = '';

    public $action = "new";
    

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-top');
    }
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
            $this->myCustomer = new Customer();
            $this->myModal = true;
        } elseif ($action == 'edit') {
            $this->myCustomer = Customer::find($id);
            $this->uname = $this->myCustomer->name;
            $this->tel = $this->myCustomer->tel;
            $this->address = $this->myCustomer->address;
            $this->remark = $this->myCustomer->remark;
            $this->email = $this->myCustomer->email;
            $this->myModal = true;
        } elseif ($action == 'member') {
            $this->info("Coming soon", position: 'toast-top');
            return;
        } elseif ($action == 'delete'){
            $rc=0;
            $sql = "select count(*) as cnt from work_orders where customer_id = ? LIMIT 1";
            $cnt = DB::select($sql, [$id]);
            foreach ($cnt as $c) {
                $rc = $c->cnt;
                break;
            }
            if($rc > 0){
                $this->error("This data is used in work order, can't be deleted.", position: 'toast-top');
                return;
            }
            $data = DB::table('customers')->where('id', [$id])->update([
                'is_active' => 0,
            ]);
            if($data<0){
                $this->error("Data not deleted.", position: 'toast-top');
            }else{
                $this->success("Data deleted.", position: 'toast-top');
            }
            $this->reset();
            $this->resetPage();
        }
        
    }
    //save 
    public function save()
    {

        $validatedData = $this->validate();
        if ($this->action == 'new') {
            $this->myCustomer->create_by = Auth()->user()->id;     
            $this->myCustomer->group_id = Auth()->user()->group_id;      
        }
        $this->myCustomer->name = $this->uname;
        $this->myCustomer->email = $this->email;
        $this->myCustomer->tel = $this->tel;
        $this->myCustomer->address = $this->address;
        $this->myCustomer->remark = $this->remark;
        $this->myCustomer->update_by = Auth()->user()->id;
        $this->myCustomer->save();
        $this->success("Data saved.", position: 'toast-top');
        $this->reset();
        $this->resetPage();
        $this->myModal = false;
    }


    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-36'],
            ['key' => 'tel', 'label' => 'Tel'],
            ['key' => 'email', 'label' => 'E-mail' ],
            ['key' => 'address', 'label' => 'Address' ],
            ['key' => 'remark', 'label' => 'Remark' ],

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
            ->paginate(10); 

    }


    public function with(): array
    {
        return [
            'Customers' => $this->Customers(),
            'headers' => $this->headers(),
        ];
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="Work Order" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="New Work Order" class="btn-primary" wire:click="selectItem(0,'new')" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$Customers" :sort-by="$sortBy" with-pagination show-empty-text>
            @scope('actions', $Customer)
            <div class="w-48 flex justify-end">
                <x-button icon="o-credit-card" wire:click="selectItem({{ $Customer['id'] }},'member')" spinner
                    class="btn-ghost btn-xs text-yellow-500" tooltip="Member Card" />
                <x-button icon="o-pencil-square" wire:click="selectItem({{ $Customer['id'] }},'edit')"
                    class="btn-ghost btn-xs text-blue-500" tooltip="Edit" />
                <x-button icon="o-trash" wire:click="selectItem({{ $Customer['id'] }},'delete')"
                    wire:confirm="Are you sure?" spinner class="btn-ghost btn-xs text-red-500" tooltip="Delete" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- New/Edit Customer modal -->
    <x-modal wire:model="myModal" separator persistent>
        <div>
            <x-input label="Name" wire:model='uname' clearable autocomplete="off" />
            <x-input label="Tel" wire:model='tel' />
            <x-input label="Email" wire:model='email' type="email" />
            <x-input label="Address" wire:model='address' />
            <x-input label="Remark" wire:model='remark' />
        </div>


        <x-slot:actions>
            <x-button label="Save" wire:click="save" class="btn-primary" />
            <x-button label="Cancel" wire:click="closeModal" />
        </x-slot:actions>
    </x-modal>
</div>