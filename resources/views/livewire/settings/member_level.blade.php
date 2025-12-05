<?php

use App\Models\MemberLevel;
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

    public MemberLevel $myMbrLvl; //new member level

    #[Validate('required')]
    public string $uname = '';
    
    public string $discount = '';
    public string $topup_amount = '';
    public string $effective_days = '';
    public string $remark = '';
    public int $i = 0;

    public $action = "new";
    //check valid amount
    public function calc(): bool
    {
        $this->resetErrorBag();
        $i=0;
        //check if discount is valid and greater than 0
        if ($this->discount) {
            if (is_numeric($this->discount) && floatval($this->discount) > 0) {
                $i++;
            } else {
                $this->addError('discount', __('The amount should not less than 0.'));
            }
        } else {
            $this->addError('discount', __('The amount is required.'));
        }
        //check topup_amount
        if ($this->topup_amount) {
            if (is_numeric($this->topup_amount) && floatval($this->topup_amount) > 0) {
                $i++;
            } else {
                $this->addError('topup_amount', __('The amount should not less than 0.'));
            }
        } else {
            $this->addError('topup_amount', __('The amount is required.'));
        }
        //check effective_days
        if ($this->effective_days) {
            if (is_numeric($this->effective_days) && floatval($this->effective_days) > 0) {
                $i++;
            } else {
                $this->addError('effective_days', __('The amount should not less than 0.'));
            }
        } else {
            $this->addError('effective_days', __('The amount should not less than 0.'));
        }
        if ($i == 3) {
            return true;
        }
        return false;
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
        if (Auth::user()->role != 'admin') {
            $this->error(__("This action is unauthorized."), position: 'toast-top');
            return;
        }

        $this->selectedItemID = $id;
        $this->action = $action;

        if ($action == 'new') {
            $this->myMbrLvl = new MemberLevel();
            $this->myModal = true;
        } elseif ($action == 'edit') {
            $this->myMbrLvl = MemberLevel::find($id);
            $this->uname = $this->myMbrLvl->name;
            $this->discount = $this->myMbrLvl->discount;
            $this->topup_amount = $this->myMbrLvl->topup_amount;
            $this->effective_days = $this->myMbrLvl->effective_days;
            //check if remark is null, if null set to empty string
            $this->remark = $this->myMbrLvl->remark ?? '';
            $this->myModal = true;
        } elseif ($action == 'delete') {
            MemberLevel::destroy($id);
            $this->success(__("Data deleted."), position: 'toast-top');
            $this->reset();
            $this->resetPage();
        }
    }
    //save 
    public function save()
    {

        $validatedData = $this->validate();
        //check
        if (!$this->calc()) {
            return false;
        }

        $this->myMbrLvl->name = $this->uname;
        $this->myMbrLvl->discount = $this->discount;
        $this->myMbrLvl->topup_amount = $this->topup_amount;
        $this->myMbrLvl->effective_days = $this->effective_days;
        $this->myMbrLvl->remark = $this->remark;
        $this->myMbrLvl->save();
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
            ['key' => 'discount', 'label' => __('Discount')],
            ['key' => 'topup_amount', 'label' => __('Topup Amount')],
            ['key' => 'effective_days', 'label' => __('Effective Days')],
            ['key' => 'remark', 'label' => __('Remark')],
        ];
    }

    // get all data from table
    public function allData(): LengthAwarePaginator
    {
        return MemberLevel::query()
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
    <x-header title="{{__('Member Level')}}" separator progress-indicator>
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
            <x-input label="{{__('Discount')}}" wire:model="discount" clearable />
            <x-input label="{{__('Topup Amount')}}" wire:model="topup_amount" clearable />
            <x-input label="{{__('Effective Days')}}" wire:model="effective_days" clearable />
            <x-input label="{{__('Remark')}}" wire:model="remark" clearable />
        </div>


        <x-slot:actions>
            <x-button label="{{__('Save')}}" wire:click="save" class="btn-primary" />
            <x-button label="{{__('Cancel')}}" wire:click="closeModal" />

        </x-slot:actions>
    </x-modal>
</div>