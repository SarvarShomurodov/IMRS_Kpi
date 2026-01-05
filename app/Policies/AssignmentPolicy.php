<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssignmentPolicy
{
    use HandlesAuthorization;

    // Assignmentni ko'rish
    public function view(User $user, Assignment $assignment)
    {
        // Super Admin va Adminlarga barcha fayllarni ko'rish huquqi
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin')) {
            return true;
        }

        // Faqat o'z faylini ko'rishga ruxsat berish
        return $user->id === $assignment->user_id;
    }

    // Assignmentni tahrirlash
    public function update(User $user, Assignment $assignment)
    {
        // Super Admin va Adminlarga barcha fayllarni tahrirlash huquqi
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin')) {
            return true;
        }

        // Faqat o'z faylini tahrirlash
        return $user->id === $assignment->user_id;
    }

    // Assignmentni o'chirish
    public function delete(User $user, Assignment $assignment)
    {
        // Super Admin va Adminlarga barcha fayllarni o'chirish huquqi
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin')) {
            return true;
        }

        // Faqat o'z faylini o'chirish
        return $user->id === $assignment->user_id;
    }
}
