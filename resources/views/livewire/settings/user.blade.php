<?php

use App\Models\User;
use App\Models\AppGroup;
use App\Models\Division;
use App\Models\Role;
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

    public string $uname = '';
    public string $password = '';
    public string $email = '';
    public string $role = '';
    public string $division_id = '';
    public string $division_name = '';
    public string $group_id = '';
    public string $group_name = '';
    public $mydivisions = [];


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
        if (auth()->user()->role != 'admin') {
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
            $this->role = $this->myuser->role;
            $this->division_id = $this->myuser->division_id;
            $this->group_id = $this->myuser->group_id;
            $this->myModal = true;
        } elseif ($action == 'reset') {
            //reset password to a random password and copy new password to clipboard
            $newPassword = Str::lower(rand(1000,9999));
            $this->myuser = User::find($id);
            $this->myuser->password = bcrypt($newPassword);
            $this->myuser->save();
            $this->success("Password reset to " . $newPassword, position: 'toast-top');

        } elseif ($action == 'delete'){
            if ($id == auth()->user()->id) {
                $this->warning("You can't delete yourself.", position: 'toast-top');
            } else {
                $rc=0;
                $sql = "select count(*) as cnt from work_orders where user_id = ? LIMIT 1";
                $cnt = DB::select($sql, [$id]);
                foreach ($cnt as $c) {
                    $rc = $c->cnt;
                    break;
                }
                if($rc > 0){
                    $this->error("This data is used in work order, can't be deleted.", position: 'toast-top');
                    return;
                }

                User::destroy($id);
                $this->success("Data deleted.", position: 'toast-top');
                $this->resetPage();
            }
        }
    }
    //save 
    public function save()
    {
        //add email validation
        $validatedData = $this->validate([
            'uname' => 'required',
            'password' => 'required|min:4',
            'email' => 'required|email|unique:users,email,' . $this->myuser->id,
            'role' => 'required',
            'division_id' => 'required',
            'group_id' => 'required',
        ]);
        if ($this->action == 'new') {
            $this->myuser->password = bcrypt($this->password);            
        }
        $this->myuser->name = $this->uname;
        $this->myuser->email = $this->email;
        $this->myuser->role = $this->role;
        $this->myuser->division_id = $this->division_id;
        $this->myuser->group_id = $this->group_id;
        //get division name from database
        $this->myuser->division_name = Division::where('id', $this->division_id)->value('name');
        //get group name from database
        $this->myuser->group_name = AppGroup::where('id', $this->group_id)->value('name');
        $this->myuser->save();
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
            ['key' => 'role', 'label' => 'Role', 'class' => 'w-24'],
            ['key' => 'email', 'label' => 'E-mail', 'class' => 'w-64'],
            ['key' => 'division_name', 'label' => 'Division'],
            ['key' => 'group_name', 'label' => 'Group'],

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


    public function with(): array
    {
        
        return [
            'roles' => Role::all(),
            'users' => $this->users(),
            'headers' => $this->headers(),
            'divisions' => $this->mydivisions,
            'groups' => AppGroup::all(),
        ];
    }

    public function getDivisions(): void {
        //get division by group_id
        $this->mydivisions = Division::where('group_id', $this->group_id)->get();
    }

};
?>

<div>
    <!-- HEADER -->
    <x-header title="User" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="New" class="btn-primary" wire:click="selectItem(0,'new')" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination show-empty-text>
            @scope('actions', $user)
            <div class="w-24 flex justify-end">
                <x-button icon="o-pencil-square" wire:click="selectItem({{ $user['id'] }},'edit')"
                    class="btn-ghost btn-xs text-blue-500" tooltip="Edit" />
                <x-button icon="o-arrow-path" wire:click="selectItem({{ $user['id'] }},'reset')"
                    wire:confirm="Are you sure?" spinner class="btn-ghost btn-xs text-yellow-500"
                    tooltip="Reset Password" />
                <x-button icon="o-trash" wire:click="selectItem({{ $user['id'] }},'delete')"
                    wire:confirm="Are you sure?" spinner class="btn-ghost btn-xs text-red-500" tooltip="Delete" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- New/Edit user modal -->
    <x-modal wire:model="myModal" separator persistent>
        <div>
            <x-input label="Name" wire:model='uname' clearable autocomplete="off" />
            @if($action =='new')
            <x-input label="Password" wire:model='password' type="password" clearable />
            @endif
            <x-input label="Email" wire:model='email' type="email" />
            <x-select label="Role" wire:model="role" :options="$roles" option-value="name" option-label="name"
                placeholder="Select role" />
            <x-select label="Group" wire:model="group_id" wire:change='getDivisions' :options="$groups"
                placeholder="Select group" />
            <x-select label="Divison" wire:model="division_id" :options="$mydivisions" placeholder="Select division" />
        </div>


        <x-slot:actions>
            <x-button label="Save" wire:click="save" class="btn-primary" />
            <x-button label="Cancel" wire:click="closeModal" />
        </x-slot:actions>
    </x-modal>
</div>