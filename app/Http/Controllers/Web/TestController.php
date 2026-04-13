<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function layout()
    {
        $breadcrumbs = [
            ['label' => 'Test', 'url' => '#'],
            ['label' => 'Layout Test']
        ];
        
        return view('test-layout', [
            'pageTitle' => 'Layout Test',
            'breadcrumbs' => $breadcrumbs
        ]);
    }
}