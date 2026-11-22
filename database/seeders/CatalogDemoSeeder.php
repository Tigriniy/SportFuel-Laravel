<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogDemoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['demo-1', 'Креатин, 150 гр.', 'Креатин', 'Maxler', 1190, 'assets/images/product/Maxler_creatine1.png'],
            ['demo-2', 'BCAA, 40 таб.', 'BCAA', 'OptimumNutrition', 3390, 'assets/images/product/OptimumNutrition_BCAA1.png'],
            ['demo-3', 'Жиросжигатель, 60 таб.', 'Жиросжигатели', 'BeFirst', 1280, 'assets/images/product/BeFirst_жиросжигатель1.png'],
            ['demo-4', 'GAINER, 1000 гр.', 'Гейнеры', 'BeFirst', 1400, 'assets/images/product/BeFirst_Gainer(1000g)1.png'],
            ['demo-5', 'Креатин, 200 гр.', 'Креатин', 'BeFirst', 1360, 'assets/images/product/BeFirst_Creatine2.png'],
            ['demo-6', 'BCAA, 225 гр.', 'BCAA', 'Siberian', 1240, 'assets/images/product/Siberian_BCAA1.png'],
            ['demo-7', 'Протеин, 908 гр.', 'Протеин', 'USN', 1280, 'assets/images/product/USN_Протеин.png'],
            ['demo-8', 'Витамины, 90 таб.', 'Витамины', 'Maxler', 2160, 'assets/images/product/MaxlerVitamen_Витамины1.png'],
        ];

        DB::transaction(function () use ($items): void {
            foreach ($items as [$slug, $name, $categoryName, $brandName, $price, $path]) {
                if (Product::where('slug', $slug)->exists()) {
                    continue;
                }

                $category = Category::firstOrCreate(['name' => $categoryName], ['slug' => Str::uuid()->toString()]);
                $brand = Brand::firstOrCreate(['name' => $brandName], ['slug' => Str::uuid()->toString()]);

                $product = Product::create([
                    'slug' => $slug,
                    'name' => $name,
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'price' => $price,
                    'stock' => 0,
                    'is_active' => true,
                ]);

                $product->images()->create(['path' => $path, 'is_primary' => true]);
            }
        });
    }
}
