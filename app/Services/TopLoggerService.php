<?php

namespace App\Services;

use Cache;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RubenVanErk\TopLoggerPhpSdk\TopLogger;
use stdClass;

class TopLoggerService
{
    public function getAscends($userId): Collection
    {
        return Cache::rememberForever(
            'ascends' . $userId,
            function () use ($userId) {
                $ascends = collect(
                    (new TopLogger())->ascends()
                        ->filter(['used' => true])
                        ->filter(['user' => ['uid' => $userId]])
                        ->param(['serialize_checks' => true])
                        ->include(['climb'])
                        ->get());
                return $ascends->map(function ($ascend) use ($ascends) {
                    $ascend->climb->grade_font = $this->gradeConverterService->toFont((float)$ascend->climb->grade);
                    $ascend->climb->gym_city = $this->getGym($ascend->climb->gym_id)->city;
                    $ascend->climb->gym_name = trim(str_replace($ascend->climb->gym_city, '', $this->getGym($ascend->climb->gym_id)->name));
                    $ascend->climb->wall_name = collect($this->getGym($ascend->climb->gym_id)->walls)->firstWhere('id', $ascend->climb->wall_id ?? null)?->name;
                    $ascend->climb->hold_color = collect($this->getGym($ascend->climb->gym_id)->holds)->firstWhere('id', $ascend->climb->hold_id ?? null)?->color;

                    $ascend->is_repeat = (bool)$ascends->first(
                        fn($searchedAscend) => $ascend->climb_id == $searchedAscend->climb_id
                            && $ascend->user_id == $searchedAscend->user_id
                            && $ascend->id != $searchedAscend->id
                            && (new Carbon($ascend->date_logged))->isAfter(new Carbon($searchedAscend->date_logged))
                    );

                    return $ascend;
                });
            }
        );
    }

    public function getGym($gymId): stdClass
    {
        return Cache::rememberForever(
            'gyms' . $gymId,
            fn() => (new TopLogger())->gyms()->include(['holds', 'walls'])->find($gymId)
        );
    }

    public function getStats(string $userId): stdClass
    {
        return Cache::rememberForever(
            'stats' . $userId,
            fn() => (new TopLogger())->users()->stats($userId)
        );
    }
}
