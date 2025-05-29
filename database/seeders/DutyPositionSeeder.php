<?php

namespace Database\Seeders;

use App\Models\DutyPosition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DutyPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DutyPosition::create([
            'code' => 'PL',
            'label' => 'Pilot'
        ]);

        DutyPosition::create([
            'code' => 'PC',
            'label' => 'Pilot in Command'
        ]);

        DutyPosition::create([
            'code' => 'MP',
            'label' => 'Maintenance Test Pilot'
        ]);

        DutyPosition::create([
            'code' => 'IP',
            'label' => 'Instructor Pilot'
        ]);

        DutyPosition::create([
            'code' => 'SP',
            'label' => 'Standardization Instructor Pilot'
        ]);

        DutyPosition::create([
            'code' => 'UT',
            'label' => 'Unit Trainer'
        ]);

        DutyPosition::create([
            'code' => 'AOB',
            'label' => 'Aerial Observer'
        ]);

//        DutyPosition::create([
//            'code' => 'CE',
//            'label' => 'Crew Chief, Flight Engineer'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'CP',
//            'label' => 'Copilot'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'FL',
//            'label' => 'Flight Engineer Instructor'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'IE',
//            'label' => 'Instrument Flight Examiner'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'ME',
//            'label' => 'Maintenance Test Flight Evaluator'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'MO',
//            'label' => 'Flight Surgeon / Medical Personnel'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'NCT',
//            'label' => 'Nonrated Crew Trainer'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'SI',
//            'label' => 'Standardization Flight Engineer Instructor'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'XP',
//            'label' => 'Experimental Test Pilot'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'AC',
//            'label' => 'Aircraft Commander'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'AO',
//            'label' => 'Aircraft Operator'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'IO',
//            'label' => 'Instructor Operator'
//        ]);
//
//        DutyPosition::create([
//            'code' => 'SO',
//            'label' => 'Standardization Instructor Operator'
//        ]);
    }
}
