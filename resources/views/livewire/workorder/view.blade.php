<?php

use Livewire\Volt\Component;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public string $action = '';
    public WorkOrder $wo;
    public string $wo_no = '';
    public string $customer_name = '';
    public string $customer_email = '';
    public string $customer_tel = '';
    public string $explain = '';
    public int    $is_express = 0;
    public int    $piece = 0;
    public $total = 0;
    public $discount = 0;
    public $tax = 0;
    public $grand_total = 0;
    public string $status = '';
    public string $pickup_date = '';
    public string $collect_date = '';
    public string $print = '';
    public array $Item_sortBy = ['column' => 'id', 'direction' => 'asc'];
    public string $content = '';

    // Table headers
    public function WOItemHeaders(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'barcode', 'label' => __('Barcode'), 'class' => 'w-24'],
            ['key' => 'name', 'label' => __('Name'), 'class' => 'w-36'],
            ['key' => 'price', 'label' => __('Price'), 'format' => ['currency', '0,.']],
            ['key' => 'unit', 'label' => __('Unit')],
            ['key' => 'quantity', 'label' => __('Quantity')],
            // ['key' => 'discount', 'label' => __('Discount') ],
            // ['key' => 'tax', 'label' => __('Tax') ],
            ['key' => 'sub_total', 'label' => __('SubTotal'), 'format' => ['currency', '0,.']],
            ['key' => 'pickup_date', 'label' => __('Pickup'), 'format' => ['date', 'd/m/Y']],
            ['key' => 'remark', 'label' => __('Remark')],
            ['key' => 'location', 'label' => __('Location')],
            ['key' => 'status', 'label' => __('Status')],

        ];
    }

    public function WOItems()
    {
        // dd($this->wo_no);
        return WorkOrderItem::query()
            ->where('wo_no', $this->wo_no)
            ->get();
    }

    public function with(): array
    {
        return [
            'WOItems' => $this->WOItems(),
            'WOItemHeaders' => $this->WOItemHeaders(),
        ];
    }

    public function mount($id, $action): void
    {
        $this->action = $action;
        if ($this->action == null) {
            $this->action = 'show';
        }
        $this->wo = WorkOrder::findOrFail($id);
        $this->wo_no = $this->wo->wo_no;
        $this->status = $this->wo->status;
        if ($this->status != 'draft') {
            $this->customer_name = $this->wo->customer_name;
            $this->customer_email = $this->wo->customer_email;
            $this->customer_tel = $this->wo->customer_tel;
            $this->explain = $this->wo->explain;
            $this->is_express = $this->wo->is_express;
            $this->piece = $this->wo->piece;
            $this->total = $this->wo->total;
            $this->discount = $this->wo->discount;
            $this->tax = $this->wo->tax;
            $this->grand_total = $this->wo->grand_total;

            //format date as Y-m-d
            if ($this->wo->pickup_date != null) {
                $this->pickup_date = date_format($this->wo->pickup_date, 'Y-m-d');
            }
            if ($this->wo->collect_date != null) {
                $this->collect_date = date_format($this->wo->collect_date, 'Y-m-d');
            }
            //get download
            $this->content = str_replace('"', '', json_encode($this->getDownload($this->wo_no)));
        }
    }

    public function getDownload($wo_no)
    {
        $filename = substr($wo_no, 0, 4) . "/receipt/" . $wo_no . '.txt';
        // $woc = new WorkOrderController();
        // $this->print = $woc->getReceipt($wo_no);
        // return response()->streamDownload(function () {
        //     echo $this->print;
        // }, $filename);
        //check the file exist or not
        if (Storage::disk('public')->exists($filename)) {
            return Storage::disk('public')->get($filename);
        } else {
            //re-create the print file
            $woc = new WorkOrderController();
            return $woc->getReceipt($wo_no);
        }
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Work Order')}}" subtitle="{{__('Work order number')}}:{{$wo_no}}" separator
        progress-indicator />
    @if($action=='new')
    <x-alert title="{{__('Work Order Successfully Confirmed')}}" icon="o-rocket-launch" class="bg-lime-500 mb-4"
        dismissible>
    </x-alert>
    @endif
    <div class="flex justify-end mb-4">
        <x-button label="{{__('Print Receipt')}}" icon="o-printer" x-on:click="WsPrint()" class="btn-sm btn-primary" />
    </div>
    <x-card title="{{__('Basic Information')}}" separator>
        <div class="grid grid-cols-3 gap-2 mb-4">

            @if($status=='draft')
            <x-badge value="{{ strtoupper($status)}}" class="badge-lg" />
            @elseif($status=='pending')
            <x-badge value="{{ strtoupper($status)}}" class="badge-lg bg-yellow-500" />
            @elseif($status=='4pickup')
            <x-badge value="{{ strtoupper($status)}}" class="badge-lg bg-blue-500" />
            @elseif($status=='completed')
            <x-badge value="{{ strtoupper($status)}}" class="badge-lg bg-lime-500" />
            @else
            <x-badge value="{{ strtoupper($status)}}" class="badge-lg bg-red-500" />
            @endif

        </div>
        <div class="grid grid-cols-3 gap-2 mt-4">
            <x-input label="{{__('Customer Name')}}" wire:model="customer_name" />
            <x-input label="{{__('Customer Tel')}}" wire:model="customer_tel" />
            <x-input label="{{__('Customer Email')}}" wire:model="customer_email" />
        </div>

        <div class="grid grid-cols-3 gap-2">
            <x-input label="{{__('Piece')}}" wire:model="piece" />
            <x-input label="{{__('Discount')}}" wire:model="discount" />
            <x-input label="{{__('Tax')}}" wire:model="tax" />
            <x-input label="{{__('Grand Total')}}" wire:model="grand_total" />
            <x-input label="{{__('Pickup Date')}}" wire:model="pickup_date" />
            <x-input label="{{__('Collect Date')}}" wire:model="collect_date" />
        </div>
        <x-textarea label="{{__('Explain')}}" wire:model="explain" placeholder="{{__('write explaination here ...')}}"
            rows="2" hint="{{__('Max 255 chars')}}" />
        <div class="mt-4">
            @if($is_express==1)
            <x-checkbox label="{{__('Express')}}" checked wire:model="is_express" />
            @else
            <x-checkbox label="{{__('Express')}}" wire:model="is_express" />
            @endif
        </div>
    </x-card>
    <x-card title="{{__('Details')}}" separator>

        <x-table :headers="$WOItemHeaders" :rows="$WOItems" show-empty-text>
            @scope('cell_status', $data)
            @if($data->status == 'draft')
            <x-badge :value="$data->status" />
            @elseif ($data->status == 'pending')
            <x-badge :value="$data->status" class="text-yellow-500" />
            @elseif ($data->status == '4pickup')
            <x-badge :value="$data->status" class="text-blue-500" />
            @elseif ($data->status == 'complete')
            <x-badge :value="$data->status" class="text-lime-500" />
            @else
            <x-badge :value="$data->status" class="text-red-500" />
            @endif
            @endscope
        </x-table>
    </x-card>

    <script>
        @if($action == 'new')

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