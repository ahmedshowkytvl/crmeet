<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'إدارة الموارد البشرية',
                'description' => 'قسم مسؤول عن إدارة شؤون الموظفين والتوظيف والتدريب',
                'manager_id' => null, // سيتم تحديثه لاحقاً
            ],
            [
                'name' => 'تقنية المعلومات',
                'description' => 'قسم مسؤول عن الأنظمة التقنية والبرمجيات والشبكات',
                'manager_id' => null,
            ],
            [
                'name' => 'المالية والمحاسبة',
                'description' => 'قسم مسؤول عن الشؤون المالية والمحاسبية للشركة',
                'manager_id' => null,
            ],
            [
                'name' => 'التسويق والمبيعات',
                'description' => 'قسم مسؤول عن التسويق والمبيعات وخدمة العملاء',
                'manager_id' => null,
            ],
            [
                'name' => 'العمليات والإنتاج',
                'description' => 'قسم مسؤول عن العمليات التشغيلية والإنتاج',
                'manager_id' => null,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
