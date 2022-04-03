<?php

namespace App\Services;

use Cache;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RubenVanErk\TopLoggerPhpSdk\TopLogger;
use stdClass;

class TopLoggerService
{
    public function __construct(protected GradeConverterService $gradeConverterService = new GradeConverterService())
    {
    }

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
                        ->get()
                );

                return $ascends->map(function ($ascend) use ($ascends) {
                    $gym = $this->getGym($ascend->climb->gym_id);
                    $ascend->climb->gym_city = $gym->city;
                    $ascend->climb->gym_name = trim(str_replace($ascend->climb->gym_city, '', $gym->name));
                    $ascend->climb->grade_font = $this->gradeConverterService->toFont($ascend->climb->grade, $gym->grading_system_boulders === 'french_rounded');
                    $ascend->climb->wall_name = collect($gym->walls)->firstWhere('id', $ascend->climb->wall_id ?? null)?->name;
                    $ascend->climb->hold_color = collect($gym->holds)->firstWhere('id', $ascend->climb->hold_id ?? null)?->color;

                    $ascend->is_repeat = $ascend->checks != 2 && $ascends->first(
                        fn($searchedAscend) => $ascend->climb_id == $searchedAscend->climb_id
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