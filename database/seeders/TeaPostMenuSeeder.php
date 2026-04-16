<?php

namespace Database\Seeders;

use App\Models\MenuAddon;
use App\Models\MenuAttribute;
use App\Models\MenuAttributeValue;
use App\Models\MenuCategory;
use App\Models\MenuProduct;
use App\Models\MenuProductAddon;
use App\Models\MenuProductAttribute;
use App\Models\Store;
use App\Models\StoreMenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeaPostMenuSeeder extends Seeder
{
    public function run()
    {
        $payload = [
            'outlet' => 'Tea Post Navrangpura CG Road',
            'categories' => [
                [
                    'name' => 'Party Combo',
                    'items' => [
                        [
                            'name' => 'Chai Party Combo',
                            'price' => 594,
                            'description' => '10 pcs of Samosa or Kachori served with hot flavoured tea (500ml)',
                            'attributes' => [
                                ['name' => 'Snack Choice', 'options' => ['Samosa (10 pcs)', 'Kachori (10 pcs)']],
                                ['name' => 'Tea Type', 'options' => ['Indian Masala Chai', 'Elaichi Chai', 'Pudina Elaichi Tea', 'Ginger Chai']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Strong Tea', 'price' => 5],
                                ['name' => 'Extra Sugar', 'price' => 0],
                            ],
                        ],
                        [
                            'name' => 'Samosa Tub',
                            'price' => 180,
                            'description' => 'Mini samosas (10 pcs)',
                            'attributes' => [],
                            'addons' => [
                                ['name' => 'Green Chutney (Extra)', 'price' => 10],
                            ],
                        ],
                        [
                            'name' => 'Kachori Tub',
                            'price' => 190,
                            'description' => 'Mini kachoris (10 pcs)',
                            'attributes' => [],
                            'addons' => [
                                ['name' => 'Sweet Chutney (Extra)', 'price' => 10],
                            ],
                        ],
                        [
                            'name' => 'Tea + Snack Mini Combo',
                            'price' => 129,
                            'description' => '2 cups tea with 4 pcs mixed snack',
                            'attributes' => [
                                ['name' => 'Tea Type', 'options' => ['Indian Masala Chai', 'Ginger Chai']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Snack Piece', 'price' => 15],
                            ],
                        ],
                        [
                            'name' => 'Family Tea Combo',
                            'price' => 349,
                            'description' => '1L flavoured tea with assorted light snacks',
                            'attributes' => [
                                ['name' => 'Tea Type', 'options' => ['Elaichi Chai', 'Pudina Elaichi Tea', 'Indian Masala Chai']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Tea Cup Set', 'price' => 25],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Tea > Chai (With Milk)',
                    'items' => [
                        [
                            'name' => 'Indian Masala Chai (2 Cups)',
                            'price' => 140,
                            'description' => 'Sweet & aromatic chai with spices',
                            'attributes' => [
                                ['name' => 'Size', 'options' => ['Regular', 'Large']],
                                ['name' => 'Sugar Level', 'options' => ['Normal', 'Less Sugar', 'No Sugar']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Strong', 'price' => 5],
                            ],
                        ],
                        [
                            'name' => 'Elaichi Chai (2 Cups)',
                            'price' => 145,
                            'description' => 'Tea flavored with elaichi',
                            'attributes' => [
                                ['name' => 'Size', 'options' => ['Regular', 'Large']],
                                ['name' => 'Sugar Level', 'options' => ['Normal', 'Less Sugar', 'No Sugar']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Strong', 'price' => 5],
                            ],
                        ],
                        [
                            'name' => 'Pudina Elaichi Tea (2 Cups)',
                            'price' => 145,
                            'description' => 'Mint + elaichi flavored milk tea',
                            'attributes' => [
                                ['name' => 'Size', 'options' => ['Regular', 'Large']],
                                ['name' => 'Sugar Level', 'options' => ['Normal', 'Less Sugar', 'No Sugar']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Strong', 'price' => 5],
                            ],
                        ],
                        [
                            'name' => 'Ginger Chai (2 Cups)',
                            'price' => 150,
                            'description' => 'Ginger flavored tea',
                            'attributes' => [
                                ['name' => 'Size', 'options' => ['Regular', 'Large']],
                                ['name' => 'Sugar Level', 'options' => ['Normal', 'Less Sugar', 'No Sugar']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Ginger Shot', 'price' => 10],
                            ],
                        ],
                        [
                            'name' => 'Cutting Chai (Single)',
                            'price' => 35,
                            'description' => 'Classic cutting chai',
                            'attributes' => [
                                ['name' => 'Sugar Level', 'options' => ['Normal', 'Less Sugar', 'No Sugar']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Strong', 'price' => 5],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Sandwich',
                    'items' => [
                        [
                            'name' => 'Tandoori Paneer Sandwich (3 Slice)',
                            'price' => 215,
                            'description' => 'Grilled sandwich with paneer, tandoori mayo & veggies',
                            'attributes' => [
                                ['name' => 'Bread Type', 'options' => ['White', 'Brown']],
                                ['name' => 'Spice Level', 'options' => ['Mild', 'Medium', 'Spicy']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Cheese', 'price' => 20],
                                ['name' => 'Extra Butter', 'price' => 10],
                            ],
                        ],
                        [
                            'name' => 'Veg Sandwich',
                            'price' => 140,
                            'description' => 'Classic vegetable sandwich',
                            'attributes' => [
                                ['name' => 'Bread Type', 'options' => ['White', 'Brown']],
                                ['name' => 'Toast Level', 'options' => ['Soft', 'Crispy']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Cheese', 'price' => 20],
                                ['name' => 'Extra Veggies', 'price' => 15],
                            ],
                        ],
                        [
                            'name' => 'Cheese Sandwich',
                            'price' => 165,
                            'description' => 'Cheese loaded sandwich',
                            'attributes' => [
                                ['name' => 'Bread Type', 'options' => ['White', 'Brown']],
                                ['name' => 'Toast Level', 'options' => ['Soft', 'Crispy']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Cheese Slice', 'price' => 25],
                                ['name' => 'Jalapeno', 'price' => 10],
                            ],
                        ],
                        [
                            'name' => 'Masala Sandwich',
                            'price' => 155,
                            'description' => 'Spicy Indian style sandwich',
                            'attributes' => [
                                ['name' => 'Bread Type', 'options' => ['White', 'Brown']],
                                ['name' => 'Spice Level', 'options' => ['Medium', 'Spicy']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Chutney', 'price' => 10],
                                ['name' => 'Extra Butter', 'price' => 10],
                            ],
                        ],
                        [
                            'name' => 'Club Sandwich',
                            'price' => 225,
                            'description' => 'Multi-layer sandwich with rich filling',
                            'attributes' => [
                                ['name' => 'Bread Type', 'options' => ['White', 'Brown']],
                                ['name' => 'Cut Style', 'options' => ['Triangle', 'Rectangle']],
                            ],
                            'addons' => [
                                ['name' => 'Extra Mayo', 'price' => 15],
                                ['name' => 'Extra Cheese', 'price' => 20],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        DB::transaction(function () use ($payload) {
            $store = Store::loc()
                ->where('name', $payload['outlet'])
                ->orWhere('name', 'like', '%' . $payload['outlet'] . '%')
                ->first();

            foreach ($payload['categories'] as $catIndex => $catData) {
                $category = MenuCategory::withTrashed()->firstOrNew(['name' => $catData['name']]);
                if ($category->trashed()) {
                    $category->restore();
                }
                $category->fill([
                    'slug' => $this->uniqueSlug('menu_categories', $catData['name'], $category->id),
                    'description' => $category->description,
                    'status' => 1,
                    'ordering' => $catIndex,
                ])->save();

                foreach ($catData['items'] as $itemIndex => $itemData) {
                    $product = MenuProduct::withTrashed()->firstOrNew([
                        'category_id' => $category->id,
                        'name' => $itemData['name'],
                    ]);
                    if ($product->trashed()) {
                        $product->restore();
                    }
                    $price = is_null($itemData['price']) ? 0 : (float) $itemData['price'];
                    $product->fill([
                        'slug' => $this->uniqueSlug('menu_products', $itemData['name'], $product->id),
                        'description' => $itemData['description'] ?? null,
                        'base_price' => $price,
                        'status' => 1,
                        'ordering' => $itemIndex,
                    ])->save();

                    MenuProductAttribute::where('product_id', $product->id)->forceDelete();
                    MenuProductAddon::where('product_id', $product->id)->forceDelete();

                    foreach ($itemData['attributes'] as $attrData) {
                        $attribute = MenuAttribute::withTrashed()->firstOrNew(['name' => $attrData['name']]);
                        if ($attribute->trashed()) {
                            $attribute->restore();
                        }
                        $attribute->fill(['status' => 1])->save();

                        foreach (($attrData['options'] ?? []) as $optIndex => $optionLabel) {
                            $value = MenuAttributeValue::withTrashed()->firstOrNew([
                                'attribute_id' => $attribute->id,
                                'value' => $optionLabel,
                            ]);
                            if ($value->trashed()) {
                                $value->restore();
                            }
                            $value->fill([
                                'extra_price' => 0,
                                'ordering' => $optIndex,
                            ])->save();

                            MenuProductAttribute::create([
                                'product_id' => $product->id,
                                'attribute_id' => $attribute->id,
                                'attribute_value_id' => $value->id,
                                'price_override' => null,
                                'is_available' => 1,
                                'is_default' => $optIndex === 0 ? 1 : 0,
                            ]);
                        }
                    }

                    foreach ($itemData['addons'] as $addonData) {
                        $addon = MenuAddon::withTrashed()->firstOrNew(['name' => $addonData['name']]);
                        if ($addon->trashed()) {
                            $addon->restore();
                        }
                        $addon->fill([
                            'price' => (float) ($addonData['price'] ?? 0),
                            'description' => $addon->description,
                            'status' => 1,
                        ])->save();

                        MenuProductAddon::create([
                            'product_id' => $product->id,
                            'addon_id' => $addon->id,
                            'price_override' => null,
                            'is_available' => 1,
                            'is_default' => 0,
                        ]);
                    }

                    if ($store) {
                        StoreMenuItem::withTrashed()->updateOrCreate(
                            [
                                'store_id' => $store->id,
                                'category_id' => $category->id,
                                'product_id' => $product->id,
                            ],
                            ['is_active' => 1, 'deleted_at' => null]
                        );
                    }
                }
            }
        });
    }

    private function uniqueSlug(string $table, string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'item';
        $slug = $base;
        $i = 1;
        while (DB::table($table)
            ->where('slug', $slug)
            ->when($ignoreId, function ($q) use ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            })
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}

