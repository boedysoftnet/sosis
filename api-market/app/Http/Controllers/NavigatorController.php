<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\Navigator;
use Illuminate\Http\Request;

class NavigatorController extends Controller
{
    public function index()
    {
        $all=\request('all');
        $data = Navigator::query();
        if (!$all) {
            foreach (\request()->user()->departement->rules as $key => $value) {
                if ($value==true) $data->orWhere('id', $key);
            }
        }
        return Boedysoft::getJson(200, 'Informasi', $data->orderBy('sort_by')->get());
    }
}
