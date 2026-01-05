<?php

return [
    // Hisobotlarni ko'ra olmaydigan adminlar ro'yxati
    'restricted_report_emails' => [
        'adminreport@gmail.com',
    ],

    // Davomat ma'lumotlarini o'zgartira oladigan adminlar ro'yxati
    'attendance_edit_emails' => [
        'admin@gmail.com',
        'adminreport@gmail.com',
    ],

    // Yoki umumiy yondashuv - barcha cheklovlar
    'permissions' => [
        'can_view_reports' => [
            'restricted_emails' => [
                'adminreport@gmail.com', 
                'admin@gmail.com'
            ],
        ],
        'can_edit_attendance' => [
            'allowed_emails' => [
                'admin@gmail.com',
                'adminreport@gmail.com'
            ],
        ],
    ],
];