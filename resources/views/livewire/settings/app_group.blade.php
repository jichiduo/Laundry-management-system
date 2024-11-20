<?php

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

    public AppGroup $myappGroup; //new user

    #[Validate('required')]
    public string $uname = '';
    #[Validate('required')]
    public string $currency = '';
    public string $tax_rate = '';
    public string $address = '';
    public string $description = '';


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
        if (auth()->user()->category != 'admin') {
            $this->error("This action is unauthorized.", position: 'toast-top');
            return;
        }
        
        $this->selectedItemID = $id;
        $this->action = $action;

        if ($action == 'new') {
            $this->myappGroup = new AppGroup();
            $this->myModal = true;
        } elseif ($action == 'edit') {
            $this->myappGroup = AppGroup::find($id);

            $this->uname = $this->myappGroup->name;
            $this->currency = $this->myappGroup->currency;
            $this->tax_rate = $this->myappGroup->tax_rate;
            $this->address = $this->myappGroup->address;
            $this->description = $this->myappGroup->description;
            $this->myModal = true;
        } elseif ($action == 'delete'){
            $rc=0;
            $sql = "select count(*) as cnt from work_orders where group_id = ? LIMIT 1";
            $cnt = DB::select($sql, $id);
            foreach ($cnt as $c) {
                $rc = $c->cnt;
                break;
            }
            if($rc > 0){
                $this->error("This data is used in work order, can't be deleted.", position: 'toast-top');
                return;
            }
            AppGroup::destroy($id);
            $this->success("Data deleted.", position: 'toast-top');
            $this->reset();
            $this->resetPage();
        }
    }
    //save 
    public function save()
    {

        $validatedData = $this->validate();

        $this->myappGroup->name = $this->uname;
        $this->myappGroup->currency = $this->currency;
        $this->myappGroup->tax_rate = $this->tax_rate;
        $this->myappGroup->address = $this->address;
        $this->myappGroup->description = $this->description;
        $this->myappGroup->save();
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
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-24'],
            ['key' => 'currency', 'label' => 'Currency'],
            ['key' => 'tax_rate', 'label' => 'Tax Rate'],
            ['key' => 'address', 'label' => 'Address'],
            ['key' => 'description', 'label' => 'Description'],

        ];
    }

    // get all data from table
    public function allData(): LengthAwarePaginator
    {
         return AppGroup::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
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
    <x-header title="App Group" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="New" class="btn-primary" wire:click="selectItem(0,'new')" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$allData" :sort-by="$sortBy" with-pagination show-empty-text>
            @scope('actions', $user)
            <div class="w-48 flex justify-end">
                <x-button icon="o-pencil-square" wire:click="selectItem({{ $user['id'] }},'edit')"
                    class="btn-ghost btn-xs text-blue-500" tooltip="Edit" />
                <x-button icon="o-trash" wire:click="selectItem({{ $user['id'] }},'delete')"
                    wire:confirm="Are you sure?" spinner class="btn-ghost btn-xs text-red-500" tooltip="Delete" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- New/Edit user modal -->
    <x-modal wire:model="myModal" separator persistent>
        <div>
            <x-input label="Name" wire:model='uname' clearable />
            <x-input label="Currency" wire:model='currency' clearable />
            <x-input label="Tax Rate" wire:model='tax_rate' clearable />
            <x-input label="Address" wire:model='address' clearable />
            <x-input label="Description" wire:model='description' clearable />
        </div>


        <x-slot:actions>
            <x-button label="Save" wire:click="save" class="btn-primary" />
            <x-button label="Cancel" wire:click="closeModal" />

        </x-slot:actions>
    </x-modal>
</div>