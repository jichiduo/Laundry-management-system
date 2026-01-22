<?php

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\AppGroup;
use App\Models\MemberLevel;
use App\Models\AppLog;
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
use Illuminate\Support\Facades\Storage;


new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';
    public string $content = '';

    public bool $myModal = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public Customer $myCustomer; //new Customer


    public string $uname = '';
    public string $code = '';
    public string $email = '';
    public string $tel = '';
    public string $address = '';
    public string $remark = '';
    public string $member_level_name = '';
    public string $member_discount = '';
    public string $member_expire_date = '';
    public $balance = 0;
    public $amount  = 0;
    public string $print = '';
    public string $print_action = '';
    public string $trans_no = '';


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
            $logs = new AppLog();
            $logs->action = 'Reversal Topup';
            $logs->trans_no = $trans_no;
            $logs->user_id = Auth::user()->id;
            $logs->user_name = Auth::user()->name;
            $logs->amount = $trans->amount;
            $logs->remark = 'Reversal Member Card Topup for customer ' . $this->myCustomer->name . ', amount ' . $trans->amount . ', original trans_no ' . $trans->trans_no . ', reversal trans_no ' . $trans_no;
            $logs->created_at = Carbon::now();
            $logs->save();
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
        //check if the current customer level is higher than this topup level
        if ($this->myCustomer->member_level_id) {
            $currentLevel = MemberLevel::where('id', $this->myCustomer->member_level_id)->first();
            if ($currentLevel && $currentLevel->topup_amount > $this->amount) {
                //send error message
                $this->addError('amount', __('The topup amount is less than the current member level, please topup more to extend or upgrade user member level'));
                return false;
            }
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
        //check if the topup amount reach any member level
        $memberLevels = MemberLevel::orderBy('topup_amount', 'desc')->get();
        foreach ($memberLevels as $level) {
            if ($this->amount >= $level->topup_amount) {
                //update customer member level info
                $this->myCustomer->member_level_id = $level->id;
                $this->myCustomer->member_level_name = $level->name;
                $this->myCustomer->member_discount = $level->discount;
                //check if current member_expire_date is more than now
                if ($this->myCustomer->member_expire_date && Carbon::parse($this->myCustomer->member_expire_date)->isFuture()) {
                    $this->myCustomer->member_expire_date = Carbon::parse($this->myCustomer->member_expire_date)->addDays($level->effective_days);
                } else {
                    $this->myCustomer->member_expire_date = Carbon::now()->addDays($level->effective_days);
                }
                break; //exit loop after first match
            }
        }
        //update customer
        $this->myCustomer->last_trans_no = $trans_no;
        $this->myCustomer->balance = $this->myCustomer->balance + $this->amount;
        $this->myCustomer->update_by = Auth::user()->id;
        $this->myCustomer->updated_at = Carbon::now();
        $this->myCustomer->save();
        //refresh data
        $this->getCustomer($this->myCustomer->id);
        //add to applog
        $logs = new AppLog();
        $logs->action = 'Member Card Topup';
        $logs->trans_no = $trans_no;
        $logs->user_id = Auth::user()->id;
        $logs->user_name = Auth::user()->name;
        $logs->amount = $this->amount;
        $logs->remark = 'Member Card Topup for customer ' . $this->myCustomer->name . ', amount ' . $this->amount . ', trans_no ' . $trans_no . 'previous expire date ' . $this->member_expire_date . ', new expire date ' . $this->myCustomer->member_expire_date;
        $logs->created_at = Carbon::now();
        $logs->save();
        $this->success(__("Topup successed"), position: 'toast-top');
        $this->amount = 0;
        //create receipt print file
        $woc->getTopupReceipt($trans_no);
        //retun to view
        return redirect()->route('customer_topup', ['id' => $this->myCustomer->id, 'trans_no' => $trans_no]);
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
    //Table headers of memberlevels
    public function memberLevelHeaders(): array
    {
        return [
            ['key' => 'name', 'label' => __('Name'), 'class' => 'w-64'],
            ['key' => 'discount', 'label' => __('Discount')],
            ['key' => 'topup_amount', 'label' => __('Topup Amount')],
            ['key' => 'effective_days', 'label' => __('Effective Days')],
            ['key' => 'remark', 'label' => __('Remark')],
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
            'MemberLevels' => MemberLevel::all(),
            'memberLevelHeaders' => $this->memberLevelHeaders(),
        ];
    }

    public function mount($id, $trans_no = ''): void
    {
        $this->getCustomer($id);
        //check if trans_no is not empty, then set print content

        if (!empty($trans_no)) {
            $this->print_action = 'receipt';
            $this->content = str_replace('"', '', json_encode($this->getDownload($trans_no)));
        }
    }

    public function getDownload($trans_no)
    {
        $filename = substr($trans_no, 0, 4) . "/receipt/topup_" . $trans_no . '.txt';
        //check the file exist or not
        if (Storage::disk('public')->exists($filename)) {
            return Storage::disk('public')->get($filename);
        } else {
            //re-create the print file
            $woc = new WorkOrderController();
            return $woc->getTopupReceipt($trans_no);
        }
    }


    public function getCustomer($id): void
    {
        $this->myCustomer = Customer::findOrFail($id);
        $this->uname = $this->myCustomer->name;
        $this->email = $this->myCustomer->email;
        $this->tel = $this->myCustomer->tel;
        $this->address = $this->myCustomer->address;
        $this->remark = $this->myCustomer->remark;
        $this->balance = $this->myCustomer->balance;
        //get member level info
        $this->member_level_name = $this->myCustomer->member_level_name ?? '';
        $this->member_discount = $this->myCustomer->member_discount ?? '';
        $this->member_expire_date = $this->myCustomer->member_expire_date ?? '';
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Topup')}}" separator progress-indicator />
    <div class="mt-2 mb-4">
        <x-card title="{{__('Customer')}}">
            <div class="grid grid-cols-3 gap-2  mt-2 mb-4">
                <x-input label="{{__('Name')}}" wire:model="uname" disabled />
                <x-input label="{{__('Tel')}}" wire:model="tel" disabled />
                <x-input label="{{__('Email')}}" wire:model="email" disabled />
                <x-input label="{{__('Balance')}}" wire:model="balance" disabled />
                <x-input label="{{__('Address')}}" wire:model="address" disabled />
                <x-input label="{{__('Remark')}}" wire:model="remark" disabled />
                <x-input label="{{__('Member Level')}}" wire:model="member_level_name" disabled />
                <x-input label="{{__('Member Discount')}}" wire:model="member_discount" disabled />
                <x-input label="{{__('Member Expire Date')}}" wire:model="member_expire_date" disabled />
            </div>
            <x-header title="{{__('Member Level')}}" subtitle="{{__('Topup the corresponding amount allows user to automatically become a member of the corresponding level')}}" size="text-xl" separator />
            <x-table :headers="$memberLevelHeaders" :rows="$MemberLevels" show-empty-text />
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
    <script>
        @if($print_action == 'receipt')

        setTimeout(function() {
            WsPrint();
        }, 1000);

        @endif

        function WsPrint() {
            var conn;
            //var command1 = "open COM3 9600";
            //var multilineContent = 'send COM3 {{ $content }}';
            //set up WebApp Hardware Bridge Configuration, add your serial port and Serial Type set to KEY
            var PrntContent = '{{ $content }}';
            async function sendCommands(ws, commands) {
                for (const command of commands) {
                    await new Promise(resolve => {
                        ws.send(command);
                        ws.onmessage = (event) => {
                            //server return message
                            //console.log('Received:', event.data);
                            resolve();
                        };
                    });
                }
            }

            // create WebSocket 
            //const ws = new WebSocket('ws://localhost:8989/ws');
            const ws = new WebSocket('ws://127.0.0.1:12212/serial/KEY?');

            // send commands
            //const commands = [command1, multilineContent];
            const commands = [PrntContent];

            // send commands to server
            ws.onopen = () => {
                sendCommands(ws, commands)
                    .then(() => {
                        //console.log('All commands sent successfully');
                    })
                    .catch(error => {
                        console.error('Error sending commands:', error);
                    });
            };
        }
    </script>

</div>