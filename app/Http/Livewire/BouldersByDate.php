<?php

namespace App\Http\Livewire;

use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use RubenVanErk\TopLoggerPhpSdk\TopLogger;

class BouldersByDate extends Component
{
    public Collection $ascendsByDate;

    protected $listeners = ['clearCache' => 'refreshData'];

    public array $subgrades = [
        0 => 'a',
        1 => 'a+',
        2 => 'b',
        3 => 'b+',
        4 => 'c',
        5 => 'c+',
    ];

    public array $climberUids = [
        '7163205870' => 'Ruben',
        '2943357766' => 'Wouter',
    ];

    public array $climberIds = [
        '104045' => 'Ruben',
        '106235' => 'Wouter',
    ];

    public array $climberStats = [];

    public function mount(): void
    {
        $this->createDataset();
        $this->createStats();
    }

    public function render(): View
    {
        return view('livewire.boulders-by-date');
    }

    public function refreshData(): void
    {
        Cache::clear();
        $this->createDataset();
    }

    private function createDataset(): void
    {
        $this->ascendsByDate = collect();

        foreach ($this->climberUids as $uid => $climber) {
            $this->ascendsByDate = $this->ascendsByDate->merge($this->getAscends($uid));
        }

        // set grade to font & sort by grade
        $this->ascendsByDate = $this->ascendsByDate
            ->sortByDesc(fn($ascend) => $ascend->climb->grade)
            ->map(function ($ascend) {
                $grade = (float)$ascend->climb->grade;
                $mainGrade = floor($grade);

                $subGrade = $this->subgrades[floor(($grade - $mainGrade) / 0.16)] ?? '?';

                $ascend->climb->grade = $mainGrade . $subGrade;
                $ascend->climb->gym_city = $this->getGym($ascend->climb->gym_id)->city;
                $ascend->climb->gym_name = trim(str_replace($ascend->climb->gym_city, '', $this->getGym($ascend->climb->gym_id)->name));
                $ascend->climb->wall_name = collect($this->getGym($ascend->climb->gym_id)->walls)->firstWhere('id', $ascend->climb->wall_id ?? null)?->name;
                $ascend->climb->hold_color = collect($this->getGym($ascend->climb->gym_id)->holds)->firstWhere('id', $ascend->climb->hold_id ?? null)?->color;

                return $ascend;
            });

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
        foreach ($this->climberUids as $uid => $climber) {
            $ascends = collect($this->getAscends($uid));

            $tops = $ascends->count();
            $sessionCount = $ascends
                ->groupBy(fn($ascend) => (new Carbon($ascend->date_logged))->format('Y-m-d'))
                ->count();

            $averageScore = $ascends->where(fn($ascend) => (new Carbon($ascend->date_logged))->greaterThan(now()->subDays(60)))
                ->sortByDesc(fn($ascend) => (float)$ascend->climb->grade)
                ->take(10)
                ->map(function ($ascend) {
                    $score = (float)$ascend->climb->grade * 100;
                    if ($ascend->checks == 2) {
                        $score += 16.66;
                    }
                    return $score;
                })->average();

            $mainGrade = (int)floor($averageScore / 100);
            $subGradeScore = $averageScore - ($mainGrade * 100);
            $subGrade = $this->subgrades[(int)floor($subGradeScore / 16.66)];
            $progress = (int)round(fmod($subGradeScore, 16.66) / 16.66 * 100);

            $this->climberStats[$uid] = [
                'sessionCount' => $sessionCount,
                'tops' => $tops,
                'grade' => $mainGrade . $subGrade,
                'grade_progress' => $progress,
            ];
        }
    }

    protected function getGym($gymId)
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
            fn() => collect(
                (new TopLogger())->ascends()
                    ->filter(['used' => true])
                    ->filter(['user' => ['uid' => $userId]])
                    ->param(['serialize_checks' => true])
                    ->include(['climb'])
                    ->get()
            )
        );
    }
}
