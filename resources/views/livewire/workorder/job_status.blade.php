<?php

use App\Models\AppGroup;
use App\Models\AppLog;
use App\Models\JobStatus;
use App\Models\WorkOrderItem;
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

    public JobStatus $myJobStatus; 

    #[Validate('required')]
    public string $wo_no = '';
    #[Validate('required')]
    public string $barcode = '';

    public $action = "new";
    

    //select Item
    public function selectItem($id, $action)
    {
        
        $this->selectedItemID = $id;
        $this->action = $action;

        if ($action == 'delete'){
                JobStatus::destroy($id);
                $this->success("Data deleted.", position: 'toast-top');
                $this->reset();
                $this->resetPage();
        }
    }


    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'wo_no', 'label' => __('WO No'), 'class' => 'w-24'],
            ['key' => 'barcode', 'label' => __('Barcode') ],
            ['key' => 'name', 'label' => __('Name')],
            ['key' => 'quantity', 'label' => __('Quantity')],
        ];
    }

    // get all data from table
    public function allData(): LengthAwarePaginator
    {
         return JobStatus::query()
            ->where('user_id', auth()->user()->id)
            ->when($this->search, fn(Builder $q) => $q->where('barcode', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(50); 

    }


    public function with(): array
    {
 
        return [
            'allData' => $this->allData(),
            'headers' => $this->headers(),
        ];
    }

    public function deleteAll(): void
    {
        //delete all record from job_statues where user_id = auth()->user()->id
        $rc=JobStatus::where('user_id', auth()->user()->id)->delete();
        if($rc>=0){

            $this->success(__("Data deleted."), position: 'toast-top');
        }else{
            $this->warning(__("Something wrong, please try again."), position: 'toast-top');
        }
        $this->reset();
        $this->resetPage();
    }

    public function changeAll(): void
    {
        //change items status to 4pickup where user_id = auth()-user()->id
        $sql = "update work_order_items set status = '4pickup' , updated_at = now() where barcode in (select barcode from job_statuses where user_id = " . auth()->user()->id . ")"; 
        $rc=DB::update($sql);
        if($rc>0){
            //check if all items belongs to one work_order status changed to 4pickup, if yes , update work_order status to 4pickup
            $sql = "select distinct(wo_no) as wo_no from job_statuses where user_id = " . auth()->user()->id;
            $wos = DB::select($sql);
            foreach($wos as $wo){
                //after upate some of the item belongs to one work order status 
                //check if all items belongs to one work_order status changed to 4pickup, if yes , update work_order status to 4pickup
                //if count status > 1 then still some items not finish, do nothing
                //if count status = 1 then all items now is 4pickup, update work_order status to 4pickup
                $sql = "select distinct(status) as status from work_order_items where wo_no = '" . $wo->wo_no . "'";
                $data = DB::select($sql);
                if(count($data)==1){
                    $sql = "update work_orders set status = '4pickup' where wo_no = '" . $wo->wo_no . "'";
                    DB::update($sql);
                    //create a log
                    $log = new AppLog();
                    $log->user_id = auth()->user()->id;
                    $log->user_name = auth()->user()->name;
                    $log->wo_no   = $wo->wo_no;
                    $log->action = "job status";
                    $log->remark = "update work_order status to 4pickup";
                    $log->save();
                    unset($log);
                }
            }
            $sql="delete from job_statuses where user_id = " . auth()->user()->id;
            $rc=DB::delete($sql);
            $this->success(__("Data updated."), position: 'toast-top');
        } elseif ($rc==0) {
            $this->warning(__("No data to update, please add some item first."), position: 'toast-top');
        } else {
            $this->warning(__("Something wrong, please try again."), position: 'toast-top');
        }
        $this->reset();
        $this->resetPage();
    }

    public function findItem(): void {
        if ($this->barcode) {
            $woi = WorkOrderItem::where('barcode', $this->barcode)->first();
            if ($woi) {
                $wo_no    = $woi->wo_no;
                $status   = $woi->status;
                $name     = $woi->name;
                $quantity = $woi->quantity;

                if($status == 'pending'){
                    //insert into job_statuses
                    $js = new JobStatus();
                    $js->wo_no = $wo_no;
                    $js->name = $name;
                    $js->quantity = $quantity;
                    $js->barcode = $this->barcode;
                    $js->user_id = auth()->user()->id;
                    $js->save();
                    $this->success(__("Item added to list."), position: 'toast-top');
                } else {
                    
                    $this->warning(__("Only pending items can be added to list."), position: 'toast-top');
                }
            } else {
                $this->warning(__("Item not found."), position: 'toast-top');
            }
            $this->barcode = '';
        }
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Job Status')}}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="{{__('Search')}}..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <div class="grid grid-cols-2 mt-2 mb-2 gap-2">
            <div>
                <x-input label="{{__('Barcode')}}" wire:model="barcode" wire:keydown.enter='findItem' inline />
            </div>
            <div>
                <x-button icon="o-truck" wire:click="changeAll" spinner="changeAll" class="btn-primary"
                    wire:confirm="{{__('Are you sure?')}}" label="{{__('Change status to 4pickup')}}"
                    tooltip="{{__('Change status to 4pickup')}}" />
                <x-button icon="o-trash" wire:click="deleteAll" spinner="deleteAll" class="btn-error"
                    wire:confirm="{{__('Are you sure?')}}" label="{{__('Remove All')}}"
                    tooltip="{{__('Remove All')}}" />
            </div>
        </div>


        <x-table :headers="$headers" :rows="$allData" :sort-by="$sortBy" with-pagination show-empty-text>
            @scope('actions', $user)
            <div class="w-48 flex justify-end">
                <x-button icon="o-trash" wire:click="selectItem({{ $user['id'] }},'delete')" spinner
                    class="btn-ghost btn-xs text-red-500" tooltip="{{__('Remove')}}" />
            </div>
            @endscope
        </x-table>
    </x-card>

</div>