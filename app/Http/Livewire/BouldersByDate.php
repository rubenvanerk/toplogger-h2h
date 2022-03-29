<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use RubenVanErk\TopLoggerPhpSdk\TopLogger;

class BouldersByDate extends Component
{
    public Collection $climbs;
    public Collection $ascends;
    public Collection $ascendsByDate;
    public string $userId = '';
    public int $gymId = 8;

    public array $subgrades = [
        0 => 'a',
        1 => 'a+',
        2 => 'b',
        3 => 'b+',
        4 => 'c',
        5 => 'c+',
    ];

    public function mount()
    {
        $this->ascendsByDate = collect();
    }

    public function render()
    {
        return view('livewire.boulders-by-date');
    }

    public function updated()
    {
        if (!$this->userId || !$this->gymId) {
            return;
        }
        $this->ascends = $this->getAscends();

        $groupedAscends = $this->ascends;
        $groupedAscends = $groupedAscends->map(function ($ascend) {
            $grade = (float)$ascend->climb->grade;
            $mainGrade = (string)floor($grade);
            $subGrade = $this->subgrades[floor(fmod($grade, 1) / 0.16)];
            $ascend->climb->grade = $mainGrade . $subGrade;

            return $ascend;
        })->sortByDesc(fn($ascend) => $ascend->climb->grade)
            ->groupBy(fn($ascend, $key) => (new Carbon($ascend->date_logged))->format('Y-m-d'))
            ->sortKeysDesc();

        $this->ascendsByDate = $groupedAscends;
    }

    public function getAscends(): Collection
    {
        return collect(
            (new TopLogger())->ascends()
                ->filter(['used' => true])
                ->filter(['user' => ['uid' => $this->userId]])
                ->filter(['climb' => ['gym_id' => $this->gymId]])
                ->param(['serialize_checks' => true])
                ->include(['climb' => ['hold']])
                ->get()
        );
    }
}
