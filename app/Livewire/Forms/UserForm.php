<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    //
    #[Validate('required|min:5')]
    public $name = '';

    #[Validate('required|min:4')]
    public $password = '';

    #[Validate('required')]
    public $category = '';
    #[Validate('required')]
    public $group_id = 0;
    #[Validate('required')]
    public $group_name = '';
}
