<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_change_and_remove_a_product(): void
    {
        $product = $this->product();
        $url = route('cart.store', $product->slug);
        $data = ['quantity' => 1];

        $this->get(route('cart.index'))->assertOk()->assertSee('Корзина пуста');
        $this->post($url, $data)->assertRedirect(route('login'));
        $this->assertDatabaseCount('cart_items', 0);

        $this->actingAs($this->user('Customer'))->get(route('products.show', $product->slug))
            ->assertOk()->assertSee('Добавить в корзину');

        $this->post($url, $data)->assertRedirect(route('cart.index'));
        $this->post($url, $data)->assertRedirect(route('cart.index'));
        $item = CartItem::sole();
        $this->assertSame(2, $item->quantity);

        $this->get(route('cart.index'))->assertOk()
            ->assertSee('Итого: 399,98 ₽')
            ->assertSee('Корзина, количество товаров: 2');

        $this->patch(route('cart.update', $item), ['quantity' => 3])->assertRedirect(route('cart.index'));
        $this->get(route('cart.index'))->assertSee('Итого: 599,97 ₽');

        $this->delete(route('cart.destroy', $item))->assertRedirect(route('cart.index'));
        $this->assertModelMissing($item);
        $this->get(route('cart.index'))->assertSee('Корзина пуста');
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_variants_have_separate_rows_and_use_their_own_prices_and_stock(): void
    {
        $product = $this->product();
        $other = $this->product('Другой товар');
        $small = $product->variants()->create(['title' => '500 г', 'sku' => 'small', 'price' => '410.25', 'stock' => 2]);
        $large = $product->variants()->create(['title' => '1 кг', 'sku' => 'large', 'price' => '770.60', 'stock' => 1]);
        $foreign = $other->variants()->create(['title' => '500 г', 'sku' => 'foreign', 'price' => '12.00', 'stock' => 1]);
        $url = route('cart.store', $product->slug);

        $this->actingAs($this->user('Customer'));
        $this->post($url, ['quantity' => 1])->assertSessionHasErrorsIn('cart', 'variant_id');
        $this->post($url, ['quantity' => 1, 'variant_id' => $foreign->id])->assertSessionHasErrorsIn('cart', 'variant_id');
        $this->post($url, ['quantity' => 2, 'variant_id' => $small->id])->assertRedirect(route('cart.index'));
        $this->post($url, ['quantity' => 1, 'variant_id' => $small->id])->assertSessionHasErrorsIn('cart', 'quantity');
        $this->post($url, ['quantity' => 1, 'variant_id' => $large->id])->assertRedirect(route('cart.index'));
        $this->assertDatabaseCount('cart_items', 2);
        $this->get(route('cart.index'))->assertSee('Итого: 1 591,10 ₽')->assertSee('500 г')->assertSee('1 кг');

        $large->update(['stock' => 0]);
        $this->get(route('cart.index'))->assertSee('Позиция сейчас недоступна.')->assertSee('Итого: 820,50 ₽');
    }

    public function test_cart_actions_are_scoped_to_owner_and_validate_new_quantity(): void
    {
        $product = $this->product();
        $owner = $this->user('Owner');
        $this->actingAs($owner)->post(route('cart.store', $product->slug), ['quantity' => 1]);
        $item = CartItem::sole();
        $this->patch(route('cart.update', $item), ['quantity' => 4])->assertSessionHasErrorsIn('cart', 'quantity');

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->actingAs($this->user('Other'))->get(route('cart.index'))->assertDontSee($product->name);
        $this->patch(route('cart.update', $item), ['quantity' => 2])->assertNotFound();
        $this->delete(route('cart.destroy', $item))->assertNotFound();
        $this->assertSame(1, $item->fresh()->quantity);
    }

    private function product(string $name = 'Тестовый протеин'): Product
    {
        $category = Category::firstOrCreate(['slug' => 'protein'], ['name' => 'Протеин']);
        $brand = Brand::firstOrCreate(['slug' => 'sportfuel'], ['name' => 'SportFuel']);

        return Product::create([
            'name' => $name, 'slug' => fake()->unique()->uuid(), 'category_id' => $category->id,
            'brand_id' => $brand->id, 'stock' => 3, 'price' => '199.99',
        ]);
    }

    private function user(string $login): User
    {
        return User::create([
            'login' => $login, 'email' => strtolower($login).'@example.com', 'phone' => '79990001122',
            'first_name' => 'Иван', 'last_name' => 'Иванов', 'patronymic' => 'Иванович',
            'password' => 'TestPassword!42',
        ]);
    }
}
