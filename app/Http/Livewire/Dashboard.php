<?php

namespace App\Http\Livewire;

use App\Services\GradeConverter;
use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use RubenVanErk\TopLoggerPhpSdk\TopLogger;
use stdClass;

class Dashboard extends Component
{
    public Collection $ascendsByDate;
    public array $climberStats = [];
    public array $chartData = [];

    protected $listeners = ['clearCache' => 'refreshData'];

    public array $climberUids = [
        '7163205870' => 'Ruben',
        '2943357766' => 'Wouter',
    ];

    public array $climberIds = [
        '104045' => 'Ruben',
        '106235' => 'Wouter',
    ];

    public function mount(): void
    {
        $this->createAscendsByDate();
        $this->createStats();
        $this->createChartData();
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }

    public function refreshData(): void
    {
        Cache::clear();
        $this->createAscendsByDate();
        $this->createStats();
        $this->createChartData();
    }

    private function createAscendsByDate(): void
    {
        $this->ascendsByDate = collect();

        foreach ($this->climberUids as $uid => $climber) {
            $this->ascendsByDate = $this->ascendsByDate->merge($this->getAscends($uid));
        }

        // set grade to font & sort by grade
        $this->ascendsByDate = $this->ascendsByDate
            ->sortByDesc(fn($ascend) => (int)$ascend->checks)
            ->sortByDesc(fn($ascend) => (float)$ascend->climb->grade);

        // group and sort by date
        $this->ascendsByDate = $this->ascendsByDate
            ->groupBy(fn($ascend) => (new Carbon($ascend->date_logged))->format('Y-m-d'))
            ->sortKeysDesc();

        $this->ascendsByDate = $this->ascendsByDate->map(function (Collection $groupedAscends) {
            return $groupedAscends->groupBy('user_id');
        });
    }

    protected function createStats(): void
    {
        foreach ($this->climberIds as $userId => $climber) {
            $ascends = $this->getAscends(array_flip($this->climberUids)[$climber]);

            $topTenAll = $ascends
                ->sortByDesc(fn($ascend) => (new Carbon($ascend->date_logged))->unix())
                ->sortByDesc(fn($ascend) => (int)$ascend->checks)
                ->sortByDesc(fn($ascend) => (float)$ascend->climb->grade)
                ->take(10)
                ->map(function ($ascend) {
                    $ascend->days_ago = (new Carbon($ascend->date_logged))->diffInDays(now());
                    return json_decode(json_encode($ascend), true);
                });

            $sessionCount = $ascends
                ->groupBy(fn($ascend) => (new Carbon($ascend->date_logged))->format('Y-m-d'))
                ->count();

            $stats = $this->getStats($userId);
            $stats->top_ten = collect($stats->top_ten)->map(function ($ascend) use ($ascends) {
                $ascend->grade_font = GradeConverter::toFont((float)$ascend->grade);

                $ascend->gym_id = $ascends->firstWhere('climb_id', $ascend->climb_id)->climb->gym_id;
                $ascend->gym_name = $this->getGym($ascend->gym_id)?->name;

                $ascend->days_ago = (new Carbon($ascend->date_logged))->diffInDays(now());

                return get_object_vars($ascend);
            });

            $this->climberStats[$userId] = [
                'sessionCount' => $sessionCount,
                'tops' => $ascends->count(),
                'grade_font' => GradeConverter::toFont((float)$stats->grade),
                'grade_progress' => GradeConverter::getProgress((float)$stats->grade),
                'top_ten_60d' => $stats->top_ten,
                'top_ten_all' => $topTenAll,
            ];
        }
    }

    protected function getGym($gymId): stdClass
    {
        return Cache::rememberForever(
            'gyms' . $gymId,
            fn() => (new TopLogger())->gyms()->include(['holds', 'walls'])->find($gymId)
        );
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
                        ->get());
                return $ascends->map(function ($ascend) use ($ascends) {
                    $ascend->climb->grade_font = GradeConverter::toFont((float)$ascend->climb->grade);
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

    protected function getStats(string $userId): stdClass
    {
        return Cache::rememberForever(
            'stats' . $userId,
            fn() => (new TopLogger())->users()->stats($userId)
        );
    }

    private function createChartData(): void
    {
        foreach ($this->climberUids as $uid => $climber) {
            $ascends = $this->getAscends($uid);

            $allAscends = $ascends->filter(fn($ascend) => $ascend->climb->grade >= 6);
            $flashes = $allAscends->filter(fn($ascend) => $ascend->checks == 2);

            $allAscends = $allAscends
                ->groupBy(fn($ascend) => $ascend->climb->grade)
                ->sortKeysDesc()
                ->mapWithKeys(fn($ascends, $key) => [$key => count($ascends)]);

            $flashes = $flashes
                ->groupBy(fn($ascend) => $ascend->climb->grade)
                ->sortKeysDesc()
                ->mapWithKeys(fn($ascends, $key) => [$key => count($ascends)]);

            $this->chartData[] = [
                array_reverse(array_pad(array_reverse(array_values($allAscends->toArray())), 6, 0)),
                array_reverse(array_pad(array_reverse(array_values($flashes->toArray())), 6, 0)),
            ];
        }
    }
}
