<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class BouldersByDate extends Component
{
    public string $date;
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
        $this->climbs = $this->getClimbs();
        $this->ascends = $this->getAscends();

        $groupedAscends = $this->ascends;
        $groupedAscends = $groupedAscends->map(function ($ascend) {
            $ascend->climb = $this->climbs->firstWhere(fn($climb) => $climb->id == $ascend->climb_id);

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

    public function getClimbs(): Collection
    {
        $response = Http::get('https://api.toplogger.nu/v1/gyms/' . $this->gymId . '/climbs.json?json_params={"filters":{"deleted":false,"live":true}}');

        return collect(json_decode($response->body()));
    }

    public function getAscends(): Collection
    {
        $response = Http::get('https://api.toplogger.nu/v1/ascends.json?json_params={"filters":{"used":true,"user":{"uid":"' . $this->userId . '"},"climb":{"gym_id":' . $this->gymId . ',"deleted":false,"live":true}}}&serialize_checks=true');

        return collect(json_decode($response->body()));
    }
}
