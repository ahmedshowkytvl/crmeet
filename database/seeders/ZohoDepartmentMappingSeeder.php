<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ZohoDepartmentMapping;
use App\Models\Department;
use Illuminate\Support\Facades\File;

class ZohoDepartmentMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Read CSV file
        $csvPath = base_path('Departments.csv');
        
        if (!File::exists($csvPath)) {
            $this->command->error('Departments.csv file not found!');
            return;
        }

        $csvData = File::get($csvPath);
        $lines = explode("\n", $csvData);
        
        // Skip header row
        $data = array_slice($lines, 1);
        
        foreach ($data as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $columns = str_getcsv($line);
            if (count($columns) < 2) continue;
            
            $zohoDepartmentName = trim($columns[0]);
            $zohoDepartmentId = trim($columns[1]);
            
            if (empty($zohoDepartmentName) || empty($zohoDepartmentId)) continue;
            
            // Find matching local department
            $localDepartment = $this->findMatchingLocalDepartment($zohoDepartmentName);
            
            if ($localDepartment) {
                ZohoDepartmentMapping::updateOrCreate(
                    ['zoho_department_id' => $zohoDepartmentId],
                    [
                        'zoho_department_name' => $zohoDepartmentName,
                        'local_department_id' => $localDepartment->id,
                        'local_department_name' => $localDepartment->name,
                        'description' => "Mapping for {$zohoDepartmentName}",
                        'is_active' => true
                    ]
                );
                
                $this->command->info("Mapped: {$zohoDepartmentName} ({$zohoDepartmentId}) -> {$localDepartment->name}");
            } else {
                $this->command->warn("No local department found for: {$zohoDepartmentName}");
            }
        }
        
        $this->command->info('Zoho Department Mapping completed!');
    }
    
    private function findMatchingLocalDepartment($zohoDepartmentName)
    {
        $localDepartments = Department::all();
        
        // Direct name matching
        foreach ($localDepartments as $dept) {
            if (strcasecmp($dept->name, $zohoDepartmentName) === 0) {
                return $dept;
            }
        }
        
        // Fuzzy matching based on keywords
        $zohoName = strtolower($zohoDepartmentName);
        
        foreach ($localDepartments as $dept) {
            $localName = strtolower($dept->name);
            
            // Check for contracting departments
            if (str_contains($zohoName, 'contracting') && str_contains($localName, 'contracting')) {
                // More specific matching for contracting departments
                if (str_contains($zohoName, 'egypt') && str_contains($localName, 'egypt')) {
                    return $dept;
                }
                if (str_contains($zohoName, 'ksa') && str_contains($localName, 'middle east')) {
                    return $dept;
                }
                if (str_contains($zohoName, 'eet global') && str_contains($localName, 'international')) {
                    return $dept;
                }
            }
            
            // Check for other departments
            if (str_contains($zohoName, 'customers') && str_contains($localName, 'operation')) {
                return $dept;
            }
            if (str_contains($zohoName, 'complaints') && str_contains($localName, 'operation')) {
                return $dept;
            }
            if (str_contains($zohoName, 'invoices') && str_contains($localName, 'accounts')) {
                return $dept;
            }
            if (str_contains($zohoName, 'stop sale') && str_contains($localName, 'operation')) {
                return $dept;
            }
        }
        
        return null;
    }
}
