<?php

use App\Models\Type;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';

    public bool $myModal = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public Type $myType; //new user

    #[Validate('required')]
    public string $uname = '';
    #[Validate('required|min:4')]
    public string $category = '';

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
        if (Auth::user()->role != 'admin') {
            $this->error(__("This action is unauthorized."), position: 'toast-top');
            return;
        }

        $this->selectedItemID = $id;
        $this->action = $action;

        if ($action == 'new') {
            $this->myType = new Type();
            $this->myModal = true;
        } elseif ($action == 'edit') {
            $this->myType = Type::find($id);
            $this->uname = $this->myType->name;
            $this->category = $this->myType->category;
            $this->myModal = true;
        } elseif ($action == 'delete') {
            Type::destroy($id);
            $this->success(__("Data deleted."), position: 'toast-top');
            $this->reset();
            $this->resetPage();
        }
    }
    //save 
    public function save()
    {

        $validatedData = $this->validate();

        $this->myType->name = $this->uname;
        $this->myType->category = $this->category;
        $this->myType->save();
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
            ['key' => 'name', 'label' => __('Name'), 'class' => 'w-64'],
            ['key' => 'category', 'label' => __('Category')],
        ];
    }

    // get all data from table
    public function allData(): LengthAwarePaginator
    {
        return Type::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }


    public function with(): array
    {

        return [
            'allData' => $this->allData(),
            'headers' => $this->headers(),
            'categories' => Category::all(),
        ];
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Type')}}" separator progress-indicator>
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
                    wire:confirm="{{__('Are you sure?')}}" spinner class="btn-ghost btn-xs text-red-500"
                    tooltip="{{__('Delete')}}" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- New/Edit user modal -->
    <x-modal wire:model="myModal" separator persistent>
        <div>
            <x-input label="{{__('Name')}}" wire:model='uname' clearable />
            <x-select label="{{__('Category')}}" wire:model="category" :options="$categories" option-value="name"
                option-label="name" placeholder="{{__('Select one category')}}" />
        </div>


        <x-slot:actions>
            <x-button label="{{__('Save')}}" wire:click="save" class="btn-primary" />
            <x-button label="{{__('Cancel')}}" wire:click="closeModal" />

        </x-slot:actions>
    </x-modal>
</div>