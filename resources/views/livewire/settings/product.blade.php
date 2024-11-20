<?php

use App\Models\Product;
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

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public Product $myProduct; //new user

    #[Validate('required')]
    public string $uname = '';
    public string $unit = '';
    public $price = 0.00;
    public string $description = '';
    public string $type = '';
    #[Validate('required')]
    public int $group_id = 0;


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
            $this->myProduct = new Product();
            $this->myModal = true;
        } elseif ($action == 'edit') {
            $this->myProduct = Product::find($id);
            $this->uname = $this->myProduct->name;
            $this->unit = $this->myProduct->unit;
            $this->price = $this->myProduct->price;
            $this->description = $this->myProduct->description;
            $this->type = $this->myProduct->type;
            $this->group_id = $this->myProduct->group_id;
            $this->myModal = true;
        } elseif ($action == 'delete'){
            $this->myProduct = Product::find($id);
            $product_name = $this->myProduct->name;
            $rc=0;
            $sql = "select count(*) as cnt from work_order_items where name = ? LIMIT 1";
            $cnt = DB::select($sql, [$product_name]);
            foreach ($cnt as $c) {
                $rc = $c->cnt;
                break;
            }
            if($rc > 0){
                $this->error("This data is used in work order, can't be deleted.", position: 'toast-top');
                return;
            }
            Product::destroy($id);
            $this->success("Data deleted.", position: 'toast-top');
            $this->reset();
            $this->resetPage();
        }
    }
    //save 
    public function save()
    {

        $validatedData = $this->validate();

        $this->myProduct->name = $this->uname;
        $this->myProduct->unit = $this->unit;
        $this->myProduct->price = $this->price;
        $this->myProduct->description = $this->description;
        $this->myProduct->type = $this->type;
        $this->myProduct->group_id = $this->group_id;
        $this->myProduct->save();
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
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
            ['key' => 'unit', 'label' => 'Unit'],
            ['key' => 'price', 'label' => 'Price'],
            ['key' => 'description', 'label' => 'Desc'],
            ['key' => 'type', 'label' => 'Type'],

        ];
    }

    // get all data from table
    public function allData(): LengthAwarePaginator
    {
         return Product::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10); 

    }


    public function with(): array
    {
 
        return [
            'allData' => $this->allData(),
            'headers' => $this->headers(),
            'groups' => AppGroup::all(),
        ];
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="Product" separator progress-indicator>
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
            <x-input label="Unit" wire:model='unit' clearable />
            <x-input label="Price" wire:model='price' clearable />
            <x-input label="Description" wire:model='description' clearable />
            <x-input label="Type" wire:model='type' clearable />
            <x-select label="Group Name" wire:model="group_id" :options="$groups" placeholder="Select one group" />
        </div>


        <x-slot:actions>
            <x-button label="Save" wire:click="save" class="btn-primary" />
            <x-button label="Cancel" wire:click="closeModal" />

        </x-slot:actions>
    </x-modal>
</div>