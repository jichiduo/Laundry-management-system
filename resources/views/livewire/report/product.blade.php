<?php

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\AppGroup;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use carbon\Carbon;
use Livewire\Attributes\Validate;
use App\Http\Controllers\WorkOrderController;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;


new class extends Component {
    use Toast;
    use WithPagination;
    #[Rule('required')]
    public string $start_date = '';
    #[Rule('required')]
    public string $end_date = '';


    public string $content = '';
    public array $sortBy = ['column' => 'amount', 'direction' => 'desc'];



    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'division_name', 'label' => __('Division')],
            ['key' => 'name', 'label' => __('Product')],
            ['key' => 'quantity', 'label' => __('Quantity')],
            ['key' => 'amount', 'label' => __('Amount'), 'format' => ['currency', '0,.']],
        ];
    }

    public function allData(): LengthAwarePaginator
    {
        $user_id = Auth::user()->id;
        $division_id = Auth::user()->division_id;
        $group_id = Auth::user()->group_id;
        $my_id = 0;
        //end_date = end_date + 1 day
        $end_date = $this->end_date;
        $end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));
        $date1 = Carbon::parse($this->start_date);
        $date2 = Carbon::parse($end_date);
        //check the end date is after start date
        if ($date2 < $date1) {
            $this->addError('end_date', __('End date cannot be before start date.'));
            return [];
        }
        //check if division_id is null
        if ($division_id == null || $group_id == null) {
            $this->addError('end_date', __('Fetal Err, cannot find basic info for the current user.'));
            return [];
        }

        if (Auth::user()->role == 'user') {
            $my_id = $division_id;
        } else {
            $my_id = $group_id;
        }
        $data = [];
        if (Auth::user()->role == 'user') {
            //$sql = "select a.division_name,max(a.customer_name) as customer_name, count(a.id) as quantity, sum(b.amount) as amount from work_orders a, transactions b where a.wo_no = b.wo_no and b.remark='CfmOrd' and a.status not in ('draft' , 'cancel') and a.division_id = ? and b.created_at between ? and ? group by a.division_name, a.customer_id order by a.division_name, amount desc";
            $data = DB::table('work_orders as a')
                ->join('transactions as b', 'a.wo_no', '=', 'b.wo_no')
                ->join('work_order_items as c', 'a.wo_no', '=', 'c.wo_no')
                ->select('a.division_name', 'c.name', DB::raw('count(c.quantity) as quantity'), DB::raw('sum(c.sub_total) as amount'))
                ->where('b.remark', 'CfmOrd')
                ->whereNotIn('a.status', ['draft', 'cancel'])
                ->where('a.division_id', $my_id)
                ->whereBetween('b.created_at', [$this->start_date, $end_date])
                ->groupBy('a.division_name', 'c.name')
                ->orderByDesc('amount')
                ->paginate(200);
        } else {
            //$sql = "select a.division_name,max(a.customer_name) as customer_name, count(a.id) as quantity, sum(b.amount) as amount from work_orders a, transactions b where a.wo_no = b.wo_no and b.remark='CfmOrd' and a.status not in ('draft' , 'cancel') and a.group_id = ? and b.created_at between ? and ? group by a.division_name, a.customer_id order by a.division_id, amount desc";
            $data = DB::table('work_orders as a')
                ->join('transactions as b', 'a.wo_no', '=', 'b.wo_no')
                ->join('work_order_items as c', 'a.wo_no', '=', 'c.wo_no')
                ->select('a.division_name', 'c.name', DB::raw('count(c.quantity) as quantity'), DB::raw('sum(c.sub_total) as amount'))
                ->where('b.remark', 'CfmOrd')
                ->whereNotIn('a.status', ['draft', 'cancel'])
                ->where('a.group_id', $my_id)
                ->whereBetween('b.created_at', [$this->start_date, $end_date])
                ->groupBy('a.division_name', 'c.name')
                ->orderBy('a.division_id')
                ->orderByDesc('amount')
                ->paginate(200);
        }
        //dd($data);
        return $data;
    }


    public function with(): array
    {
        $mark = 0;
        //check if end date before start date
        if ($this->end_date < $this->start_date) {
            $this->addError('end_date', __('End date cannot be before start date.'));
            $mark = 1;
        }
        //check if duration is more than 31 days
        $date1 = Carbon::parse($this->start_date);
        $date2 = Carbon::parse($this->end_date);

        $diffInDays = $date1->diffInDays($date2);

        if ($diffInDays > 365) {
            $this->addError('end_date', __('Duration cannot be more than 1 year.'));
            $mark = 1;
        }
        if ($mark == 1) {
            return [
                'allData' => [],
                'headers' => [],
                'headersTotal' => [],
            ];
        } else {
            $this->resetErrorBag();
            return [
                'allData' => $this->allData(),
                'headers' => $this->headers(),
            ];
        }
    }

    public function mount(): void
    {
        //set start date to first day of juanuary and end date to last day of december
        $this->start_date = date('Y-01-01');
        $this->end_date = date('Y-12-31');
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Product Report')}}" separator progress-indicator />

    <!-- TABLE  -->
    <x-card>
        <div class="grid grid-cols-4 content-end gap-2 mt-4 mb-4">
            <x-datetime label="{{__('Start date')}}" wire:model.live.debounce="start_date" icon="o-calendar" />
            <x-datetime label="{{__('End date')}}" wire:model.live.debounce="end_date" icon="o-calendar" />
        </div>
        <div class="mt-4 mb-4">
            <x-header title="{{__('Details')}}" size="text-xl" separator />
            <x-table :headers="$headers" :rows="$allData" with-pagination show-empty-text />
        </div>
    </x-card>

</div>