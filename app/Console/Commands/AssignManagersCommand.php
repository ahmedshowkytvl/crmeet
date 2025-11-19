<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssignManagersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'managers:assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تعيين المديرين للموظفين بناءً على ملف CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('بدء تعيين المديرين للموظفين...');
        $this->newLine();

        // mapping للمديرين الرئيسيين
        $managers = [
            '981' => 'Abdel Hamid Mohamed Abdel Hamid', // مدير الحسابات
            '726' => 'Ashraf Shafie Mohamed Mahmoud', // مدير المبيعات
            '59' => 'Heba Mohamed Ezzat Hal', // مدير التطوير التجاري
            '16' => 'Mohamed Anwar Awad Baioumy', // مدير تقنية المعلومات
            '156' => 'Khaled Ahmed Mohamed', // مدير الموارد البشرية
            '35' => 'Wafaa Mohamed Naguib Osman', // مدير التعاقدات المحلية
            '11' => 'Mohamed Fathy Mohamed El Toukhy', // مدير التعاقدات الشرق أوسطية
            '2' => 'Mousad Soliman Abdel Ghany Abd El Meged', // مدير التعاقدات الدولية
            '338' => 'Karim Mohamed Ali Mostafa', // مدير الإنترنت
        ];

        // العلاقات بين المديرين والموظفين بناءً على تحليل CSV
        $reportingRelations = [
            // الحسابات - Abdel Hamid Mohamed (981)
            '7' => '981', // Hanan Mohamed Ali Ibrahem
            '208' => '981', // Emad Saad El-Sayed El Refai
            '206' => '981', // Ahmed Alaa Ali Mohamed
            '705' => '981', // El-Sayed Mohamed Mohamed Deif
            '885' => '981', // Mohamed Kamel Saleh AbdelAziz
            '891' => '981', // Yasmin Mourad Mokhtar Gaber
            '943' => '981', // Mohamed Rafat Dessouky Dessouky
            '1004' => '981', // Omar Essam Hassan Ahmed
            '1008' => '981', // Eslam Mohamed Kamel Mohamed
            '1016' => '981', // Abanoub Saad Lotfy Tawfik
            '947' => '981', // Kareem Saeed Hassan Mohamed
            '960' => '981', // Mohamed Ashraf El-Dessouky Tolpa

            // المبيعات - Ashraf Shafie (726)
            '181' => '726', // Ahmed Maher Saad Fahmy
            '872' => '726', // Ahmed Mohamed Ahmed Dieb

            // التطوير التجاري - Heba Mohamed Ezzat (59)
            '173' => '59', // Ahmed El-Sayed Abdel Rahim Mohamed

            // العمليات - Ahmed Elsayed (173) - لكنه موظف، نحتاج مدير العمليات
            '1006' => '173', // Islam Ehab Ahmed Mohamed
            '988' => '173', // Amira Hamdy Elsayed Ahmed
            '989' => '173', // Viola Fayez Awad Garges
            '998' => '173', // Hesham Talaat Hussein Hassan
            '1000' => '173', // Hadir Mostafa Ahmed El Mokadem
            '1003' => '173', // Aya Allah Ashraf Mohamed El Awady
            '1012' => '173', // Sherif Ibrahim Abbas
            '1014' => '173', // Nada Magady Ibrahim Abdullah

            // تقنية المعلومات - Mohamed Anwar Awad (16)
            '26' => '16', // Rami Sayed Ali Hassan
            '896' => '16', // Sayed Khalifa Sayed Khalifa
            '970' => '16', // Barkat Ramadan Barkat Baioumy

            // الإدارة - Abdelrahman Mohamed Elsayed (714)
            '920' => '714', // Moustafa Magdy Abd El Hamed El Garb
            '976' => '714', // Ahmed Gomaa Riad AbdelHaleem
            '870' => '714', // Amal Saleh Fargaly Salah
            '871' => '714', // Noha Fathy El Sayed Gad

            // الموارد البشرية - Khaled Ahmed (156)
            '3' => '156', // Rania Abdel Mohsen Mahmoud
            '1019' => '156', // Zied Mustafa Ali
            '1022' => '156', // Lamiaa Hussein Ali

            // قادة الرحلات - Mohamed Mahmoud Ibrahim (629)
            '816' => '629', // Ayman Ashraf Farouk Labib
            '841' => '629', // Waleed Khalaf Hassan AbdelAziz

            // النقل - Gamal Mosallhy El-Sayed (12)
            '407' => '12', // Mohamed Morsi Mohamed Morsi
            '786' => '12', // Ahmed Hussein Madbouly Sayed

            // التعاقدات المحلية - Wafaa Mohamed Naguib (35)
            '916' => '35', // Yara Ahmed Abderab El Naby
            '892' => '35', // Nayra Ahmed Mabrouk Hamed
            '1010' => '35', // Abd El Rahman Mohamed Mohamed Khaled

            // التعاقدات الشرق أوسطية - Mohamed Fathy Mohamed (11)
            '112' => '11', // Reham Magdy Abdo Swilam
            '910' => '11', // Nada Mahmoud Ibrahem El Hoseny
            '958' => '11', // Mayada Adel Mohamed Barakat
            '647' => '11', // Shaimaa Waleed Amer Kassem
            '403' => '11', // Amira Ahmed Ibrahim
            '539' => '11', // Rania Mohamed Seif El Dien Ali

            // التعاقدات الدولية - Mousad Soliman (2)
            '828' => '2', // Tarek Mostafa Taha Abd El Gawad
            '893' => '2', // Mai Zeyada El sayed Zeyada
            '897' => '2', // Habiba Alaa Ali Abd El Aziz
            '931' => '2', // Louay Atef Mohamed Sharif
            '977' => '2', // Salma Ahmed Abdel Aziz El Sayed
            '957' => '2', // Yara Khaled Abd El Aziz Matwaly
            '959' => '2', // Monica Ashraf Ezzat Gerges
            '944' => '2', // Aisha Mohamed Hesham Mohamed
            '1001' => '2', // Marten Mansour Maknota Saeed
            '994' => '2', // Nancy Nagy Rizk Garges

            // الإنترنت - Karim Mohamed Ali (338)
            '798' => '338', // Amr Atef Elsayed Ali
            '879' => '338', // Ahmed El-Sayed Ahmed El-Sayed
            '895' => '338', // Asmaa Hassan Ismail Swiefy
            '932' => '338', // Mostafa Osama Hamdy Bahloul
            '887' => '338', // Asmaa Ahmed Lotfy Mohamed

            // التسويق - Nadia Saeed (غير موجود في CSV، سنستخدم مدير عام)
            '865' => '981', // Mariam Mostafa Mohamed Abd El Ghany
            '847' => '981', // Madonna Nashaat Anwer Seha
            '968' => '981', // Ahmed Shawky Dahy Mahmoud
        ];

        $this->info("تم العثور على " . count($managers) . " مدير");
        $this->info("تم العثور على " . count($reportingRelations) . " علاقة إدارية");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;

        // تعيين المديرين
        foreach ($reportingRelations as $employeeCode => $managerCode) {
            $employee = User::where('EmployeeCode', $employeeCode)->first();
            $manager = User::where('EmployeeCode', $managerCode)->first();

            if ($employee && $manager) {
                $employee->update(['manager_id' => $manager->id]);
                $this->line("✓ تم تعيين المدير {$manager->name} للموظف {$employee->name}");
                $successCount++;
            } else {
                if (!$employee) {
                    $this->error("✗ لم يتم العثور على الموظف بكود: {$employeeCode}");
                }
                if (!$manager) {
                    $this->error("✗ لم يتم العثور على المدير بكود: {$managerCode}");
                }
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("=== ملخص النتائج ===");
        $this->info("تم تعيين المديرين بنجاح: {$successCount}");
        $this->info("فشل في التعيين: {$errorCount}");
        $this->info("تم الانتهاء من تعيين المديرين!");

        return Command::SUCCESS;
    }
}
