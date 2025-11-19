<?php

namespace App\Console\Commands;

use App\Models\TaskTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportTaskTemplatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:task-templates {file? : Path to CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import task templates from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file') ?? 'storage/app/task_templates.csv';
        
        // التحقق من وجود الملف
        if (!file_exists($filePath)) {
            $this->error("❌ الملف غير موجود: {$filePath}");
            $this->info("💡 تأكد من وجود الملف في المسار المحدد أو استخدم:");
            $this->info("php artisan import:task-templates /path/to/your/file.csv");
            return 1;
        }

        $this->info("📁 بدء استيراد قوالب المهام من: {$filePath}");
        
        try {
            $imported = $this->importTemplates($filePath);
            $this->info("✅ تم استيراد {$imported} قالب بنجاح!");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ حدث خطأ أثناء الاستيراد: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * استيراد القوالب من ملف CSV
     */
    private function importTemplates(string $filePath): int
    {
        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception("لا يمكن فتح الملف");
        }

        $imported = 0;
        $skipped = 0;
        $currentDepartment = null;
        $lineNumber = 0;

        while (($data = fgetcsv($file)) !== false) {
            $lineNumber++;
            
            // تخطي الصف الأول (العناوين)
            if ($lineNumber === 1) {
                continue;
            }

            // التحقق من وجود قسم جديد
            if (!empty($data[0]) && empty($data[1]) && empty($data[2])) {
                $currentDepartment = trim($data[0]);
                $this->info("📂 قسم: {$currentDepartment}");
                continue;
            }

            // تخطي الصفوف الفارغة
            if (empty($data[0]) && empty($data[1]) && empty($data[2])) {
                continue;
            }

            // تخطي صف العناوين الفرعية
            if (isset($data[1]) && $data[1] === 'action_id') {
                continue;
            }

            // معالجة بيانات القالب
            if (isset($data[1]) && isset($data[2]) && isset($data[3])) {
                $actionId = trim($data[1]);
                $actionName = trim($data[2]);
                $actionWait = (float) trim($data[3]);

                if (empty($actionName)) {
                    continue;
                }

                // التحقق من عدم وجود القالب مسبقاً
                $existing = TaskTemplate::where('name', $actionName)
                                      ->where('department', $currentDepartment)
                                      ->first();

                if ($existing) {
                    $this->warn("⚠️  تم تخطي القالب المكرر: {$actionName} ({$currentDepartment})");
                    $skipped++;
                    continue;
                }

                // إنشاء القالب الجديد
                TaskTemplate::create([
                    'name' => $actionName,
                    'estimated_time' => $actionWait,
                    'department' => $currentDepartment,
                    'description' => "قالب مستورد من CSV - Action ID: {$actionId}",
                    'is_active' => true,
                ]);

                $this->line("✅ تم إنشاء: {$actionName} ({$actionWait} ساعة)");
                $imported++;
            }
        }

        fclose($file);

        $this->info("\n📊 ملخص الاستيراد:");
        $this->info("✅ مستورد: {$imported}");
        $this->info("⚠️  متخطى: {$skipped}");
        $this->info("📁 إجمالي الصفوف المعالجة: {$lineNumber}");

        return $imported;
    }
}
