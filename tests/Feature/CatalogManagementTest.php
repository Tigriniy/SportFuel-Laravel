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
        $this->post(route('admin.products.store'), $data + ['photos' => [$cover]])
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

    public function test_product_page_and_gallery_use_saved_catalog_data(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Протеин', 'slug' => 'protein']);
        $brand = Brand::create(['name' => 'SportFuel', 'slug' => 'sportfuel']);
        $data = [
            'name' => 'Тестовый протеин',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => '500.00',
            'stock' => 8,
            'is_active' => '1',
        ];
        $product = Product::create($data + [
            'slug' => 'test-protein',
            'specs' => ['Белок: 20 г', 'Растворяется в воде'],
        ]);
        $related = Product::create(array_replace($data, ['slug' => 'related', 'name' => 'Другой протеин']));
        $hidden = Product::create(array_replace($data, [
            'slug' => 'hidden',
            'name' => 'Скрытый товар',
            'is_active' => '0',
        ]));
        $url = route('products.show', $product->slug);

        $this->get(route('catalog'))->assertOk()->assertSee($url);
        $this->get($url)->assertOk()->assertSee('Белок')->assertSee('20 г')->assertSee('Растворяется в воде');
        $this->get(route('products.show', $hidden->slug))->assertNotFound();

        $this->actingAs($this->createUser('manager'))
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Растворяется в воде');

        $png = file_get_contents(public_path('assets/images/product/Maxler_creatine1.png'));
        $details = [
            'description' => 'Описание протеина',
            'composition' => 'Сывороточный белок',
            'specs' => [['name' => 'Белок на порцию', 'value' => '25 г']],
            'variants' => [['title' => '500 г', 'price' => '650.00', 'stock' => 2]],
            'related_ids' => [$related->id, $hidden->id],
        ];
        $this->patch(route('admin.products.update', $product), array_replace($data, [
            'specs' => [['value' => 'Требуется название']],
        ]))->assertSessionHasErrors('specs.0.name');

        $this->patch(route('admin.products.update', $product), $data + $details + [
            'photos' => [
                UploadedFile::fake()->createWithContent('front.png', $png),
                UploadedFile::fake()->createWithContent('back.png', $png),
            ],
        ])->assertSessionHasNoErrors();
        $photos = $product->images()->orderBy('sort_order')->get();
        $this->assertCount(2, $photos);
        $this->assertSame($photos[0]->id, $product->cover()->first()->id);
        $variant = $product->variants()->sole();
        $details['variants'][0]['id'] = $variant->id;
        $this->get($url)->assertOk()
            ->assertSee('Описание протеина')->assertSee('Сывороточный белок')
            ->assertSee('Белок на порцию')->assertSee('25 г')->assertSee('500 г')->assertSee('650,00')
            ->assertSee('Другой протеин')->assertDontSee('Скрытый товар')
            ->assertSee('Показать фото 2');

        $this->patch(route('admin.products.update', $product), $data + $details + [
            'cover_id' => $photos[1]->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($photos[1]->id, $product->cover()->first()->id);
        $this->get(route('catalog'))->assertSee($photos[1]->path)->assertDontSee($photos[0]->path);
        $this->assertSame($variant->sku, $product->variants()->sole()->sku);

        $this->patch(route('admin.products.update', $product), $data + $details + [
            'cover_id' => $photos[1]->id,
            'remove_images' => [$photos[1]->id],
        ])->assertSessionHasNoErrors();
        $this->assertSame($photos[0]->id, $product->cover()->first()->id);
        Storage::disk('public')->assertMissing(substr($photos[1]->path, strlen('storage/')));
        $this->get($url)->assertDontSee('Показать фото 1');
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
