<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Assignment;

class AssignmentUpdatedNotification extends Notification
{
    use Queueable;

    protected $assignment;
    protected $user;

    public function __construct(Assignment $assignment, $user)
    {
        $this->assignment = $assignment;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        // Agar o'zgartirish admin tomonidan kiritilgan bo'lsa, bildirishnoma yuborilmasin
        if ($this->user->email === 'admin@gmail.com') {
            return [];
        }
        
        return ['database']; // Faqat bazaga yoziladi
    }

    public function toDatabase($notifiable)
    {
        // O'zgargan ustun nomlarini aniqlash
        $changedFields = array_keys($this->assignment->getDirty());
    
        // Ustun nomlarini foydalanuvchi uchun tushunarli tarzda tarjima qilish
        $fieldNames = [
            'name' => 'Бажарилган топшириқ номи',
            'who_from' => 'Ким томонидан берилди',
            'file' => 'Файл',
            'who_hand' => 'Материални топширган санаси (якуний вариант)',
            'people' => 'Лойиҳадаги ижрочилар ва ҳиссалар',
        ];
    
        // Har bir o'zgargan ustunni foydalanuvchiga ko'rsatiladigan shaklga o'tkazish
        $translatedFields = array_map(function ($field) use ($fieldNames) {
            return $fieldNames[$field] ?? $field;
        }, $changedFields);
    
        // Tarjima qilingan o'zgarishlarni vergul bilan ajratib birlashtirish
        $changedList = implode(', ', $translatedFields);
    
        // Natijaviy xabar
        return [
            'message_short' => "{$this->user->lastName} {$this->user->firstName} o'zgartirish kiritdi.",
            'message_full' => "{$this->user->lastName} {$this->user->firstName} quyidagi ustun(lar)ni o'zgartirdi: {$changedList}",
            'assignment_id' => $this->assignment->id,
        ];
    }
}