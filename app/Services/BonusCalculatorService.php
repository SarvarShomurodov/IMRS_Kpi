<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class BonusCalculatorService
{
    /**
     * ✅ GLOBAL AVERAGE
     * Barcha bonus olishga haqli xodimlarning o'rtacha balli
     */
    public function calculateGlobalAverage(Collection $eligibleUsers, Collection $newUsersWithRating, array $userRatings): float
    {
        $ratingsForGlobalAvg = [];

        // Eligible userlar
        foreach ($eligibleUsers as $user) {
            $ratingsForGlobalAvg[] = $userRatings[$user->id] ?? 0;
        }

        // Yangi userlar (ball olganlar)
        foreach ($newUsersWithRating as $user) {
            $ratingsForGlobalAvg[] = $userRatings[$user->id] ?? 0;
        }

        return count($ratingsForGlobalAvg) > 0 ? array_sum($ratingsForGlobalAvg) / count($ratingsForGlobalAvg) : 0;
    }

    /**
     * ✅ DOMINANT PROJECT
     * Xodim eng ko'p qaysi loyihada vazifa bajargan
     */
    public function getDominantProjects(Collection $baseUsers, Collection $rawAssignments): array
    {
        $dominantProjects = [];

        foreach ($baseUsers as $user) {
            $userAssignments = $rawAssignments->where('user_id', $user->id);

            if ($userAssignments->isNotEmpty()) {
                $projectCounts = $userAssignments->countBy('project_id');
                $dominantProjects[$user->id] = $projectCounts->sortDesc()->keys()->first();
            } else {
                $dominantProjects[$user->id] = $user->project_id;
            }
        }

        return $dominantProjects;
    }

    /**
     * 🔥 TO'G'RILANGAN: EFFECTIVE PROJECT
     * Bonus hisoblash uchun qaysi loyiha hisoblanishi kerak
     * 
     * TARIXIY DAVRLAR UCHUN HAM TO'G'RI ISHLAYDI!
     */
    public function getEffectiveProjectIds(Collection $baseUsers, array $dominantProjects, $from, $to): array
    {
        $effectiveProjects = [];
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);
        $midpoint = $start->copy()->addDays($start->diffInDays($end) / 2);

        foreach ($baseUsers as $user) {
            $dominantProjectId = $dominantProjects[$user->id] ?? $user->project_id;

            // Agar loyiha o'zgarish ma'lumoti yo'q bo'lsa
            if (!$user->project_changed_at || !$user->previous_project_id) {
                $effectiveProjects[$user->id] = $dominantProjectId;
                continue;
            }

            $changeDate = Carbon::parse($user->project_changed_at);

            // 🔥 ASOSIY TUZATISH: Tarixiy davrlar uchun to'g'ri loyihani aniqlash
            
            // 1️⃣ Agar o'zgarish so'ralgan davrDAN KEYIN bo'lsa
            // → Demak, shu davrda hali ESKI loyihada ishlagan
            // Misol: Noyabrni so'raganmiz, 17-dekabrda o'zgardi → noyabrda eski loyihada edi
            if ($changeDate->isAfter($end)) {
                $effectiveProjects[$user->id] = $user->previous_project_id; // ✅ ESKI loyiha
                continue;
            }

            // 2️⃣ Agar o'zgarish so'ralgan davrDAN OLDIN bo'lsa
            // → Demak, shu davrda allaqachon YANGI loyihada ishlagan
            // Misol: Yanvarnı so'raganmiz, 17-dekabrda o'zgardi → yanvarda yangi loyihada
            if ($changeDate->isBefore($start)) {
                $effectiveProjects[$user->id] = $user->project_id; // ✅ YANGI loyiha
                continue;
            }

            // 3️⃣ Agar o'zgarish davr ICHIDA bo'lsa
            // → O'rtadan oldin o'zgardi → yangi loyiha bonusi
            // → O'rtadan keyin o'zgardi → eski loyiha bonusi
            if ($changeDate->lte($midpoint)) {
                $effectiveProjects[$user->id] = $user->project_id; // Yangi
            } else {
                $effectiveProjects[$user->id] = $user->previous_project_id; // Eski
            }
        }

        return $effectiveProjects;
    }

    /**
     * ✅ PROJECT BONUSES
     * Har bir loyiha uchun bonus mavjudligini va miqdorini hisoblash
     */
    public function calculateProjectBonuses(
        Collection $baseUsers,
        Collection $eligibleUsers,
        Collection $newUsersWithRating,
        array $userRatings,
        float $globalAvg,
        array $effectiveProjects
    ): array {
        $projectBonuses = [];

        // EFFECTIVE PROJECTS bo'yicha guruhlash
        $projectGroups = [];
        foreach ($baseUsers as $user) {
            $effectiveProjectId = $effectiveProjects[$user->id] ?? $user->project_id;

            if (!isset($projectGroups[$effectiveProjectId])) {
                $projectGroups[$effectiveProjectId] = collect();
            }

            $projectGroups[$effectiveProjectId]->push($user);
        }

        foreach ($projectGroups as $projectId => $projectUsers) {
            // O'sha effective projectdagi ELIGIBLE userlar
            $eligibleProjectUsers = $eligibleUsers->filter(fn($u) => ($effectiveProjects[$u->id] ?? null) == $projectId);

            // Yangi userlar (ball olganlar)
            $newProjectUsers = $newUsersWithRating->filter(fn($u) => ($effectiveProjects[$u->id] ?? null) == $projectId);

            // Birlashtiramiz
            $allProjectUsers = $eligibleProjectUsers->merge($newProjectUsers);

            // Project average
            $projectRatings = $allProjectUsers->map(fn($u) => $userRatings[$u->id] ?? 0);
            $projectAvg = $projectRatings->avg() ?: 0;

            // ✅ BONUS BOR YOKI YO'Q
            $hasBonus = $projectAvg > 0 && $globalAvg > 0 && $projectAvg > $globalAvg;

            $projectBonuses[$projectId] = [
                'project_avg' => $projectAvg,
                'has_bonus' => $hasBonus,
                'bonus_10' => $hasBonus ? round($projectAvg * 0.1, 2) : 0,
                'bonus_5' => $hasBonus ? round($projectAvg * 0.05, 2) : 0,
            ];
        }

        return $projectBonuses;
    }

    /**
     * ✅ USER BONUS
     * Har bir xodim uchun bonus miqdorini hisoblash
     */
    public function calculateUserBonus(
        int $userId, 
        float $userRating, 
        Collection $eligibleUsers, 
        Collection $newUsersWithRating, 
        array $projectBonuses, 
        array $effectiveProjects
    ): float {
        // ✅ BONUS OLISH HUQUQI
        $isEligible = $eligibleUsers->contains('id', $userId);
        $isNewWithRating = $newUsersWithRating->contains('id', $userId);

        if (!$isEligible && !$isNewWithRating) {
            return 0;
        }

        // Project bonusi
        $projectId = $effectiveProjects[$userId] ?? null;
        $projectBonus = $projectBonuses[$projectId] ?? null;

        if (!$projectBonus || !$projectBonus['has_bonus']) {
            return 0;
        }

        // ✅ BONUS MIQDORI
        $projectAvg = $projectBonus['project_avg'];

        return $userRating > $projectAvg
            ? $projectBonus['bonus_10'] // 10%
            : $projectBonus['bonus_5']; // 5%
    }

    /**
     * 🔥 PROJECT CHANGE INFO
     * Loyiha o'zgarish ma'lumotini olish (frontend uchun)
     */
    public function getProjectChangeInfo($user, $from, $to, $dominantProjectId, $effectiveProjectId)
    {
        if (!$user->project_changed_at) {
            return null;
        }

        $changeDate = Carbon::parse($user->project_changed_at);
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);

        // Agar o'zgarish davr ichida bo'lmasa
        if (!$changeDate->between($start, $end)) {
            return null;
        }

        $midpoint = $start->copy()->addDays($start->diffInDays($end) / 2);

        return [
            'changed_at' => $changeDate->format('d.m.Y'),
            'current_project' => $user->project->name ?? 'N/A',
            'previous_project' => $user->previousProject->name ?? 'N/A',
            'effective_project_id' => $effectiveProjectId,
            'dominant_project_id' => $dominantProjectId,
            'is_midpoint_before' => $changeDate->lte($midpoint),
        ];
    }
}