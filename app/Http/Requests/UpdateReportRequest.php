<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // 1. FAQAT <ol> va <li> formatini qabul qilish
                    if (!str_contains($value, '<ol>') || !str_contains($value, '<li>')) {
                        $fail('❌ Xisobot faqat raqamli ro\'yxat (formatted list) formatida bo\'lishi kerak!');
                        return;
                    }
                    
                    // 2. Kamida 1 ta element bo'lishi kerak
                    preg_match_all('/<li>/', $value, $matches);
                    $count = count($matches[0]);
                    
                    if ($count < 1) {
                        $fail("❌ Kamida 1 ta vazifa yozish kerak! Hozir: {$count} ta");
                        return;
                    }
                    
                    // 3. Har bir element kamida 3 ta belgi bo'lishi kerak
                    $dom = new \DOMDocument();
                    libxml_use_internal_errors(true);
                    $dom->loadHTML(mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                    libxml_clear_errors();
                    
                    $items = $dom->getElementsByTagName('li');
                    
                    foreach ($items as $index => $item) {
                        $text = trim($item->textContent);
                        if (strlen($text) < 3) {
                            $fail(($index + 1) . "-vazifa juda qisqa! Kamida 3 ta belgi yozing. (Hozir: " . strlen($text) . " belgi)");
                            return;
                        }
                    }
                    
                    // 4. Jami belgilar soni kamida 2 bo'lishi kerak
                    $totalText = strip_tags($value);
                    if (strlen($totalText) < 2) {
                        $fail("❌ Xisobot juda qisqa! Kamida 2 ta belgi bo'lishi kerak. (Hozir: " . strlen($totalText) . " belgi)");
                        return;
                    }
                }
            ],
            
            // Fayl yuklash validatsiyasi
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'string',
        ];
    }

    public function messages()
    {
        return [
            'content.required' => '❌ Xisobot matni kiritilishi shart',
            'attachments.max' => '❌ Maksimal 5 ta fayl yuklash mumkin',
            'attachments.*.file' => '❌ Yuklangan fayllar noto\'g\'ri formatda',
            'attachments.*.mimes' => '❌ Faqat PDF, Word, Excel va rasm fayllarini yuklash mumkin',
            'attachments.*.max' => '❌ Har bir fayl hajmi 10MB dan oshmasligi kerak',
        ];
    }
}