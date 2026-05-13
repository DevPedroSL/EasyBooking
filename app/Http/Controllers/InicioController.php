<?php

namespace App\Http\Controllers;

use App\Models\Barbershop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class InicioController extends Controller
{
    public function index(Request $request): View
    {
        $name = trim((string) $request->query('name', ''));
        $address = trim((string) $request->query('address', ''));

        $cacheVersion = Cache::get('public_barbershops_version', '1');
        $cacheKey = 'public_barbershops:created_at_asc:'.$cacheVersion.':'.md5(json_encode([$name, $address]));

        $barbershops = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($name, $address) {
            return Barbershop::query()
                ->publiclyVisible()
                ->when($name !== '', function ($query) use ($name) {
                    $query->where('name', 'like', '%'.$name.'%');
                })
                ->when($address !== '', function ($query) use ($address) {
                    $query->where('address', 'like', '%'.$address.'%');
                })
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();
        });

        return view('inicio', compact('barbershops', 'name', 'address'));
    }
}
