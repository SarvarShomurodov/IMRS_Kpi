<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Report $report): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Report $report): bool
    {
        return $user->id === $report->user_id; // faqat o‘zining reportini tahrirlash
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->id === $report->user_id; // faqat o‘zining reportini o‘chirish
    }

    public function restore(User $user, Report $report): bool
    {
        return false; // ruxsat bermaymiz
    }

    public function forceDelete(User $user, Report $report): bool
    {
        return false; // ruxsat bermaymiz
    }
}
