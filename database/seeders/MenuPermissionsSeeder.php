<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MenuPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'menu_categories.view',
            'menu_categories.create',
            'menu_categories.edit',
            'menu_categories.delete',
            'menu_categories.restore',

            'menu_attributes.view',
            'menu_attributes.create',
            'menu_attributes.edit',
            'menu_attributes.delete',
            'menu_attributes.restore',

            'menu_addons.view',
            'menu_addons.create',
            'menu_addons.edit',
            'menu_addons.delete',
            'menu_addons.restore',

            'menu_products.view',
            'menu_products.create',
            'menu_products.edit',
            'menu_products.delete',
            'menu_products.restore',

            'store_qr_codes.view',
            'store_qr_codes.download',

            'store_menu.view',
            'store_menu.manage',

            'menu_promotions.view',
            'menu_promotions.create',
            'menu_promotions.edit',
            'menu_promotions.delete',
            'menu_promotions.restore',

            'menu_orders.view',
            'menu_orders.create',
            'menu_orders.edit',
            'menu_orders.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }
}
