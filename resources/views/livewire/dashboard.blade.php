<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>

    <!-- HEADER -->
    <x-header title="Home" separator progress-indicator>
    </x-header>

    <!-- TABLE  -->
    <x-card title="Welcome to the system" separator>
        <div class="p-4 rounded-xl grid lg:grid-cols-2 gap-4 bg-base-200">
            <x-card title="Item 1" separator>
                this is item1
            </x-card>
            <x-card title="Item 2" separator>
                this is item2
            </x-card>
        </div>


    </x-card>

</div>