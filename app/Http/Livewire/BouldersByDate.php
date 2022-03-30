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

    public function mount(): void
    {
        $this->createDataset();
    }

    public function render(): View
    {
        return view('livewire.boulders-by-date');
    }

    public function getAscends($userId): Collection
    {
        return collect(
            (new TopLogger())->ascends()
                ->filter(['used' => true])
                ->filter(['user' => ['uid' => $userId]])
                ->param(['serialize_checks' => true])
                ->include(['climb'])
                ->get()
        );
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
            $this->ascendsByDate = $this->ascendsByDate->merge(
                Cache::rememberForever(
                    'ascends' . $uid,
                    fn() => $this->getAscends($uid)
                )
            );
        }

        // set grade to font & sort by grade
        $this->ascendsByDate = $this->ascendsByDate
            ->sortByDesc(fn($ascend) => $ascend->climb->grade)
            ->map(function ($ascend) {
                $grade = (float)$ascend->climb->grade;
                $mainGrade = floor($grade);

                $subGrade = $this->subgrades[floor(($grade - $mainGrade) / 0.16)] ?? '?';

                $ascend->climb->grade = $mainGrade . $subGrade;
                $ascend->climb->gym_name = $this->getGym($ascend->climb->gym_id)->name;
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

    protected function getGym($gymId)
    {
        return Cache::rememberForever(
            'gyms' . $gymId,
            fn() => (new TopLogger())->gyms()->include(['holds', 'walls'])->find($gymId)
        );
    }
}
