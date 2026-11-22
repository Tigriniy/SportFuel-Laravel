<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_combines_categories_brands_and_inclusive_price_range(): void
    {
        $protein = Category::create(['name' => 'Протеин', 'slug' => 'protein']);
        $creatine = Category::create(['name' => 'Креатин', 'slug' => 'creatine']);
        $vitamins = Category::create(['name' => 'Витамины', 'slug' => 'vitamins']);
        $brand = Brand::create(['name' => 'SportFuel', 'slug' => 'sportfuel']);
        $other = Brand::create(['name' => 'Other', 'slug' => 'other']);

        $this->product($protein, $brand, 'Fuel Дорогой', 800);
        $this->product($creatine, $brand, 'Fuel Дешёвый', 300);
        $this->product($vitamins, $brand, 'Fuel Другая категория', 500);
        $this->product($protein, $other, 'Fuel Другой бренд', 500);
        $this->product($protein, $brand, 'Fuel Вне цены', 801);
        $this->product($protein, $brand, 'Fuel Скрытый', 500, false);
        $this->product($protein, $brand, 'Другое название', 500);

        $this->get(route('catalog', [
            'q' => 'fuel',
            'category' => [$protein->id, $creatine->id],
            'brand' => [$brand->id],
            'min_price' => 300,
            'max_price' => 800,
            'sort' => 'price_asc',
        ]))->assertOk()
            ->assertSeeInOrder(['Fuel Дешёвый', 'Fuel Дорогой'])
            ->assertSee('Найдено товаров: 2')
            ->assertDontSee('Fuel Скрытый')
            ->assertDontSee('Fuel Вне цены')
            ->assertDontSee('Fuel Другая категория')
            ->assertDontSee('Fuel Другой бренд')
            ->assertDontSee('Другое название');

        $this->get(route('catalog', ['q' => 'Fuel', 'brand' => [$brand->id, $other->id]]))
            ->assertOk()
            ->assertSee('Fuel Другой бренд');
    }

    public function test_search_treats_wildcards_as_literal_text_and_preserves_filters_in_pagination(): void
    {
        $category = Category::create(['name' => 'Протеин', 'slug' => 'protein']);
        $brand = Brand::create(['name' => 'SportFuel', 'slug' => 'sportfuel']);

        for ($i = 1; $i <= 17; $i++) {
            $this->product($category, $brand, 'Смесь 25%_'.$i, $i);
        }
        $this->product($category, $brand, 'Смесь 25XY', 999);

        $filters = ['q' => '25%_', 'category' => [$category->id], 'min_price' => 0, 'sort' => 'price_desc'];
        $response = $this->get(route('catalog', $filters))
            ->assertOk()
            ->assertSeeInOrder(['Смесь 25%_17', 'Смесь 25%_16'])
            ->assertSee('Найдено товаров: 17')
            ->assertDontSee('Смесь 25XY');

        $url = $response->viewData('products')->nextPageUrl();
        $this->assertNotNull($url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertEquals([
            'q' => '25%_', 'category' => [(string) $category->id],
            'min_price' => '0', 'sort' => 'price_desc', 'page' => '2',
        ], $query);
        $this->get($url)->assertOk()->assertSee('Смесь 25%_1')->assertDontSee('Смесь 25%_17');
    }

    public function test_bad_filters_redirect_to_a_valid_catalog_and_search_forms_are_available(): void
    {
        $this->get(route('catalog', ['q' => ['invalid']]))
            ->assertRedirect(route('catalog'))
            ->assertSessionHasErrors('q');
        $this->get(route('catalog', ['min_price' => 800, 'max_price' => 100, 'q' => 'BCAA']))
            ->assertRedirect(route('catalog', ['q' => 'BCAA']))
            ->assertSessionHasErrors('min_price');

        foreach (['/', '/cart.html'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee('action="'.route('catalog').'"', false)
                ->assertSee('name="q"', false)
                ->assertSee('header__mobile-search');
        }
    }

    private function product(Category $category, Brand $brand, string $name, int $price, bool $active = true): void
    {
        Product::create([
            'name' => $name,
            'slug' => fake()->unique()->uuid(),
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => $price,
            'stock' => 1,
            'is_active' => $active,
        ]);
    }
}
