<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('storefront.contact', [
            'departments' => Department::active()->orderBy('sort_order')->get(),
        ]);
    }
}
