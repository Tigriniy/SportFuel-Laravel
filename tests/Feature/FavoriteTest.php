<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_save_and_remove_a_product_from_favorites(): void
    {
        $user = $this->user('Customer');
        $product = $this->product();
        $page = route('products.show', $product->slug);
        $list = route('favorites.index');
        $store = route('favorites.store', $product->slug);

        $this->get($list)->assertOk()->assertSee('Список избранного пуст');
        $this->post($store)->assertRedirect(route('login'));

        $this->actingAs($user)->from($page)->post($store)->assertRedirect($page);
        $this->post($store)->assertRedirect($page);
        $this->assertDatabaseCount('favorites', 1);
        $this->get($page)->assertSee('Убрать из избранного');
        $this->get(route('catalog'))->assertSee('Убрать из избранного')
            ->assertSee('Избранное, количество товаров: 1');
        $this->get($list)->assertSee($product->name);

        $this->from($list)->delete(route('favorites.destroy', $product->slug))->assertRedirect($list);
        $this->get($list)->assertSee('Список избранного пуст')->assertDontSee($product->name);
        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_favorites_are_private_and_hidden_products_cannot_be_added(): void
    {
        $owner = $this->user('Owner');
        $other = $this->user('OtherUser');
        $product = $this->product();
        $owner->favorites()->create(['product_id' => $product->id]);
        $list = route('favorites.index');

        $this->actingAs($other)->get($list)->assertDontSee($product->name);
        $this->from($list)->delete(route('favorites.destroy', $product->slug))->assertRedirect($list);
        $this->assertDatabaseHas('favorites', ['user_id' => $owner->id, 'product_id' => $product->id]);

        $product->update(['is_active' => false]);
        $this->post(route('favorites.store', $product->slug))->assertNotFound();
        $this->post(route('logout'));
        $this->actingAs($owner)->get($list)->assertDontSee($product->name)->assertDontSee('Избранное, количество товаров: 1');
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Протеин', 'slug' => 'protein']);
        $brand = Brand::create(['name' => 'SportFuel', 'slug' => 'sportfuel']);

        return Product::create([
            'name' => 'Тестовый протеин', 'slug' => 'test-protein',
            'category_id' => $category->id, 'brand_id' => $brand->id,
            'price' => 500, 'stock' => 2,
        ]);
    }

    private function user(string $login): User
    {
        return User::create([
            'login' => $login, 'email' => strtolower($login).'@example.com',
            'first_name' => 'Иван', 'last_name' => 'Иванов', 'patronymic' => 'Иванович',
            'phone' => '79990001122', 'password' => 'TestPassword!42',
        ]);
    }
}
