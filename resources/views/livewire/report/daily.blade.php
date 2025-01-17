<?php

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\AppGroup;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;
use App\Http\Controllers\WorkOrderController;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;


new class extends Component {
    use Toast;
    use WithPagination;

    public string $start_date = '';
    public string $end_date = '';


    public string $content = '';



    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'payment_type', 'label' => __('Payment Type')],
            ['key' => 'amount', 'label' => __('Amount'),'format' => ['currency', '0,.']],
        ];
    }

    public function allData(): array
    {
        $this->end_date = date('Y-m-d', strtotime($this->start_date . ' +1 day'));
        $sql = "select payment_type, sum(amount) as amount from transactions where remark='CfmOrd' and created_at between ? and ? group by payment_type";
        $data= DB::select($sql, [$this->start_date, $this->end_date]);
        //dd($data);
        return $data;
    }

    public function with(): array
    {
 
        return [
            'allData' => $this->allData(),
            'headers' => $this->headers(),
        ];
    }

    public function mount(): void
    {
        //set start date the today minus 30 days ,end date is today
        $this->start_date = date('Y-m-d');
        
    } 



};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Daily Report')}}" separator progress-indicator />

    <!-- TABLE  -->
    <x-card>
        <div class="grid grid-cols-4 content-end gap-2 mt-4 mb-4">
            <x-datetime label="{{__('Report date')}}" wire:model.live.debounce="start_date" icon="o-calendar" />
        </div>
        <x-table :headers="$headers" :rows="$allData" show-empty-text />
    </x-card>

</div>