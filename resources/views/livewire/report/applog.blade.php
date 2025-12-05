<?php

use App\Models\AppGroup;
use App\Models\AppLog;
use App\Models\MemberLevel;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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


    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];






    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'wo_no', 'label' => __('WO No'), 'class' => 'w-36'],
            ['key' => 'trans_no', 'label' => __('Trans No')],
            ['key' => 'user_name', 'label' => __('User Name')],
            ['key' => 'action', 'label' => __('Action')],
            ['key' => 'amount', 'label' => __('Amount')],
            ['key' => 'remark', 'label' => __('Remark')],
            ['key' => 'created_at', 'label' => __('Created At'), 'format' => ['datetime', 'Y-m-d H:i:s']],
        ];
    }

    public function Applogs(): LengthAwarePaginator
    {
        return Applog::query()
            ->when($this->search, fn(Builder $q) => $q->where('wo_no', 'like', "%$this->search%")->orwhere('trans_no', 'like', "%$this->search%")->orWhere('remark', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(200);
    }


    public function with(): array
    {
        return [
            'Applogs' => $this->Applogs(),
            'headers' => $this->headers(),
        ];
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="{{__('Applog')}}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="{{__('wo no, trans no, remark...')}}" wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$Applogs" :sort-by="$sortBy" with-pagination show-empty-text>
        </x-table>
    </x-card>


</div>