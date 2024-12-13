<?php

use App\Models\Customer;
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

    public string $uname = '';
    public string $code = '';
    public string $email = '';
    public string $tel = '';
    public string $address = '';
    public string $remark = '';

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
                $this->error(__("This data is used in work order, can't be deleted."), position: 'toast-top');
                return;
            }
            $data = DB::table('customers')->where('id', [$id])->update([
                'is_active' => 0,
            ]);
            if($data<0){
                $this->error(__("Data not deleted."), position: 'toast-top');
            }else{
                $this->success(__("Data deleted."), position: 'toast-top');
            }
            $this->reset();
            $this->resetPage();
        }
        
    }
    //save 
    public function save()
    {
        //add email validation
        $validatedData = $this->validate([
            'uname' => 'required',
            'email' => 'email|unique:customers,email,' . $this->myCustomer->id,
            'tel' => 'required|unique:customers,tel,' . $this->myCustomer->id,
        ]);
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
        $this->success(__("Data saved."), position: 'toast-top');
        $this->reset();
        $this->resetPage();
        $this->myModal = false;
    }


    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => __('Name'), 'class' => 'w-36'],
            ['key' => 'tel', 'label' => __('Tel')],
            ['key' => 'email', 'label' => __('Email') ],
            ['key' => 'address', 'label' => __('Address') ],
            ['key' => 'remark', 'label' => __('Remark') ],

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
    <x-header title="{{__('Customer')}}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="{{__('name, tel, email...')}}" wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="{{__('New')}}" class="btn-primary" wire:click="selectItem(0,'new')" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$Customers" :sort-by="$sortBy" with-pagination show-empty-text>
            @scope('actions', $Customer)
            <div class="w-48 flex justify-end">
                {{--
                <x-button icon="o-credit-card" wire:click="selectItem({{ $Customer['id'] }},'member')" spinner
                    class="btn-ghost btn-xs text-yellow-500" tooltip="{{__('Member Card')}}" /> --}}
                <x-button icon="o-pencil-square" wire:click="selectItem({{ $Customer['id'] }},'edit')"
                    class="btn-ghost btn-xs text-blue-500" tooltip="{{__('Edit')}}" />
                <x-button icon="o-trash" wire:click="selectItem({{ $Customer['id'] }},'delete')"
                    wire:confirm="{{__('Are you sure?')}}" spinner class="btn-ghost btn-xs text-red-500"
                    tooltip="{{__('Delete')}}" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- New/Edit Customer modal -->
    <x-modal wire:model="myModal" separator persistent>
        <div>
            <x-input label="{{__('Name')}}" wire:model='uname' clearable autocomplete="off" />
            <x-input label="{{__('Tel')}}" wire:model='tel' />
            <x-input label="{{__('Email')}}" wire:model='email' type="email" />
            <x-input label="{{__('Address')}}" wire:model='address' />
            <x-input label="{{__('Remark')}}" wire:model='remark' />
        </div>


        <x-slot:actions>
            <x-button label="{{__('Save')}}" wire:click="save" class="btn-primary" />
            <x-button label="{{__('Cancel')}}" wire:click="closeModal" />
        </x-slot:actions>
    </x-modal>
</div>