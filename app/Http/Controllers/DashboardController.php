<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Staff;
use App\Models\Classe;
use App\Models\FeeMaster;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students' => Student::count(),
            'staff' => Staff::count(),
            'classes' => Classe::count(),
            'fees_collected' => FeeMaster::sum('amount'),
        ];

        return view('dashboard', compact('stats'));
    }
}
