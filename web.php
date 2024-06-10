<?php

use App\Livewire\HomePage;
use App\Livewire\CategoriesPage;
use App\Livewire\ProductsPage;
use App\Livewire\CartPage;
use App\Livewire\ProductDetailPage;
use App\Livewire\CheckoutPage;
use App\Livewire\MyOrdersPage;
use App\Livewire\MyOrderDetailPage;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\Auth\ForgotPage;
Use App\Livewire\Auth\ResetPasswordPage;
use App\Livewire\SuccesPage;
use App\Livewire\CancelPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// routes/web.php


Route::get('/', HomePage::class);
Route::get('/categories',CategoriesPage::class);
Route::get('/products',ProductsPage::class);
Route::get('/cart',CartPage::class);
Route::get('/products/{slug}', ProductDetailPage::class);

Route::middleware('guest')->group(function () {
    Route::get('/login',LoginPage::class)->name('login');
    Route::get('/register',RegisterPage::class);
    Route::get('/forgot',ForgotPage::class)->name('password.request');
    Route::get('/reset/{token}',ResetPasswordPage::class)->name('password.reset');

});

Route::middleware('auth')->group(function () {
    Route::get('/logout', function (){
        Auth::logout();
        return redirect('/');
    });
    Route::get('/my-orders',MyOrdersPage::class);
    Route::get('/my-orders/{order}', MyOrderDetailPage::class);
    Route::get('/checkout',CheckoutPage::class)->name('my-orders.show');
    Route::get('/success',SuccesPage::class)->name('success');
    Route::get('/cancel',CancelPage::class)->name('cancel');

});


