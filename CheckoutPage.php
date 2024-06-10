<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Mail\OrderPlaced;
use App\Models\Address;

#[Title('Checkout')]
class CheckoutPage extends Component
{
    public $name;
    public $phone;
    public $street_address;
    public $city;
    public $state;
    public $zip_code;
    public $payment_method;

    public function mount()
    {
        $cart_items = CartManagement::getCartItemsFromCookie();
        if (count($cart_items) == 0) {
            return redirect('/products');
        }
    }

    public function placeOrder()
    {
        // Validate inputs
        $this->validate([
            'name' => 'required',
            'phone' => 'required',
            'street_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_method' => 'required',
        ]);

        // Get cart items
        $cart_items = CartManagement::getCartItemsFromCookie();
        $line_items = [];

        foreach ($cart_items as $item) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'myr',
                    'unit_amount' => $item['unit_amount'] * 100,
                    'product_data' => [
                        'name' => $item['name'],
                    ]
                ],
                'quantity' => $item['quantity'],
            ];
        }

        // Create order
        $order = new Order();
        $order->user_id = auth()->user()->id;
        $order->grand_total = CartManagement::calculateGrandTotal($cart_items);
        $order->payment_method = $this->payment_method; // Corrected typo
        $order->payment_status = 'pending';
        $order->status = 'new';
        $order->currency = 'myr';
        $order->shipping_amount = 0;
        $order->shipping_method = 'none';
        $order->notes = 'Order placed by ' . auth()->user()->name;

        // Save order to generate ID
        $order->save();

        // Create address and associate with order
        $address = new Address();
        $address->first_name = $this->name; // Adjust as per your schema
        $address->phone = $this->phone;
        $address->street_address = $this->street_address;
        $address->city = $this->city;
        $address->state = $this->state;
        $address->zip_code = $this->zip_code;
        $address->order_id = $order->id;
        $address->save();

        // Associate line items with order
        foreach ($cart_items as $item) {
            $order->items()->create([
                'product_id' => $item['id'], // Adjust as per your schema
                'quantity' => $item['quantity'],
                'price' => $item['unit_amount'],
            ]);
        }

        // Clear cart items
        CartManagement::clearCartItemsFromCookie();

        // Send email notification
        Mail::to(request()->user())->send(new OrderPlaced($order));

        // Handle payment
        $redirect_url = '';
        if ($this->payment_method == 'stripe') {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $sessionCheckOut = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => auth()->user()->email,
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cancel'),
            ]);

            $redirect_url = $sessionCheckOut->url;
        } else {
            $redirect_url = route('success');
        }

        // Redirect to payment or success page
        return redirect($redirect_url);
    }

    public function render()
    {
        $cart_items = CartManagement::getCartItemsFromCookie();
        $grand_total = CartManagement::calculateGrandTotal($cart_items);
        return view('livewire.checkout-page', [
            'cart_items' => $cart_items,
            'grand_total' => $grand_total,
        ]);
    }
}
