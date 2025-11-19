<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\Asset;
use App\Models\AssetCategoryProperty;
use App\Models\User;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Asset Categories
        $categories = [
            [
                'name' => 'Computers',
                'name_ar' => 'أجهزة الكمبيوتر',
                'description' => 'Desktop computers, laptops, and related equipment',
                'description_ar' => 'أجهزة الكمبيوتر المكتبية والمحمولة والمعدات ذات الصلة',
                'is_active' => true,
            ],
            [
                'name' => 'Office Equipment',
                'name_ar' => 'معدات المكتب',
                'description' => 'Printers, scanners, and office machinery',
                'description_ar' => 'الطابعات والماسحات الضوئية وآلات المكتب',
                'is_active' => true,
            ],
            [
                'name' => 'Furniture',
                'name_ar' => 'الأثاث',
                'description' => 'Desks, chairs, and office furniture',
                'description_ar' => 'المكاتب والكراسي وأثاث المكتب',
                'is_active' => true,
            ],
            [
                'name' => 'Vehicles',
                'name_ar' => 'المركبات',
                'description' => 'Company cars and transportation vehicles',
                'description_ar' => 'سيارات الشركة ومركبات النقل',
                'is_active' => true,
            ],
            [
                'name' => 'Electronics',
                'name_ar' => 'الإلكترونيات',
                'description' => 'Mobile phones, tablets, and electronic devices',
                'description_ar' => 'الهواتف المحمولة والأجهزة اللوحية والأجهزة الإلكترونية',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            AssetCategory::create($categoryData);
        }

        // Create Asset Locations
        $locations = [
            [
                'name' => 'Main Office',
                'name_ar' => 'المكتب الرئيسي',
                'address' => '123 Business Street, Downtown',
                'address_ar' => '123 شارع الأعمال، وسط المدينة',
                'is_active' => true,
            ],
            [
                'name' => 'Warehouse',
                'name_ar' => 'المستودع',
                'address' => '456 Industrial Avenue, Industrial Zone',
                'address_ar' => '456 شارع الصناعة، المنطقة الصناعية',
                'is_active' => true,
            ],
            [
                'name' => 'Branch Office',
                'name_ar' => 'الفرع',
                'address' => '789 Commercial Road, Business District',
                'address_ar' => '789 طريق التجارة، المنطقة التجارية',
                'is_active' => true,
            ],
            [
                'name' => 'Remote Office',
                'name_ar' => 'المكتب البعيد',
                'address' => 'Remote Location',
                'address_ar' => 'موقع بعيد',
                'is_active' => true,
            ],
        ];

        foreach ($locations as $locationData) {
            AssetLocation::create($locationData);
        }

        // Create Category Properties
        $computerCategory = AssetCategory::where('name', 'Computers')->first();
        if ($computerCategory) {
            $computerProperties = [
                [
                    'name' => 'Brand',
                    'name_ar' => 'العلامة التجارية',
                    'type' => 'select',
                    'options' => ['Dell', 'HP', 'Lenovo', 'Apple', 'ASUS', 'Acer'],
                    'is_required' => true,
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Model',
                    'name_ar' => 'الموديل',
                    'type' => 'text',
                    'is_required' => true,
                    'sort_order' => 2,
                ],
                [
                    'name' => 'RAM',
                    'name_ar' => 'الذاكرة العشوائية',
                    'type' => 'select',
                    'options' => ['4GB', '8GB', '16GB', '32GB', '64GB'],
                    'is_required' => true,
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Storage',
                    'name_ar' => 'التخزين',
                    'type' => 'select',
                    'options' => ['128GB SSD', '256GB SSD', '512GB SSD', '1TB HDD', '2TB HDD'],
                    'is_required' => true,
                    'sort_order' => 4,
                ],
                [
                    'name' => 'Operating System',
                    'name_ar' => 'نظام التشغيل',
                    'type' => 'select',
                    'options' => ['Windows 10', 'Windows 11', 'macOS', 'Linux', 'Chrome OS'],
                    'is_required' => true,
                    'sort_order' => 5,
                ],
                [
                    'name' => 'Warranty Active',
                    'name_ar' => 'الضمان نشط',
                    'type' => 'boolean',
                    'is_required' => false,
                    'sort_order' => 6,
                ],
            ];

            foreach ($computerProperties as $propertyData) {
                $propertyData['category_id'] = $computerCategory->id;
                AssetCategoryProperty::create($propertyData);
            }
        }

        $vehicleCategory = AssetCategory::where('name', 'Vehicles')->first();
        if ($vehicleCategory) {
            $vehicleProperties = [
                [
                    'name' => 'Make',
                    'name_ar' => 'الشركة المصنعة',
                    'type' => 'select',
                    'options' => ['Toyota', 'Honda', 'Ford', 'Chevrolet', 'Nissan', 'BMW', 'Mercedes'],
                    'is_required' => true,
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Model',
                    'name_ar' => 'الموديل',
                    'type' => 'text',
                    'is_required' => true,
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Year',
                    'name_ar' => 'السنة',
                    'type' => 'number',
                    'is_required' => true,
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Color',
                    'name_ar' => 'اللون',
                    'type' => 'select',
                    'options' => ['White', 'Black', 'Silver', 'Red', 'Blue', 'Gray', 'Other'],
                    'is_required' => true,
                    'sort_order' => 4,
                ],
                [
                    'name' => 'License Plate',
                    'name_ar' => 'رقم اللوحة',
                    'type' => 'text',
                    'is_required' => true,
                    'sort_order' => 5,
                ],
                [
                    'name' => 'Insurance Expiry',
                    'name_ar' => 'انتهاء التأمين',
                    'type' => 'date',
                    'is_required' => true,
                    'sort_order' => 6,
                ],
            ];

            foreach ($vehicleProperties as $propertyData) {
                $propertyData['category_id'] = $vehicleCategory->id;
                AssetCategoryProperty::create($propertyData);
            }
        }

        // Create Sample Assets
        $users = User::take(5)->get();
        $categories = AssetCategory::all();
        $locations = AssetLocation::all();

        $sampleAssets = [
            [
                'name' => 'Dell OptiPlex 7090',
                'name_ar' => 'ديل أوبتيبليكس 7090',
                'category_id' => $categories->where('name', 'Computers')->first()->id,
                'location_id' => $locations->where('name', 'Main Office')->first()->id,
                'serial_number' => 'DL7090-001',
                'description' => 'Desktop computer for office use',
                'description_ar' => 'جهاز كمبيوتر مكتبي للاستخدام المكتبي',
                'purchase_date' => now()->subMonths(6),
                'warranty_expiry' => now()->addMonths(18),
                'cost' => 1200.00,
                'status' => 'active',
                'assigned_to' => $users->random()->id,
            ],
            [
                'name' => 'HP LaserJet Pro',
                'name_ar' => 'إتش بي ليزر جت برو',
                'category_id' => $categories->where('name', 'Office Equipment')->first()->id,
                'location_id' => $locations->where('name', 'Main Office')->first()->id,
                'serial_number' => 'HP-LJ-002',
                'description' => 'Laser printer for office printing',
                'description_ar' => 'طابعة ليزر للطباعة المكتبية',
                'purchase_date' => now()->subMonths(3),
                'warranty_expiry' => now()->addMonths(9),
                'cost' => 350.00,
                'status' => 'active',
                'assigned_to' => null,
            ],
            [
                'name' => 'Office Chair - Ergonomic',
                'name_ar' => 'كرسي مكتب - مريح',
                'category_id' => $categories->where('name', 'Furniture')->first()->id,
                'location_id' => $locations->where('name', 'Main Office')->first()->id,
                'serial_number' => 'CHAIR-003',
                'description' => 'Ergonomic office chair with lumbar support',
                'description_ar' => 'كرسي مكتب مريح مع دعم أسفل الظهر',
                'purchase_date' => now()->subMonths(12),
                'warranty_expiry' => now()->addMonths(24),
                'cost' => 250.00,
                'status' => 'active',
                'assigned_to' => $users->random()->id,
            ],
            [
                'name' => 'Toyota Camry 2022',
                'name_ar' => 'تويوتا كامري 2022',
                'category_id' => $categories->where('name', 'Vehicles')->first()->id,
                'location_id' => $locations->where('name', 'Warehouse')->first()->id,
                'serial_number' => 'VIN-123456789',
                'description' => 'Company car for business travel',
                'description_ar' => 'سيارة الشركة للسفر التجاري',
                'purchase_date' => now()->subMonths(8),
                'warranty_expiry' => now()->addMonths(36),
                'cost' => 25000.00,
                'status' => 'active',
                'assigned_to' => $users->random()->id,
            ],
            [
                'name' => 'iPhone 14 Pro',
                'name_ar' => 'آيفون 14 برو',
                'category_id' => $categories->where('name', 'Electronics')->first()->id,
                'location_id' => $locations->where('name', 'Main Office')->first()->id,
                'serial_number' => 'IPH14-004',
                'description' => 'Company mobile phone for business use',
                'description_ar' => 'هاتف محمول للشركة للاستخدام التجاري',
                'purchase_date' => now()->subMonths(2),
                'warranty_expiry' => now()->addMonths(10),
                'cost' => 999.00,
                'status' => 'active',
                'assigned_to' => $users->random()->id,
            ],
            [
                'name' => 'Dell Laptop XPS 13',
                'name_ar' => 'ديل لابتوب إكس بي إس 13',
                'category_id' => $categories->where('name', 'Computers')->first()->id,
                'location_id' => $locations->where('name', 'Branch Office')->first()->id,
                'serial_number' => 'DL-XPS-005',
                'description' => 'Portable laptop for remote work',
                'description_ar' => 'لابتوب محمول للعمل عن بُعد',
                'purchase_date' => now()->subMonths(4),
                'warranty_expiry' => now()->addMonths(20),
                'cost' => 1500.00,
                'status' => 'maintenance',
                'assigned_to' => null,
            ],
            [
                'name' => 'Office Desk - Executive',
                'name_ar' => 'مكتب تنفيذي',
                'category_id' => $categories->where('name', 'Furniture')->first()->id,
                'location_id' => $locations->where('name', 'Main Office')->first()->id,
                'serial_number' => 'DESK-006',
                'description' => 'Large executive desk with drawers',
                'description_ar' => 'مكتب تنفيذي كبير مع أدراج',
                'purchase_date' => now()->subMonths(18),
                'warranty_expiry' => now()->addMonths(6),
                'cost' => 800.00,
                'status' => 'active',
                'assigned_to' => $users->random()->id,
            ],
            [
                'name' => 'Canon Scanner',
                'name_ar' => 'ماسح كانون',
                'category_id' => $categories->where('name', 'Office Equipment')->first()->id,
                'location_id' => $locations->where('name', 'Main Office')->first()->id,
                'serial_number' => 'CAN-SC-007',
                'description' => 'High-speed document scanner',
                'description_ar' => 'ماسح مستندات عالي السرعة',
                'purchase_date' => now()->subMonths(9),
                'warranty_expiry' => now()->addMonths(15),
                'cost' => 450.00,
                'status' => 'retired',
                'assigned_to' => null,
            ],
        ];

        foreach ($sampleAssets as $assetData) {
            // Generate asset code and barcode
            $assetCode = 'AST-' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $barcode = 'BC' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            
            $assetData['asset_code'] = $assetCode;
            $assetData['barcode'] = $barcode;
            
            $asset = Asset::create($assetData);
            
            // Add property values for computers
            if ($asset->category->name === 'Computers') {
                $properties = $asset->category->properties;
                foreach ($properties as $property) {
                    $value = $this->getSamplePropertyValue($property);
                    if ($value) {
                        $asset->setPropertyValue($property->id, $value);
                    }
                }
            }
            
            // Add property values for vehicles
            if ($asset->category->name === 'Vehicles') {
                $properties = $asset->category->properties;
                foreach ($properties as $property) {
                    $value = $this->getSamplePropertyValue($property);
                    if ($value) {
                        $asset->setPropertyValue($property->id, $value);
                    }
                }
            }
        }
    }

    /**
     * Get sample property value based on property type
     */
    private function getSamplePropertyValue($property)
    {
        switch ($property->name) {
            case 'Brand':
                return $property->options[array_rand($property->options)];
            case 'Model':
                return 'Model ' . rand(100, 999);
            case 'RAM':
                return $property->options[array_rand($property->options)];
            case 'Storage':
                return $property->options[array_rand($property->options)];
            case 'Operating System':
                return $property->options[array_rand($property->options)];
            case 'Warranty Active':
                return rand(0, 1);
            case 'Make':
                return $property->options[array_rand($property->options)];
            case 'Year':
                return rand(2018, 2024);
            case 'Color':
                return $property->options[array_rand($property->options)];
            case 'License Plate':
                return 'ABC-' . rand(1000, 9999);
            case 'Insurance Expiry':
                return now()->addMonths(rand(1, 12))->format('Y-m-d');
            default:
                return null;
        }
    }
}

