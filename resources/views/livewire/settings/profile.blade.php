<?php

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use Toast;
    //check the validation of the password
    public $old_password;
    public $new_password;
    public $confirm_password;

    public function save()
    {
        $this->validate([
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password',
        ]);

        if (Hash::check($this->old_password, Auth::user()->password)) {
            Auth::user()->password = Hash::make($this->new_password);
            Auth::user()->save();
            $this->reset();
            $this->success(__("Password successfully changed."), position: 'toast-top');
        } else {
            $this->error(__("Old password is incorrect."), position: 'toast-top');
        }
    }
}; ?>

<div>
    <x-card title="{{Auth::user()->name;}} {{__('Profile')}}" shadow separator progress-indicator>

        <x-input label="{{__('Old Password')}}" wire:model='old_password' type="password" clearable />

        <x-input label="{{__('New Password')}}" wire:model='new_password' type="password" clearable />

        <x-input label="{{__('Confirm Password')}}" wire:model='confirm_password' type="password" clearable />

        <div class="flex justify-center">
            <x-button label="{{__('Change Password')}}" wire:confirm="{{__('Are you sure?')}}" wire:click="save"
                class="btn-primary mt-4" />
        </div>
        <x-dropdown>
            <x-slot:trigger>
                <x-button icon="o-globe-alt" class="btn-ghost" />
            </x-slot:trigger>
            <x-menu-item title="EN" link="{{route('language', 'en')}}" />
            <x-menu-item title="ID" link="{{route('language', 'id')}}" />
            {{--
            <x-menu-item title="CN" link="{{route('language', 'cn')}}" /> --}}
        </x-dropdown>
    </x-card>

    <x-card title="{{__('Download')}}" shadow separator progress-indicator class="mt-4">
        <x-button label="{{__('Printer Server')}}" link="/download/whb-1.0.0.exe" external icon="o-link" class="btn-ghost" />
        <x-collapse>
            <x-slot:heading>
                {{__('How to use?')}}
            </x-slot:heading>
            <x-slot:content>
                {{__('1. Download the printer server from the link above.')}}<br>
                {{__('2. Install the printer server on your computer.')}}<br>
                {{__('3. Add one serial port name is KEY')}}<br>
                <img src="/download/print_server_settings.png">
            </x-slot:content>
        </x-collapse>
    </x-card>


</div>