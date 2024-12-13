<?php

use App\Models\Product;
use App\Models\AppGroup;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
    public string $unit = '-';
    #[Validate('required|numeric|gt:0')]
    public $price = 0.00;
    #[Validate('required|numeric|gt:0')]
    public $turnover = 0.00;
    #[Validate('required|numeric|gte:price')]
    public $express_price = 0.00;
    #[Validate('required|numeric|lte:turnover')]
    public $express_turnover = 0.00;
    public string $description = '';
    #[Validate('required')]
    public string $type = '';
    #[Validate('required')]
    public int $group_id = 0;


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
        if (auth()->user()->role == 'user') {
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
            $this->price    = $this->myProduct->price;
            $this->turnover = $this->myProduct->turnover;
            $this->express_price    = $this->myProduct->express_price;
            $this->express_turnover = $this->myProduct->express_turnover;
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
        $this->myProduct->turnover = $this->turnover;
        $this->myProduct->express_price    = $this->express_price;
        $this->myProduct->express_turnover = $this->express_turnover;
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
            ['key' => 'name', 'label' => __('Name'), 'class' => 'w-48'],
            ['key' => 'unit', 'label' => __('Unit')],
            ['key' => 'price', 'label' => __('Price')],
            ['key' => 'turnover', 'label' => __('Turnover')],
            ['key' => 'express_price', 'label' => 'Exp Prc'],
            ['key' => 'express_turnover', 'label' => 'Exp TO'],
            ['key' => 'type', 'label' => __('Type')],

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
            'types'  => DB::table('types')->where('category','Laundry')->get(),
        ];
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Product')}}" separator progress-indicator>
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
        <x-table :headers="$headers" :rows="$allData" :sort-by="$sortBy" with-pagination show-empty-text>
            @scope('actions', $user)
            <div class="w-24 flex justify-end">
                <x-button icon="o-pencil-square" wire:click="selectItem({{ $user['id'] }},'edit')"
                    class="btn-ghost btn-xs text-blue-500" tooltip="{{__('Edit')}}" />
                <x-button icon="o-trash" wire:click="selectItem({{ $user['id'] }},'delete')"
                    wire:confirm="Are you sure?" spinner class="btn-ghost btn-xs text-red-500"
                    tooltip="{{__('Delete')}}" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- New/Edit user modal -->
    <x-modal wire:model="myModal" separator persistent>
        <div>
            <x-input label="{{__('Name')}}" wire:model='uname' clearable
                hint="{{__('The receipt displays a maximum of the first 14 digits of the name')}}" />
            <x-input label="{{__('Unit')}}" wire:model='unit' clearable />
            <x-input label="{{__('Price')}}" wire:model='price' clearable />
            <x-input label="{{__('Turnover')}}" wire:model='turnover' clearable suffix="{{__('day(s)')}}" />
            <x-input label="{{__('Express Price')}}" wire:model='express_price' clearable />
            <x-input label="{{__('Express Turnover')}}" wire:model='express_turnover' clearable
                suffix="{{__('day(s)')}}" />
            <x-input label="{{__('Description')}}" wire:model='description' clearable />
            <x-select label="{{__('Type')}}" wire:model="type" :options="$types" option-value="name" option-label="name"
                placeholder="{{__('Select one type')}}" />
            <x-select label="{{__('Group Name')}}" wire:model="group_id" :options="$groups"
                placeholder="{{__('Select one group')}}" />
        </div>


        <x-slot:actions>
            <x-button label="{{__('Save')}}" wire:click="save" class="btn-primary" />
            <x-button label="{{__('Cancel')}}" wire:click="closeModal" />

        </x-slot:actions>
    </x-modal>
</div>