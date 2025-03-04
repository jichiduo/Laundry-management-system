<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

new
    #[Layout('components.layouts.empty')]       // <-- Here is the `empty` layout
    #[Title('Login')]
    class extends Component {

        #[Rule('required|email')]
        public string $email = '';

        #[Rule('required')]
        public string $password = '';

        public string $locale = '';

        public function mount()
        {
            // It is logged in
            if (Auth::user()) {
                return redirect('/');
            }
            $this->locale = App::currentLocale();
        }

        public function login()
        {
            $credentials = $this->validate();

            if (auth()->attempt($credentials)) {
                request()->session()->regenerate();
                //get language from user() then put to session
                $locale = Auth::user()->language;
                session()->put('locale', $locale);

                return redirect()->intended('/');
            }

            $this->addError('email', __('The provided credentials do not match our records.'));
        }
    }; ?>

<div>
    <div class="mt-24 w-80 flex flex-col items-center justify-center mx-auto">
        <div class="mb-10">
            <p class="text-blue-600 text-2xl"> {{ isset($title) ? $title.' - '.config('app.name') : config('app.name')
                }} {{__('System Login')}}</p>
        </div>

        <x-form wire:submit="login">
            <x-input label="{{__('Email')}}" wire:model="email" icon="o-envelope" inline />
            <x-input label="{{__('Password')}}" wire:model="password" type="password" icon="o-key" inline />

            <x-slot:actions>
                <x-button label="{{__('Login')}}" type="submit" icon="o-paper-airplane" class="btn-primary"
                    spinner="login" />
            </x-slot:actions>
            <x-dropdown>
                <x-slot:trigger>
                    <x-button icon="o-globe-alt" class="btn-ghost" />
                </x-slot:trigger>
                <x-menu-item title="EN" link="{{route('language', 'en')}}" />
                <x-menu-item title="ID" link="{{route('language', 'id')}}" />
                {{--
                <x-menu-item title="CN" link="{{route('language', 'cn')}}" /> --}}
            </x-dropdown>
        </x-form>
    </div>
</div>