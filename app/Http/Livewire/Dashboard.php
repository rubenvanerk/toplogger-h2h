<?php

namespace App\Http\Livewire;

use App\Services\GradeConverterService;
use App\Services\TopLoggerService;
use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public Collection $ascendsByDate;
    public array $climberStats = [];
    public array $chartData = [];
    public array $strengthHistory = [];
    public array $strengthHistoryDifference = [];
    public array $gyms = [];
    protected GradeConverterService $gradeConverterService;
    protected TopLoggerService $topLoggerService;

    protected $listeners = ['clearCache' => 'refreshData'];

    public array $climbers = [
        'Ruben' => [
            'uid' => '7163205870',
            'id' => '104045',
        ],
        'Wouter' => [
            'uid' => '2943357766',
            'id' => '106235',
        ],
    ];

    public function boot(): void
    {
        $this->gradeConverterService = new GradeConverterService();
        $this->topLoggerService = new TopLoggerService();
    }

    public function mount(): void
    {
        $this->generateData();
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }

    protected function generateData(): void
    {
        $this->createAscendsByDate();
        $this->createStats();
        $this->createChartData();
        $this->generateGymData();
        $this->createStrengthHistory();
    }

    public function refreshData(): void
    {
        Cache::put('updated_at', now()->subMinutes(5));
        return;

        Cache::clear();
        $this->generateData();
    }

    private function createAscendsByDate(): void
    {
        $this->ascendsByDate = collect();

        foreach ($this->climbers as $ids) {
            $this->ascendsByDate = $this->ascendsByDate->merge($this->topLoggerService->getAscends($ids['uid']));
        }

        // sort by grade & flash
        $this->ascendsByDate = $this->ascendsByDate
            ->sortByDesc(fn($ascend) => (int)$ascend->checks)
            ->sortByDesc(fn($ascend) => (float)$ascend->climb->grade);

        // group and sort by date
        $this->ascendsByDate = $this->ascendsByDate
            ->sortBy(fn($ascend) => $ascend->user_id)
            ->groupBy(fn($ascend) => (new Carbon($ascend->date_logged))->format('Y-m-d'))
            ->sortKeysDesc();

        // group by user
        $this->ascendsByDate = $this->ascendsByDate->map(function (Collection $groupedAscends) {
            return $groupedAscends->groupBy(fn($ascend) => $this->getClimberName($ascend->user_id));
        });
    }

    private function createStats(): void
    {
        foreach ($this->climbers as $name => $climberIds) {
            $ascends = $this->topLoggerService->getAscends($climberIds['uid']);

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

            $stats = $this->topLoggerService->getStats($climberIds['id']);
            $stats->top_ten = collect($stats->top_ten)->map(function ($ascend) use ($ascends) {
                $ascend->gym_id = $ascends->firstWhere('climb_id', $ascend->climb_id)->climb->gym_id;
                $gym = $this->topLoggerService->getGym($ascend->gym_id);
                $ascend->gym_name = $gym?->name;
                $ascend->grade_font = $this->gradeConverterService->toFont($ascend->grade, $gym->grading_system_boulders === 'french_rounded');

                $ascend->days_ago = (new Carbon($ascend->date_logged))->diffInDays(now());

                return get_object_vars($ascend);
            });

            $this->climberStats[$name] = [
                'sessionCount' => $sessionCount,
                'tops' => $ascends->count(),
                'grade_font' => $this->gradeConverterService->toFont($stats->grade),
                'grade_progress' => $this->gradeConverterService->getProgress((float)$stats->grade),
                'top_ten_60d' => $stats->top_ten,
                'top_ten_all' => $topTenAll,
            ];
        }
    }

    private function createChartData(): void
    {
        $this->chartData = [];

        foreach ($this->climbers as $name => $climberIds) {
            $ascends = $this->topLoggerService->getAscends($climberIds['uid']);

            $allAscends = $ascends->filter(fn($ascend) => $ascend->climb->grade >= 6);
            $flashes = $allAscends->filter(fn($ascend) => (int)$ascend->checks === 2);

            $allAscends = $allAscends
                ->groupBy(fn($ascend) => $ascend->climb->grade)
                ->sortKeysDesc()
                ->mapWithKeys(fn($ascends, $key) => [$key => count($ascends)]);

            $flashes = $flashes
                ->groupBy(fn($ascend) => $ascend->climb->grade)
                ->sortKeysDesc()
                ->mapWithKeys(fn($ascends, $key) => [$key => count($ascends)]);

            $this->chartData[$name] = [
                array_pad($allAscends->values()->toArray(), -6, 0),
                array_pad($flashes->values()->toArray(), -6, 0),
            ];
        }
    }

    private function createStrengthHistory()
    {
        $this->strengthHistory = [];

        foreach ($this->climbers as $name => $climberIds) {
            $strengthHistory = $this->topLoggerService->getStrengthHistory($climberIds['id']);

            $this->strengthHistory['data'][] = collect($strengthHistory->data)
                ->pluck('adjusted_grade')
                ->skip(3)
                ->map(fn($grade) => (float)$grade ?: 0)
                ->values();
            $this->strengthHistory['labels'] = collect($strengthHistory->data)
                ->pluck('date')
                ->skip(3)
                ->map(fn($date) => (new Carbon($date))->format('d-m-Y'))
                ->values();
        }

        $combined = collect(collect($this->strengthHistory['data'])->first())->zip(collect($this->strengthHistory['data'])->last());
        $this->strengthHistoryDifference = $combined->map(fn($pair) => $pair[0] - $pair[1])->toArray();
    }

    private function generateGymData()
    {
        $ascends = collect();
        foreach ($this->climbers as $climber) {
            $ascends = $ascends->merge($this->topLoggerService->getAscends($climber['uid']));
        }

        $gymIds = $ascends->sortByDesc(fn($ascend) => new Carbon($ascend->date_logged))
            ->unique('climb.gym_id')
            ->mapWithKeys(fn($ascend) => [$ascend->climb->gym_id => new Carbon($ascend->date_logged)]);
        $gyms = collect();

        /** @var Carbon $lastSessionAt */
        foreach ($gymIds as $gymId => $lastSessionAt) {
            $gym = $this->topLoggerService->getGym($gymId);
            $climbsSinceLastSession = collect($this->topLoggerService->getClimbs($gymId))
                ->filter(fn($climb) => (new Carbon($climb->date_set))->isAfter($lastSessionAt));
            $gym->new_climbs = $climbsSinceLastSession;
            $gym->last_session_at = $lastSessionAt;
            $gym->weeks_since_last_session = $lastSessionAt->diffInWeeks();

            $allAscends = $gym->new_climbs
                ->filter(fn($climb) => $climb->grade >= 6 && $climb->grade < 7)
                ->groupBy(fn($climb) => $climb->grade)
                ->sortKeys()
                ->mapWithKeys(fn($ascends, $key) => [$key => count($ascends)]);

            $chartData = array_merge([
                '6.0' => 0,
                '6.17' => 0,
                '6.33' => 0,
                '6.5' => 0,
                '6.67' => 0,
                '6.83' => 0,
            ], $allAscends->toArray());

            $gym->chart_data = array_values($chartData);

            $gyms->push($gym);

        }

        $this->gyms = $gyms->toArray();
    }

    private function getClimberName($id): string
    {
        foreach ($this->climbers as $name => $climber) {
            if ($climber['uid'] == $id || $climber['id'] == $id) {
                return $name;
            }
        }

        return '???';
    }
}
