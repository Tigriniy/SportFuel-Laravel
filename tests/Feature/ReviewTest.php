<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_is_published_only_after_moderation_and_rating_follows_status(): void
    {
        $category = Category::create(['name' => 'Протеин', 'slug' => 'protein']);
        $brand = Brand::create(['name' => 'SportFuel', 'slug' => 'sportfuel']);
        $product = Product::create([
            'name' => 'Протеин', 'slug' => 'test-protein', 'category_id' => $category->id,
            'brand_id' => $brand->id, 'price' => 500, 'stock' => 4,
        ]);
        $page = route('products.show', $product->slug);
        $store = route('reviews.store', $product->slug);
        $comment = 'Проверочный отзыв.';

        $this->post($store, ['rating' => 5, 'comment' => $comment])->assertRedirect(route('login'));
        $customer = $this->user('Customer');
        $this->actingAs($customer)->from($page)->post($store, ['rating' => 6, 'comment' => $comment])
            ->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('reviews', 0);

        $this->post($store, ['rating' => 5, 'comment' => $comment, 'status' => Review::APPROVED])
            ->assertRedirect($page.'#reviews');
        $review = Review::sole();
        $this->assertSame(Review::PENDING, $review->status);
        $approve = route('admin.reviews.update', $review);
        $this->patch($approve, ['status' => Review::APPROVED])->assertForbidden();
        $this->post($store, ['rating' => 2, 'comment' => 'Дубликат'])->assertSessionHasErrors('review');
        $this->assertDatabaseCount('reviews', 1);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->get($page)->assertOk()->assertSee('Оценок пока нет')->assertDontSee($comment);

        $moderator = $this->user('Moderator', 'manager');
        $this->actingAs($moderator)->get(route('admin.reviews.index'))->assertOk()->assertSee($comment);
        $this->patch($approve, ['status' => Review::APPROVED])->assertRedirect(route('admin.reviews.index'));
        $this->assertNotNull($review->fresh()->moderated_at);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->get($page)->assertOk()->assertSee('5,0 из 5')->assertSee($comment);

        $this->actingAs($this->user('Administrator', 'admin'))
            ->patch($approve, ['status' => Review::REJECTED])->assertRedirect(route('admin.reviews.index'));
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->get($page)->assertOk()->assertSee('Оценок пока нет')->assertDontSee($comment);
    }

    private function user(string $login, string $role = 'user'): User
    {
        $user = User::create([
            'login' => $login, 'email' => strtolower($login).'@example.com',
            'first_name' => 'Иван', 'last_name' => 'Иванов', 'patronymic' => 'Иванович',
            'phone' => '79990001122', 'password' => 'TestPassword!42',
        ]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
