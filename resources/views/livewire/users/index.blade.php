<?php

use App\Models\User;
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

    public User $myuser; //new user

    #[Validate('required')]
    public string $uname = '';
    #[Validate('required|min:4')]
    public string $password = '';
    #[Validate('required|email|unique:users')]
    public string $email = '';
    #[Validate('required')]
    public string $category = '';
    #[Validate('required')]
    public string $group_id = '';

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
        if (auth()->user()->category == 'user') {
            $this->error("This action is unauthorized.", position: 'toast-top');
            return;
        }
        
        $this->selectedItemID = $id;
        $this->action = $action;

        if ($action == 'new') {
            $this->myuser = new User();
            $this->myModal = true;
        } elseif ($action == 'edit') {
            $this->myuser = User::find($id);
            $this->uname = $this->myuser->name;
            $this->password = $this->myuser->password;
            $this->email = $this->myuser->email;
            $this->category = $this->myuser->category;
            $this->group_id = $this->myuser->group_id;
            $this->myModal = true;
        } elseif ($action == 'delete'){
            if ($id == auth()->user()->id) {
                $this->warning("You can't delete yourself.", position: 'toast-top');
            } else {
                User::destroy($id);
                $this->success("User deleted.", position: 'toast-top');
                $this->resetPage();
            }
        }
    }
    //save 
    public function save()
    {

        $validatedData = $this->validate();
        if ($this->action == 'new') {
            $this->myuser->password = bcrypt($this->password);            
        }
        $this->myuser->name = $this->uname;
        $this->myuser->email = $this->email;
        $this->myuser->category = $this->category;
        $this->myuser->group_id = $this->group_id;
        $this->myuser->save();
        $this->success("Info saved.", position: 'toast-top');
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
            ['key' => 'category', 'label' => 'Category', 'class' => 'w-24'],
            ['key' => 'email', 'label' => 'E-mail', 'class' => 'w-64'],
            ['key' => 'group_id', 'label' => 'Group ID'],

        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
    public function users(): LengthAwarePaginator
    {
         return User::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10); // No more `->get()`

    }

    public function groups(): Collection
    {
        return AppGroup::all();
    }

    public function with(): array
    {
        $categories = [
            [
                'id' => 'admin',
                'name' => 'admin'
            ],
            [
                'id' => 'manager',
                'name' => 'manager'
            ],
            [
                'id' => 'user',
                'name' => 'user'
            ]
        ];
        return [
            'categories' => $categories,
            'users' => $this->users(),
            'headers' => $this->headers(),
            'groups' => $this->groups(),
        ];
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="Users" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="New User" class="btn-primary" wire:click="selectItem(0,'new')" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination :per-page-values="[10, 25, 50]"
            show-empty-text>
            @scope('actions', $user)
            <div class="w-48 flex justify-end gap-2">
                <x-button icon="o-pencil-square" wire:click="selectItem({{ $user['id'] }},'edit')"
                    class="btn-ghost btn-sm text-blue-500" />
                <x-button icon="o-trash" wire:click="selectItem({{ $user['id'] }},'delete')"
                    wire:confirm="Are you sure?" spinner class="btn-ghost btn-sm text-red-500" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- New/Edit user modal -->
    <x-modal wire:model="myModal" title="User Info" separator persistent>
        <div>
            <x-input label="Name" wire:model='uname' clearable />
            @if($action =='new')
            <x-input label="Password" wire:model='password' type="password" clearable />
            @endif
            <x-input label="Email" wire:model='email' type="email" />
            <x-select label="Category" wire:model="category" :options="$categories" placeholder="Select one category" />
            <x-select label="Group Name" wire:model="group_id" :options="$groups" placeholder="Select one group" />
        </div>


        <x-slot:actions>
            <x-button label="Cancel" wire:click="closeModal" />
            <x-button label="Confirm" wire:click="save" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>