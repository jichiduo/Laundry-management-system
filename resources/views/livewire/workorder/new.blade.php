<?php
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use App\Http\Controllers\WorkOrderController;
use App\Models\Customer;
use App\Models\WorkOrder;
use App\Models\AppGroup;
use Carbon\Carbon;


new class extends Component {
    public WorkOrder $myWorkOrder;

    public function mount() {
        $this->myWorkOrder = new WorkOrder();
        $woc = new WorkOrderController();
        //get division id from current user
        $this->myWorkOrder->user_id = auth()->user()->id;
        $this->myWorkOrder->user_name = auth()->user()->name;
        //submit_date = today
        $this->myWorkOrder->division_id = auth()->user()->division_id;
        $this->myWorkOrder->division_name = auth()->user()->division_name;
        $this->myWorkOrder->group_id = auth()->user()->group_id;
        $this->myWorkOrder->group_name = auth()->user()->group_name;
        //get tax rate from database by group id
        
        $this->myWorkOrder->base_currency = AppGroup::find($this->myWorkOrder->group_id)->currency;
        if(empty($this->myWorkOrder->base_currency)){
            $this->myWorkOrder->base_currency = 'SGD';
        }
        $this->myWorkOrder->status = "draft";
        //get wo number     
        $this->myWorkOrder->wo_no = $woc->get_wo_no($this->myWorkOrder->division_id);
        //save wo and redirect to update page
        
        $this->myWorkOrder->save();
        $wo_id = $this->myWorkOrder->id;
        return redirect()->route('workorderupdate', $wo_id);
    }
    
}; ?>

<div>
    <x-loading /> Generating Work Order...
</div>