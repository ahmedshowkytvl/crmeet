<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Department;

class AssignDepartmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'departments:assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign employees to departments from CSV data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Department Assignment Script ===');
        
        // Department mapping
        $departmentMapping = [
            'Accounts' => 'Accounts',
            'BTC - Sales' => 'BTC - Sales',
            'Commercial' => 'Commercial',
            'Operation' => 'Operation',
            'IT' => 'IT',
            'Admin' => 'Admin',
            'HR' => 'HR',
            'Traffic' => 'Traffic',
            'Contracting Egypt' => 'Contracting Egypt',
            'Contracting Middle East' => 'Contracting Middle East',
            'Contracting International' => 'Contracting International',
            'Internet' => 'Internet',
            'Marketing' => 'Marketing'
        ];

        // Employee to department assignments from CSV
        $assignments = [
            '981' => 'Accounts', // عبد الحميد محمد عبد الحميد
            '7' => 'Accounts', // حنان محمد علي إبراهيم
            '208' => 'Accounts', // عماد سعد السيد الرفاعي
            '206' => 'Accounts', // أحمد علاء علي محمد
            '705' => 'Accounts', // السيد محمد محمد ديف
            '885' => 'Accounts', // محمد كامل صالح عبد العزيز
            '891' => 'Accounts', // ياسمين مراد مختار جابر
            '943' => 'Accounts', // محمد رأفت دسوقي دسوقي
            '1004' => 'Accounts', // عمر عصام حسن أحمد
            '1008' => 'Accounts', // إسلام محمد كامل محمد
            '1016' => 'Accounts', // أبانوب سعد لطفي توفيق
            '947' => 'Accounts', // كريم سعيد حسن محمد
            '960' => 'Accounts', // محمد أشرف الدسوقي طلبة
            '726' => 'BTC - Sales', // أشرف شافعي محمد محمود
            '181' => 'BTC - Sales', // أحمد ماهر سعد فهمي
            '872' => 'BTC - Sales', // أحمد محمد أحمد ذيب
            '59' => 'Commercial', // هبة محمد عزت حال
            '173' => 'Operation', // أحمد السيد عبد الرحيم محمد
            '1006' => 'Operation', // إسلام إيهاب أحمد محمد
            '1020' => 'Operation', // أحمد حمدي عبد الحميد
            '988' => 'Operation', // أميرة حمدي السيد أحمد
            '989' => 'Operation', // فيولا فايز عوض جرجس
            '998' => 'Operation', // هشام طلعت حسين حسن
            '1000' => 'Operation', // هدير مصطفى أحمد المقدم
            '1003' => 'Operation', // آية الله أشرف محمد العوادي
            '1012' => 'Operation', // شريف إبراهيم عباس
            '1014' => 'Operation', // ندا مجدي إبراهيم عبد الله
            '16' => 'IT', // محمد أنور عوض بيومي
            '26' => 'IT', // رامي سيد علي حسن
            '896' => 'IT', // سيد خليفة سيد خليفة
            '970' => 'IT', // بركات رمضان بركات بيومي
            '714' => 'Admin', // عبد الرحمن محمد السيد إسماعيل
            '920' => 'Admin', // مصطفى مجدي عبد الحميد الجرب
            '976' => 'Admin', // أحمد جمعة رياض عبد الحليم
            '870' => 'Admin', // آمال صالح فرجالي صلاح
            '871' => 'Admin', // نهى فتحي السيد جاد
            '49' => 'Admin', // محمد محمود إبراهيم عبد الحميد
            '816' => 'Admin', // أيمن أشرف فاروق لبيب
            '841' => 'Admin', // وليد خلف حسن عبد العزيز
            '156' => 'HR', // خالد أحمد محمد
            '3' => 'HR', // رانيا عبد المحسن محمود عبد المحسن محمود
            '1019' => 'HR', // زياد مصطفى علي
            '1022' => 'HR', // لمياء حسين علي
            '12' => 'Traffic', // جمال مصلحي السيد زلط
            '407' => 'Traffic', // محمد مرسي محمد مرسي
            '786' => 'Traffic', // أحمد حسين مدبولي سيد
            '35' => 'Contracting Egypt', // وفاء محمد نجيب عثمان
            '916' => 'Contracting Egypt', // يارا أحمد عبد الرب النبوي عبد الرحمن
            '892' => 'Contracting Egypt', // نيرة أحمد مبروك حامد
            '1010' => 'Contracting Egypt', // عبد الرحمن محمد محمد خالد
            '11' => 'Contracting Middle East', // محمد فتحي محمد التوكي
            '112' => 'Contracting Middle East', // رهام مجدي عبده سويلام
            '910' => 'Contracting Middle East', // ندى محمود إبراهيم الحسيني
            '958' => 'Contracting Middle East', // ميادة عادل محمد بركات
            '647' => 'Contracting Middle East', // شيماء وليد عامر قاسم
            '403' => 'Contracting Middle East', // أميرة أحمد إبراهيم
            '539' => 'Contracting Middle East', // رانيا محمد سيف الدين علي
            '2' => 'Contracting International', // مسعد سليمان عبد الغني عبد المجيد
            '828' => 'Contracting International', // طارق مصطفى طه عبد الجواد
            '893' => 'Contracting International', // مي زيادة السيد زيادة
            '897' => 'Contracting International', // حبيبة علاء علي عبد العزيز
            '931' => 'Contracting International', // لؤي عاطف محمد شريف
            '977' => 'Contracting International', // سلمى أحمد عبد العزيز السيد
            '957' => 'Contracting International', // يارا خالد عبد العزيز متولي
            '959' => 'Contracting International', // مونيكا أشرف عزت جرجس
            '944' => 'Contracting International', // عائشة محمد هشام محمد
            '1001' => 'Contracting International', // مارتن منصور مقنوتا سعيد
            '338' => 'Internet', // كريم محمد علي مصطفى
            '798' => 'Internet', // عمرو عاطف السيد علي
            '879' => 'Internet', // أحمد السيد أحمد السيد
            '895' => 'Internet', // أسماء حسن إسماعيل سويفي
            '932' => 'Internet', // مصطفى أسامة حمدي بهلول
            '887' => 'Internet', // أسماء أحمد لطفي محمد
            '865' => 'Marketing', // مريم مصطفى محمد عبد الغني
            '847' => 'Marketing', // مادونا نشأت أنور سها
            '968' => 'Marketing', // أحمد شوقي ضاحي محمود
        ];

        $this->info('Total assignments to process: ' . count($assignments));
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($assignments as $employeeCode => $departmentName) {
            try {
                // Find user by employee code
                $user = User::where('EmployeeCode', $employeeCode)->first();
                
                if (!$user) {
                    $errors[] = "لم يتم العثور على الموظف بكود: $employeeCode";
                    $errorCount++;
                    continue;
                }
                
                // Find department
                $department = Department::where('name', $departmentName)->first();
                
                if (!$department) {
                    $errors[] = "لم يتم العثور على القسم: $departmentName للموظف: $employeeCode";
                    $errorCount++;
                    continue;
                }
                
                // Update user department
                $user->department_id = $department->id;
                $user->save();
                
                $this->line("تم تعيين الموظف {$user->name} (كود: $employeeCode) للقسم: $departmentName");
                $successCount++;
                
            } catch (\Exception $e) {
                $errors[] = "خطأ في تعيين الموظف $employeeCode: " . $e->getMessage();
                $errorCount++;
            }
        }

        // Summary
        $this->info("\n=== ملخص العمل ===");
        $this->info("تم تعيين $successCount موظف بنجاح");
        $this->error("فشل في تعيين $errorCount موظف");
        
        if (!empty($errors)) {
            $this->error("\n=== الأخطاء ===");
            foreach ($errors as $error) {
                $this->error("- $error");
            }
        }

        return Command::SUCCESS;
    }
}





