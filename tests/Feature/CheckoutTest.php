<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_saves_price_snapshots_and_updates_stock_exactly_once(): void
    {
        $product = $this->product('Тестовый протеин', '1500.25');
        $other = $this->product('Тестовый креатин', '900.00');
        $variant = $other->variants()->create([
            'title' => '500 г', 'sku' => 'CHECKOUT-500', 'price' => '650.10', 'stock' => 3,
        ]);
        $user = $this->user();
        $url = route('checkout.show');

        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($user)->get($url)->assertRedirect(route('cart.index'));
        $this->post(route('cart.store', $product->slug), ['quantity' => 1])->assertRedirect(route('cart.index'));
        $this->post(route('cart.store', $other->slug), ['quantity' => 2, 'variant_id' => $variant->id])
            ->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))->assertSee($url);
        $checkout = $this->get($url)->assertOk()->assertSee('2 800,45 ₽')->assertSee('500 г');
        $payload = $this->payload($checkout->viewData('quote'));
        $this->from($url)->post(route('checkout.store'), array_replace($payload, ['payment_method' => 'unknown']))
            ->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);

        $this->post(route('checkout.store'), $payload + ['total_amount' => '0.01'])
            ->assertRedirect(route('cart.index'))->assertSessionHas('cart_status');

        $order = Order::sole();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(Order::PENDING, $order->status);
        $this->assertSame('3050.45', $order->total_amount);
        $this->assertSame('250.00', $order->delivery_fee);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id, 'product_name' => $other->name, 'variant_title' => '500 г',
            'quantity' => 2, 'unit_price' => '650.10', 'total' => '1300.20',
        ]);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(1, $variant->fresh()->stock);
        $this->assertSame(3, $other->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);

        $this->post(route('checkout.store'), $payload)->assertSessionHasErrorsIn('cart', 'checkout');
        $this->assertDatabaseCount('orders', 1);

        $other->delete();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id, 'product_id' => null, 'variant_id' => null,
            'product_name' => 'Тестовый креатин', 'variant_title' => '500 г',
        ]);

        $this->get(route('orders.index'))->assertOk()
            ->assertSee(route('orders.show', $order))
            ->assertSee('Ожидает обработки')
            ->assertSee('3 050,45 ₽');
        $this->get(route('orders.show', $order))->assertOk()
            ->assertSee('Тестовый креатин')
            ->assertSee('500 г')
            ->assertSee('650,10 ₽ × 2 шт.')
            ->assertSee('Почта России')
            ->assertSee('Томск, ул. Ленина, д. 5')
            ->assertSee('Доставка: 250,00 ₽');
    }

    public function test_checkout_leaves_cart_and_stock_untouched_when_price_or_availability_changes(): void
    {
        $user = $this->user();
        $first = $this->product('Первый товар', '1000.00');
        $second = $this->product('Второй товар', '1000.00');
        $this->actingAs($user);
        foreach ([$first, $second] as $product) {
            $this->post(route('cart.store', $product->slug), ['quantity' => 2]);
        }

        $quote = $this->get(route('checkout.show'))->assertOk()->assertSee('бесплатно')->viewData('quote');
        $second->update(['price' => '1200.00']);
        $this->post(route('checkout.store'), $this->payload($quote))->assertSessionHasErrorsIn('cart', 'checkout');
        $quote = $this->get(route('checkout.show'))->assertOk()->viewData('quote');
        $second->update(['stock' => 1]);
        $this->post(route('checkout.store'), $this->payload($quote))->assertSessionHasErrorsIn('cart', 'checkout');

        $this->assertSame(3, $first->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 2);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_order_history_only_shows_the_signed_in_customers_orders(): void
    {
        $owner = $this->user();
        $order = $owner->orders()->create([
            'status' => Order::PENDING, 'delivery_method' => 'courier', 'payment_method' => 'cash',
            'delivery_address' => 'Адрес покупателя', 'contact_phone' => '79990001122',
            'total_amount' => '4500.00',
        ]);
        $other = $owner->replicate()->fill(['login' => 'OtherBuyer', 'email' => 'other@example.com']);
        $other->save();
        $index = route('orders.index');
        $detail = route('orders.show', $order);

        $this->get($index)->assertRedirect(route('login'));
        $this->get($detail)->assertRedirect(route('login'));

        $this->actingAs($other)->get($index)->assertOk()
            ->assertSee('У тебя пока нет заказов.')
            ->assertDontSee($detail);
        $this->get($detail)->assertNotFound();
        $this->get(route('profile'))->assertOk()->assertSee($index);
    }

    private function payload(string $quote): array
    {
        return [
            'quote' => $quote, 'delivery_method' => 'post', 'payment_method' => 'card',
            'delivery_address' => 'Томск, ул. Ленина, д. 5', 'contact_phone' => '79990001122',
            'comment' => 'Учебный заказ',
        ];
    }

    private function product(string $name, string $price): Product
    {
        $category = Category::firstOrCreate(['slug' => 'protein'], ['name' => 'Протеин']);
        $brand = Brand::firstOrCreate(['slug' => 'sportfuel'], ['name' => 'SportFuel']);

        return Product::create([
            'name' => $name, 'slug' => fake()->unique()->uuid(), 'category_id' => $category->id,
            'brand_id' => $brand->id, 'stock' => 3, 'price' => $price,
        ]);
    }

    private function user(): User
    {
        return User::create([
            'login' => 'Customer', 'email' => 'customer@example.com', 'phone' => '79990001122',
            'first_name' => 'Иван', 'last_name' => 'Иванов', 'patronymic' => 'Иванович',
            'password' => 'TestPassword!42',
        ]);
    }
}
