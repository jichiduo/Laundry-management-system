<?php

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\AppGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Validate;
use Carbon\Carbon;
use App\Http\Controllers\WorkOrderController;


new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';

    public bool $myModal = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public Customer $myCustomer; //new Customer


    public string $uname = '';
    public string $code = '';
    public string $email = '';
    public string $tel = '';
    public string $address = '';
    public string $remark = '';
    public $balance = 0;
    public $amount  = 0;


    public $action = "new";


    //select Item
    public function selectItem($id, $action)
    {

        $this->selectedItemID = $id;
        $this->action = $action;

        if ($action == 'reversal') {
            if (Auth::user()->role == 'user') {
                $this->error(__("You do not have sufficient privileges to do this."), position: 'toast-top');
                return;
            }
            //get amount from the select transaction
            $trans = Transaction::findOrFail($id);
            if ($trans->remark != 'Topup') {
                $this->error(__("This transaction is not a topup transaction."), position: 'toast-top');
                return;
            }
            //check if the reversal amount more than current balance
            if ($trans->amount > $this->myCustomer->balance) {
                $this->error(__("The reversal amount is more than the current balance."), position: 'toast-top');
                return;
            }
            //add one transaction record
            $woc = new WorkOrderController();
            $trans_no = $woc->get_trans_no(Auth::user()->division_id);
            $myTrans = new Transaction();
            $myTrans->trans_no = $trans_no;
            $myTrans->wo_no = $trans->trans_no; //set new trans's wo_no to the original trans_no
            $myTrans->customer_id = $this->myCustomer->id;
            $myTrans->customer_name = $this->myCustomer->name;
            $myTrans->amount = $trans->amount * (-1);
            $myTrans->payment_type = 'Member Card';
            $myTrans->type = 'credit';
            $myTrans->remark = 'Reversal';
            $myTrans->create_by = Auth::user()->id;
            $myTrans->created_at = Carbon::now();
            $myTrans->save();
            //set current trans remark to Reversed
            $trans->wo_no = $trans_no; //set old trans's wo_no to the reversal trans_no
            $trans->remark = 'Reversed';
            $trans->updated_at = Carbon::now();
            $trans->save();

            //update customer
            $this->myCustomer->last_trans_no = $trans_no;
            $this->myCustomer->balance = $this->myCustomer->balance + ($trans->amount * (-1));
            $this->balance = $this->myCustomer->balance;
            $this->myCustomer->update_by = Auth::user()->id;
            $this->myCustomer->updated_at = Carbon::now();
            $this->myCustomer->save();
            //add to applog

            $this->success(__("Reversal successed"), position: 'toast-top');
        }
    }
    //save 
    public function topup()
    {
        //check
        if (!$this->calc()) {
            return false;
        }
        //add one transaction record
        $woc = new WorkOrderController();
        $trans_no = $woc->get_trans_no(Auth::user()->division_id);
        $myTrans = new Transaction();
        $myTrans->trans_no = $trans_no;
        $myTrans->customer_id = $this->myCustomer->id;
        $myTrans->customer_name = $this->myCustomer->name;
        $myTrans->amount = $this->amount;
        $myTrans->payment_type = 'Member Card';
        $myTrans->type = 'credit';
        $myTrans->remark = 'Topup';
        $myTrans->create_by = Auth::user()->id;
        $myTrans->created_at = Carbon::now();
        $myTrans->save();
        //update customer
        $this->myCustomer->last_trans_no = $trans_no;
        $this->myCustomer->balance = $this->myCustomer->balance + $this->amount;
        $this->balance = $this->myCustomer->balance;
        $this->myCustomer->update_by = Auth::user()->id;
        $this->myCustomer->updated_at = Carbon::now();
        $this->myCustomer->save();
        $this->success(__("Topup successed"), position: 'toast-top');
        $this->amount = 0;
    }

    public function calc(): bool
    {
        $this->resetErrorBag();
        if ($this->amount) {
            if (($this->amount) > 0) {
                return true;
            } else {
                $this->addError('amount', __('The amount should not less than 0.'));
            }
        } else {
            $this->addError('amount', __('The amount is required.'));
        }
        return false;
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'trans_no', 'label' => __('Transaction Number')],
            ['key' => 'created_at', 'label' => __('Date')],
            ['key' => 'remark', 'label' => __('Type')],
            ['key' => 'amount', 'label' => __('Amount')],

        ];
    }

    public function Transactions(): LengthAwarePaginator
    {
        return Transaction::query()
            ->where('customer_id', $this->myCustomer->id)
            ->where('payment_type', 'Member Card')
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }


    public function with(): array
    {
        return [
            'Transactions' => $this->Transactions(),
            'headers' => $this->headers(),
        ];
    }

    public function mount($id): void
    {
        $this->myCustomer = Customer::findOrFail($id);
        $this->uname = $this->myCustomer->name;
        $this->email = $this->myCustomer->email;
        $this->tel = $this->myCustomer->tel;
        $this->address = $this->myCustomer->address;
        $this->remark = $this->myCustomer->remark;
        $this->balance = $this->myCustomer->balance;
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Topup')}}" separator progress-indicator />
    <div class="mt-2 mb-4">
        <x-card title="{{__('Customer')}}">
            <div class="grid grid-cols-3 gap-2  mt-2">
                <x-input label="{{__('Name')}}" wire:model="uname" disabled />
                <x-input label="{{__('Tel')}}" wire:model="tel" disabled />
                <x-input label="{{__('Email')}}" wire:model="email" disabled />
                <x-input label="{{__('Balance')}}" wire:model="balance" disabled />
                <x-input label="{{__('Address')}}" wire:model="address" disabled />
                <x-input label="{{__('Remark')}}" wire:model="remark" disabled />
            </div>
            <div class="divider"></div>
            <div class="grid grid-cols-3  gap-2">
                <x-input label="{{__('Topup')}} {{__('Amount')}}" wire:model="amount">
                    <x-slot:append>
                        <x-button wire:click="topup" spinner="topup" label="{{__('Topup')}}" icon="o-currency-dollar" class="btn-primary rounded-s-none" />
                    </x-slot:append>
                </x-input>
            </div>
        </x-card>
    </div>
    <!-- TABLE  -->
    <x-card>

        <x-table :headers="$headers" :rows="$Transactions" :sort-by="$sortBy" with-pagination show-empty-text>
            @scope('actions', $Transactions)
            <div class="w-24 flex justify-end">
                <x-button icon="o-arrow-uturn-left" wire:click="selectItem({{ $Transactions['id'] }},'reversal')"
                    wire:confirm="{{__('Are you sure?')}}" spinner class="btn-ghost btn-xs text-red-500"
                    tooltip="{{__('Reversal')}}" />
            </div>
            @endscope
        </x-table>
    </x-card>

</div>