<?php

use App\Models\Currency;
use App\Models\ExchangeRate;
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

    public ExchangeRate $myExchangeRate; 

    #[Validate('required')]
    public string $from_currency = '';
    #[Validate('required')]
    public string $to_currency = '';
    public $rate = 0;

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
        if (auth()->user()->role == 'user') {
            $this->error("This action is unauthorized.", position: 'toast-top');
            return;
        }
        
        $this->selectedItemID = $id;
        $this->action = $action;

        if ($action == 'new') {
            $this->myExchangeRate = new ExchangeRate();
            $this->myModal = true;
        } elseif ($action == 'edit') {
            $this->myExchangeRate = ExchangeRate::find($id);
            $this->from_currency = $this->myExchangeRate->from_currency;
            $this->to_currency = $this->myExchangeRate->to_currency;
            $this->rate = $this->myExchangeRate->rate;
            $this->myModal = true;
        } elseif ($action == 'delete'){
                ExchangeRate::destroy($id);
                $this->success("Data deleted.", position: 'toast-top');
                $this->reset();
                $this->resetPage();
        }
    }
    //save 
    public function save()
    {

        $validatedData = $this->validate();

        $this->myExchangeRate->from_currency = $this->from_currency;
        $this->myExchangeRate->to_currency = $this->to_currency;
        $this->myExchangeRate->rate = $this->rate;
        $this->myExchangeRate->save();
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
            ['key' => 'from_currency', 'label' => 'From Currency', 'class' => 'w-64'],
            ['key' => 'to_currency', 'label' => 'To Currency', 'class' => 'w-64'],
            ['key' => 'rate', 'label' => 'Rate'],
        ];
    }

    // get all data from table
    public function allData(): LengthAwarePaginator
    {
         return ExchangeRate::query()
            ->when($this->search, fn(Builder $q) => $q->where('from_currency', 'like', "%$this->search%")->orWhere('to_currency', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10); 

    }


    public function with(): array
    {
 
        return [
            'allData' => $this->allData(),
            'headers' => $this->headers(),
            'currencies' => Currency::all(),
        ];
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="Exchange Rate" separator progress-indicator>
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
            <div class="w-24 flex justify-end">
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
            <x-select label="From Currency" wire:model="from_currency" :options="$currencies" option-value="name"
                option-label="name" placeholder="Select Currency" />
            <x-select label="To Currency" wire:model="to_currency" :options="$currencies" option-value="name"
                option-label="name" placeholder="Select Currency" />
            <x-input label="Rate" wire:model='rate' clearable />
        </div>


        <x-slot:actions>
            <x-button label="Save" wire:click="save" class="btn-primary" />
            <x-button label="Cancel" wire:click="closeModal" />

        </x-slot:actions>
    </x-modal>
</div>