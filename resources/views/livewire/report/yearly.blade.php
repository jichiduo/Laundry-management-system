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



    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'division_name', 'label' => __('Name')],
            ['key' => 'payment_date', 'label' => __('Date')],
            ['key' => 'revenue', 'label' => __('Revenue'), 'format' => ['currency', '0,.']],
            ['key' => 'topup', 'label' => __('Topup'), 'format' => ['currency', '0,.']],
            ['key' => 'total', 'label' => __('Total'), 'format' => ['currency', '0,.']],
        ];
    }
    public function headersTotal(): array
    {
        return [
            ['key' => 'division_name', 'label' => __('Name')],
            ['key' => 'revenue', 'label' => __('Revenue'), 'format' => ['currency', '0,.']],
            ['key' => 'topup', 'label' => __('Topup'), 'format' => ['currency', '0,.']],
            ['key' => 'total', 'label' => __('Total'), 'format' => ['currency', '0,.']],
        ];
    }
    public function headersMember(): array
    {
        return [
            ['key' => 'group_id', 'label' => __('Group ID')],
            ['key' => 'member_count', 'label' => __('Number of Members')],
            ['key' => 'total', 'label' => __('Total'), 'format' => ['currency', '0,.']],
        ];
    }


    public function allData(): array
    {
        $user_id = Auth::user()->id;
        $division_id = Auth::user()->division_id;
        $group_id = Auth::user()->group_id;
        $my_id = 0;
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

        if (Auth::user()->role == 'user') {
            $sql = "select a.division_name,date_format(b.created_at,'%Y-%m') as payment_date, sum(b.amount) as amount from work_orders a, transactions b where a.wo_no = b.wo_no and b.remark='CfmOrd' and b.payment_type<>'Member Card' and a.status not in ('draft' , 'cancel') and a.division_id = ? and b.created_at between ? and ? group by division_name, payment_date";
            $sql2 = "select division_name, date_format(created_at,'%Y-%m') as payment_date, sum(amount) as amount from transactions where remark='Topup' and division_id = ? and created_at between ? and ? group by division_name, payment_date";
        } else {
            $sql = "select a.division_name,date_format(b.created_at,'%Y-%m') as payment_date, sum(b.amount) as amount from work_orders a, transactions b where a.wo_no = b.wo_no and b.remark='CfmOrd' and b.payment_type<>'Member Card' and a.status not in ('draft' , 'cancel') and a.group_id = ? and b.created_at between ? and ? group by division_name, payment_date order by division_name, payment_date";
            $sql2 = "select division_name, date_format(created_at,'%Y-%m') as payment_date, sum(amount) as amount from transactions where remark='Topup' and group_id = ? and created_at between ? and ? group by division_name, payment_date order by division_name, payment_date";
        }
        //end_date = end_date + 1 day
        $end_date = $this->end_date;
        $end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));

        $data = DB::select($sql, [$my_id, $this->start_date, $end_date]);
        $data2 = DB::select($sql2, [$my_id, $this->start_date, $end_date]);
        //merge data and data2 based on division_name and payment_date
        $data3 = [];
        foreach ($data as $row) {
            $data3[] = (object)[
                'division_name' => $row->division_name,
                'payment_date' => $row->payment_date,
                'revenue' => $row->amount,
                'topup' => 0,
                'total' => $row->amount,
            ];
            end($data3);
            $newIndex = key($data3);

            foreach ($data2 as $row2) {
                if ($row->division_name == $row2->division_name && $row->payment_date == $row2->payment_date) {
                    $data3[$newIndex]->topup = $row2->amount;
                    $data3[$newIndex]->total = $row->amount + $row2->amount;

                    break;
                }
            }
        }
        //dd($data);
        return $data3;
    }

    public function allDataTotal(): array
    {
        //$this->resetErrorBag();
        $user_id = Auth::user()->id;
        $division_id = Auth::user()->division_id;
        $group_id = Auth::user()->group_id;
        $my_id = 0;
        //check if division_id is null
        if ($division_id == null || $group_id == null) {
            $this->addError('start_date', __('Fetal Err, cannot find basic info for the current user.'));
            return [];
        }

        if (Auth::user()->role == 'user') {
            $my_id = $division_id;
        } else {
            $my_id = $group_id;
        }

        if (Auth::user()->role == 'user') {
            $sql = "select a.division_name, sum(b.amount) as amount from work_orders a, transactions b where a.wo_no = b.wo_no and b.remark='CfmOrd' and b.payment_type<>'Member Card' and a.status not in ('draft' , 'cancel') and a.division_id = ? and b.created_at between ? and ? group by division_name";
            //total topup amount per division
            $sql2 = "select division_name, sum(amount) as amount from transactions where remark='Topup' and division_id = ? and created_at between ? and ? group by division_name";
        } else {
            $sql = "select a.division_name, sum(b.amount) as amount from work_orders a, transactions b where a.wo_no = b.wo_no and b.remark='CfmOrd' and b.payment_type<>'Member Card' and a.status not in ('draft' , 'cancel') and a.group_id = ? and b.created_at between ? and ? group by division_name order by division_name";
            $sql2 = "select division_name, sum(amount) as amount from transactions where remark='Topup' and group_id = ? and created_at between ? and ? group by division_name order by division_name";
        }
        //end_date = end_date + 1 day
        $end_date = $this->end_date;
        $end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));

        $data = DB::select($sql, [$my_id, $this->start_date, $end_date]);
        $data2 = DB::select($sql2, [$my_id, $this->start_date, $end_date]);
        //creae a new array , combin $data and $data2 into $data3 based on division_name
        $data3 = [];
        foreach ($data as $row) {
            $data3[] = (object)[
                'division_name' => $row->division_name,
                'revenue' => $row->amount,
                'topup' => 0,
                'total' => $row->amount,
            ];
            end($data3);
            $newIndex = key($data3);
            foreach ($data2 as $row2) {
                if ($row->division_name == $row2->division_name) {
                    $data3[$newIndex]->topup = $row2->amount;
                    $data3[$newIndex]->total = $row->amount + $row2->amount;
                    break;
                }
            }
        }
        //dd($data);
        return $data3;
    }

    public function allDataMember(): array
    {
        $user_id = Auth::user()->id;
        $group_id = Auth::user()->group_id;
        if (Auth::user()->role == 'admin') {
            $sql = "select group_id, count(*) as member_count, sum(balance) as total from customers group by group_id";
            $data = DB::select($sql);
        } else {
            $sql = "select group_id, sum(balance) as total from customers where group_id = ?  group by group_id";
            $data = DB::select($sql, [$group_id]);
        }

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
                'allDataTotal' => [],
                'headersTotal' => [],
                'allDataMember' => [],
                'headersMember' => [],
            ];
        } else {
            $this->resetErrorBag();
            return [
                'allData' => $this->allData(),
                'headers' => $this->headers(),
                'allDataTotal' => $this->allDataTotal(),
                'headersTotal' => $this->headersTotal(),
                'allDataMember' => $this->allDataMember(),
                'headersMember' => $this->headersMember(),
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
    <x-header title="{{__('Yearly Report')}}" separator progress-indicator />

    <!-- TABLE  -->
    <x-card>
        <div class="grid grid-cols-4 content-end gap-2 mt-4 mb-4">
            <x-datetime label="{{__('Start date')}}" wire:model.live.debounce="start_date" icon="o-calendar" />
            <x-datetime label="{{__('End date')}}" wire:model.live.debounce="end_date" icon="o-calendar" />
        </div>
        <div class="mt-4 mb-4">
            <x-header title="{{__('Details')}}" size="text-xl" separator />
            <x-table :headers="$headers" :rows="$allData" show-empty-text />
        </div>
        <x-header title="{{__('Total')}}" size="text-xl" separator />
        <x-table :headers="$headersTotal" :rows="$allDataTotal" show-empty-text />
        <div class="mt-4 mb-4">
            <x-header title="{{__('Member Balance')}}" size="text-xl" separator />
            <x-table :headers="$headersMember" :rows="$allDataMember" show-empty-text />
        </div>
    </x-card>

</div>