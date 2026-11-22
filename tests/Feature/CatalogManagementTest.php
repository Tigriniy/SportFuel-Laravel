<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_management_restricts_reads_and_writes_by_role(): void
    {
        $url = route('admin.products.index');
        $this->get($url)->assertRedirect(route('login'));
        $user = $this->createUser();
        $this->actingAs($user)->get($url)->assertForbidden();
        $this->post(route('admin.products.store'))->assertForbidden();

        $user->forceFill(['role' => 'manager'])->save();
        $this->get($url)->assertOk();

        $user->forceFill(['role' => 'admin', 'is_blocked' => true])->save();
        $this->get($url)->assertForbidden();
    }

    public function test_catalog_crud_updates_the_public_list_and_protects_used_references(): void
    {
        $this->actingAs($this->createUser('admin'));
        Storage::fake('public');

        foreach (['categories' => Category::class, 'brands' => Brand::class] as $kind => $model) {
            $this->get(route('admin.references.index', $kind))->assertOk();
            $this->post(route('admin.references.store', $kind), ['name' => $kind])
                ->assertRedirect(route('admin.references.index', $kind))->assertSessionHasNoErrors();
            $reference = $model::sole();
            $this->get(route('admin.references.edit', [$kind, $reference->id]))->assertOk();
            $this->patch(route('admin.references.update', [$kind, $reference->id]), ['name' => $kind.' edited'])
                ->assertSessionHasNoErrors();
            $this->assertSame($kind.' edited', $reference->fresh()->name);
        }

        $category = Category::sole();
        $brand = Brand::sole();
        $this->get(route('admin.products.create'))->assertOk();

        $data = [
            'name' => 'Проверочный товар',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => '199.90',
            'stock' => 7,
            'is_active' => '1',
        ];

        $this->post(route('admin.products.store'), array_replace($data, ['price' => '-1']))
            ->assertSessionHasErrors('price');

        $cover = UploadedFile::fake()->createWithContent('cover.png', file_get_contents(
            public_path('assets/images/product/Maxler_creatine1.png')
        ));
        $this->post(route('admin.products.store'), $data + ['cover' => $cover])
            ->assertRedirect(route('admin.products.index'))->assertSessionHasNoErrors();
        $product = Product::sole();
        $upload = substr($product->cover->path, strlen('storage/'));
        Storage::disk('public')->assertExists($upload);
        $this->get(route('catalog'))->assertOk()->assertSee($product->name)->assertSee('199,90');
        $this->get(route('admin.products.edit', $product))->assertOk()->assertSee($product->name);

        foreach (['categories' => $category, 'brands' => $brand] as $kind => $reference) {
            $this->delete(route('admin.references.destroy', [$kind, $reference->id]))->assertSessionHasErrors();
            $this->assertNotNull($reference->fresh());
        }

        $this->patch(route('admin.products.update', $product), array_replace($data, [
            'name' => 'Проверочный товар с другим названием',
            'is_active' => '0',
        ]))->assertSessionHasNoErrors();
        $this->assertSame('Проверочный товар с другим названием', $product->fresh()->name);
        $this->get(route('catalog'))->assertDontSee($product->fresh()->name);

        $this->delete(route('admin.products.destroy', $product))->assertSessionHasNoErrors();
        $this->assertModelMissing($product);
        $this->assertDatabaseCount('product_images', 0);
        Storage::disk('public')->assertMissing($upload);

        foreach (['categories' => $category, 'brands' => $brand] as $kind => $reference) {
            $this->delete(route('admin.references.destroy', [$kind, $reference->id]))->assertSessionHasNoErrors();
            $this->assertModelMissing($reference);
        }
    }

    private function createUser(string $role = 'user'): User
    {
        $user = User::create([
            'login' => 'TestUser',
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'patronymic' => 'Иванович',
            'phone' => '79990001122',
            'email' => 'test@example.com',
            'password' => 'OldPassword!42',
        ]);

        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
