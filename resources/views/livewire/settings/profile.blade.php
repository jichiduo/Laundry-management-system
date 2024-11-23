<?php

use Livewire\Volt\Component;
use Mary\Traits\Toast;


new class extends Component {
    use Toast;
    //check the validation of the password
    public $old_password;
    public $new_password;
    public $confirm_password;

    public function save() {
        $this->validate([
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password',
        ]);

        if (Hash::check($this->old_password, Auth::user()->password)) 
        {
            Auth()->user()->password = Hash::make($this->new_password);
            Auth()->user()->save();
            $this->reset();
            $this->success("Password successfully changed.", position: 'toast-top');
        } else {
            $this->error("Old password is incorrect.", position: 'toast-top');
        }
    }
}; ?>

<div>
    <x-card title="{{Auth()->user()->name;}} Profile" shadow separator progress-indicator>

        <x-input label="Old Password" wire:model='old_password' type="password" clearable />

        <x-input label="New Password" wire:model='new_password' type="password" clearable />

        <x-input label="Confirm Password" wire:model='confirm_password' type="password" clearable />

        <div class="flex justify-center">
            <x-button label="Change Password" wire:confirm="Are you sure?" wire:click="save" class="btn-primary mt-4" />
        </div>

    </x-card>


</div>