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

class BouldersByDate extends Component
{
    public Collection $ascendsByDate;
    public array $climberStats = [];

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
                $ascend->climb->grade_font = GradeConverter::toFont((float)$ascend->climb->grade);
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
        foreach ($this->climberIds as $id => $climber) {
            $ascends = collect($this->getAscends(array_flip($this->climberUids)[$climber]));

            $sessionCount = $ascends
                ->groupBy(fn($ascend) => (new Carbon($ascend->date_logged))->format('Y-m-d'))
                ->count();

            $stats = $this->getStats($id);
            $stats->top_ten = collect($stats->top_ten)->map(function ($ascend) {
                $ascend->grade_font = GradeConverter::toFont((float)$ascend->grade);
                return $ascend;
            })->sortByDesc(fn($ascend) => (float)$ascend->grade);

            $this->climberStats[$id] = [
                'sessionCount' => $sessionCount,
                'tops' => $ascends->count(),
                'grade_font' => GradeConverter::toFont((float)$stats->grade),
                'grade_progress' => GradeConverter::getProgress((float)$stats->grade),
                'top_ten' => $stats->top_ten,
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

    protected function getStats(string $userId): stdClass
    {
        return Cache::rememberForever(
            'stats' . $userId,
            fn() => (new TopLogger())->users()->stats($userId)
        );
    }
}
