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
    }

    public function refreshData(): void
    {
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
            ->groupBy(fn($ascend) => (new Carbon($ascend->date_logged))->format('Y-m-d'))
            ->sortKeysDesc();

        // group by user
        $this->ascendsByDate = $this->ascendsByDate->map(function (Collection $groupedAscends) {
            return $groupedAscends->groupBy(fn ($ascend) => $this->getClimberName($ascend->user_id));
        });
    }

    protected function createStats(): void
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
        foreach ($this->climbers as $climberIds) {
            $ascends = $this->topLoggerService->getAscends($climberIds['uid']);

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

    protected function getClimberName($id): string
    {
        foreach ($this->climbers as $name => $climber) {
            if ($climber['uid'] == $id || $climber['id'] == $id) {
                return $name;
            }
        }

        return '???';
    }
}
