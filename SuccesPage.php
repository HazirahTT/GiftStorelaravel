<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Success - Iraty Gifts')]
class SuccesPage extends Component
{
    public function render()
    {
        $latest_order = Order::with('address')->where('user_id', auth()->user()->id)->latest()->first();

        return view('livewire.succes-page', [
            'order' => $latest_order,
        ]);
    }
}
