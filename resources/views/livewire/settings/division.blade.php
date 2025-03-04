<?php

use App\Models\Division;
use App\Models\AppGroup;
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

    public Division $myDivision; //new user

    #[Validate('required')]
    public string $uname = '';
    public string $address = '';
    public string $tel = '';
    public string $remark = '';
    public int $group_id = 0;
    public string $printer_com_port = 'COM3';

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
            $this->error("This action is unauthorized.", position: 'toast-top');
            return;
        }

        $this->selectedItemID = $id;
        $this->action = $action;

        if ($action == 'new') {
            $this->myDivision = new Division();
            $this->myModal = true;
        } elseif ($action == 'edit') {
            $this->myDivision = Division::find($id);
            $this->uname = $this->myDivision->name;
            $this->address = $this->myDivision->address;
            $this->tel = $this->myDivision->tel;
            $this->remark = $this->myDivision->remark;
            $this->group_id = $this->myDivision->group_id;
            $this->group_name = $this->myDivision->group_name;
            $this->printer_com_port = $this->myDivision->printer_com_port;

            $this->myModal = true;
        } elseif ($action == 'delete') {

            $rc = 0;
            $sql = "select count(*) as cnt from work_orders where division_id = ? LIMIT 1";
            $cnt = DB::select($sql, [$id]);
            foreach ($cnt as $c) {
                $rc = $c->cnt;
                break;
            }
            if ($rc > 0) {
                $this->error("This data is used in work order, can't be deleted.", position: 'toast-top');
                return;
            }

            Division::destroy($id);
            $this->success("Data deleted.", position: 'toast-top');
            $this->reset();
            $this->resetPage();
        }
    }
    //save 
    public function save()
    {

        $validatedData = $this->validate();

        $this->myDivision->name = $this->uname;
        $this->myDivision->address = $this->address;
        $this->myDivision->tel = $this->tel;
        $this->myDivision->remark = $this->remark;
        $this->myDivision->group_id = $this->group_id;
        //get group_name from database
        $this->myDivision->group_name = AppGroup::find($this->group_id)->name;
        $this->myDivision->printer_com_port = $this->printer_com_port;
        $this->myDivision->save();
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
            ['key' => 'tel', 'label' => __('Tel'), 'class' => 'w-24'],
            ['key' => 'address', 'label' => __('Address')],
            ['key' => 'remark', 'label' => __('Remark')],
            ['key' => 'printer_com_port', 'label' => __('Com Port')],
            ['key' => 'group_name', 'label' => __('Group')],

        ];
    }

    // get all data from table
    public function allData(): LengthAwarePaginator
    {
        return Division::query()
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
    <x-header title="{{__('Division')}}" separator progress-indicator>
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
            <x-input label="{{__('Tel')}}" wire:model='tel' clearable />
            <x-input label="{{__('Address')}}" wire:model='address' clearable />
            <x-input label="{{__('Remark')}}" wire:model='remark' clearable />
            <x-input label="{{__('Com Port')}}" wire:model='printer_com_port' clearable />
            <x-select label="{{__('Group')}}" wire:model="group_id" :options="$groups"
                placeholder="{{__('Select one group')}}" />
        </div>


        <x-slot:actions>
            <x-button label="{{__('Save')}}" wire:click="save" class="btn-primary" />
            <x-button label="{{__('Cancel')}}" wire:click="closeModal" />

        </x-slot:actions>
    </x-modal>
</div>