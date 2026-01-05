<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    private const CACHE_TTL = 3600; // 1 soat
    private const EXCLUDED_ROLES = ['Admin', 'Super Admin', 'Texnik'];

    /**
     * Base users - ✅ LOGIKA O'ZGARMAYDI
     * Faqat optimizatsiya qo'shildi (cache + eager loading)
     */
    public function getBaseUsers($from, bool $useCache = true): Collection
    {
        $cacheKey = 'base_users_' . $from->format('Y-m-d');

        if (!$useCache) {
            return $this->fetchBaseUsers($from);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from) {
            return $this->fetchBaseUsers($from);
        });
    }

    /**
     * Actual query - ✅ AYNAN SIZNING LOGIKANGIZ
     */
    private function fetchBaseUsers($from): Collection
    {
        return User::withTrashed()
            ->where(function ($query) use ($from) {
                $query->whereNull('deleted_at')->orWhere('deleted_at', '>', $from);
            })
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', self::EXCLUDED_ROLES);
            })
            ->with([
                'project:id,name',
                'previousProject:id,name', // 🔥 YANGI
                'roles:id,name',
            ])
            ->select([
                'id',
                'firstName',
                'lastName',
                'email',
                'position',
                'project_id',
                'previous_project_id', // 🔥 YANGI
                'project_changed_at',
                'created_at',
                'deleted_at', // 🔥 YANGI
            ])
            ->get();
    }

    /**
     * Eligible users - ✅ LOGIKA O'ZGARMAYDI
     */
    public function getEligibleUsers(Collection $baseUsers, $from): Collection
    {
        return $baseUsers->filter(function ($user) use ($from) {
            return !$user->created_at || $user->created_at <= $from;
        });
    }

    /**
     * Yangi userlar (ball olganlar) - ✅ LOGIKA O'ZGARMAYDI
     */
    public function getNewUsersWithRating(Collection $baseUsers, $from, array $userRatings): Collection
    {
        return $baseUsers->filter(function ($user) use ($from, $userRatings) {
            $isNew = $user->created_at && $user->created_at > $from;
            $hasRating = ($userRatings[$user->id] ?? 0) > 0;
            return $isNew && $hasRating;
        });
    }

    /**
     * Position bo'yicha filter
     */
    public function getUsersByPosition(Collection $users, ?string $position): Collection
    {
        if (!$position || $position === 'all') {
            return $users;
        }

        return $users->where('position', $position);
    }

    /**
     * Cache tozalash
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}
