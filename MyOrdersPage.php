<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title("My Orders')]
class MyOrdersPage extends Component {
  use With Pagination;

  public function render() {
    $my_orders = Order: :where('user_id', auth()->id())->latest()->paginate(2);
    return view('livewire.my-orders-page', [
      'orders' => $my_orders,
    ]);
  }
}
