<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
 
new
#[Layout('components.layouts.empty')]       // <-- Here is the `empty` layout
#[Title('Login')]
class extends Component {
 
    #[Rule('required|email')]
    public string $email = '';
 
    #[Rule('required')]
    public string $password = '';
 
    public function mount()
    {
        // It is logged in
        if (auth()->user()) {
            return redirect('/');
        }
    }
 
    public function login()
    {
        $credentials = $this->validate();
 
        if (auth()->attempt($credentials)) {
            request()->session()->regenerate();
 
            return redirect()->intended('/');
        }
 
        $this->addError('email', 'The provided credentials do not match our records.');
    }

}; ?>

<div>
    <div class="mt-24 w-80 flex flex-col items-center justify-center mx-auto">
        <div class="mb-10">
            <p class="text-blue-600 text-2xl"> Cuci-Cuci System Login </p>
        </div>

        <x-form wire:submit="login">
            <x-input label="E-mail" wire:model="email" icon="o-envelope" inline />
            <x-input label="Password" wire:model="password" type="password" icon="o-key" inline />

            <x-slot:actions>
                <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login" />
            </x-slot:actions>
        </x-form>
    </div>
</div>