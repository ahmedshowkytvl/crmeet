<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactCategory;

class ContactCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'مطابع',
                'name_en' => 'Printers',
                'description' => 'مقدمي خدمات الطباعة والنشر',
                'description_en' => 'Printing and publishing service providers',
                'color' => '#e74c3c',
                'icon' => 'fas fa-print',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'موردين',
                'name_en' => 'Suppliers',
                'description' => 'موردي المواد والخدمات',
                'description_en' => 'Material and service suppliers',
                'color' => '#f39c12',
                'icon' => 'fas fa-truck',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'عملاء',
                'name_en' => 'Clients',
                'description' => 'عملاء الشركة',
                'description_en' => 'Company clients',
                'color' => '#27ae60',
                'icon' => 'fas fa-handshake',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'شركاء',
                'name_en' => 'Partners',
                'description' => 'الشركاء التجاريين',
                'description_en' => 'Business partners',
                'color' => '#8e44ad',
                'icon' => 'fas fa-handshake',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'مقاولين',
                'name_en' => 'Contractors',
                'description' => 'مقاولي البناء والصيانة',
                'description_en' => 'Construction and maintenance contractors',
                'color' => '#34495e',
                'icon' => 'fas fa-hammer',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'مستشارين',
                'name_en' => 'Consultants',
                'description' => 'المستشارين القانونيين والماليين',
                'description_en' => 'Legal and financial consultants',
                'color' => '#16a085',
                'icon' => 'fas fa-user-tie',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'أخرى',
                'name_en' => 'Other',
                'description' => 'تصنيفات أخرى',
                'description_en' => 'Other categories',
                'color' => '#95a5a6',
                'icon' => 'fas fa-tag',
                'is_active' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($categories as $category) {
            ContactCategory::create($category);
        }
    }
}
