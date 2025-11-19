<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', 'AIzaSyBV5ci35XfUGyRkj2Bcfp00apc_Xh_LrGE');
    }

    /**
     * Generate notes for password account based on category and account name
     */
    public function generatePasswordNotes($accountName, $category = null, $email = null, $url = null)
    {
        try {
            $prompt = $this->buildPrompt($accountName, $category, $email, $url);
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $this->apiKey,
            ])->post($this->baseUrl, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($data['candidates'][0]['content']['parts'][0]['text']);
                }
            }

            Log::error('Gemini API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Gemini Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build prompt for password account notes generation
     */
    private function buildPrompt($accountName, $category = null, $email = null, $url = null)
    {
        $prompt = "Generate helpful security notes for a password account with the following details:\n\n";
        $prompt .= "Account Name: {$accountName}\n";
        
        if ($category) {
            $prompt .= "Category: {$category}\n";
        }
        
        if ($email) {
            $prompt .= "Email/Username: {$email}\n";
        }
        
        if ($url) {
            $prompt .= "Website URL: {$url}\n";
        }

        $prompt .= "\nPlease generate concise, practical security notes that include:\n";
        $prompt .= "1. Security best practices for this type of account\n";
        $prompt .= "2. Important reminders about password management\n";
        $prompt .= "3. Any specific considerations for this service/platform\n";
        $prompt .= "4. Backup and recovery information if relevant\n\n";
        $prompt .= "Keep the notes professional, concise, and actionable. Format as a single paragraph or bullet points.";

        return $prompt;
    }

    /**
     * Generate Arabic notes
     */
    public function generatePasswordNotesArabic($accountName, $category = null, $email = null, $url = null)
    {
        try {
            $prompt = $this->buildArabicPrompt($accountName, $category, $email, $url);
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $this->apiKey,
            ])->post($this->baseUrl, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($data['candidates'][0]['content']['parts'][0]['text']);
                }
            }

            Log::error('Gemini API Error (Arabic): ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Gemini Service Error (Arabic): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build Arabic prompt for password account notes generation
     */
    private function buildArabicPrompt($accountName, $category = null, $email = null, $url = null)
    {
        $prompt = "قم بتوليد ملاحظات أمنية مفيدة لحساب كلمة مرور بالتفاصيل التالية:\n\n";
        $prompt .= "اسم الحساب: {$accountName}\n";
        
        if ($category) {
            $prompt .= "الفئة: {$category}\n";
        }
        
        if ($email) {
            $prompt .= "البريد الإلكتروني/اسم المستخدم: {$email}\n";
        }
        
        if ($url) {
            $prompt .= "رابط الموقع: {$url}\n";
        }

        $prompt .= "\nيرجى توليد ملاحظات أمنية مختصرة وعملية تشمل:\n";
        $prompt .= "1. أفضل الممارسات الأمنية لهذا النوع من الحسابات\n";
        $prompt .= "2. تذكيرات مهمة حول إدارة كلمات المرور\n";
        $prompt .= "3. اعتبارات خاصة لهذه الخدمة/المنصة\n";
        $prompt .= "4. معلومات النسخ الاحتياطي والاسترداد إذا كانت ذات صلة\n\n";
        $prompt .= "اجعل الملاحظات مهنية ومختصرة وقابلة للتنفيذ. قم بتنسيقها كفقرة واحدة أو نقاط.";

        return $prompt;
    }
}












