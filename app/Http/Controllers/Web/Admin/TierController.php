<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tier;
use Illuminate\Http\Request;

class TierController extends Controller
{
    public function index()
    {
        $tiers = Tier::orderBy('min_points', 'desc')->paginate(5);
        return view('Contents.Tiers.index', compact('tiers'));
    }

    public function create()
    {
        return view('Contents.Tiers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tier_name' => 'required|string|max:50|unique:tiers,tier_name',
            'min_points' => 'required|integer|min:0',
            'color_theme' => 'required|string|in:blue,gold,silver,bronze,red,purple,green,orange,pink,cyan,indigo,teal,lime,amber,rainbow',
            'icon' => 'required|string|max:50',
        ]);

        Tier::create($request->only('tier_name', 'min_points', 'color_theme', 'icon', 'description'));

        return redirect()->route('admin.tiers.index')
                         ->with('swal_success_crud', 'Tier baru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $tier = Tier::findOrFail($id);
        return view('Contents.Tiers.edit', compact('tier'));
    }

    public function update(Request $request, $id)
    {
        $tier = Tier::findOrFail($id);

        $request->validate([
            'tier_name' => 'required|string|max:50|unique:tiers,tier_name,' . $id . ',tier_id',
            'min_points' => 'required|integer|min:0',
            'color_theme' => 'required|string|in:blue,gold,silver,bronze,red,purple,green,orange,pink,dark,rainbow',
            'icon' => 'required|string|max:50',
        ]);

        $tier->update($request->only('tier_name', 'min_points', 'color_theme', 'icon', 'description'));

        return redirect()->route('admin.tiers.index')
                         ->with('swal_success_crud', 'Tier berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Tier::destroy($id);
        return redirect()->route('admin.tiers.index')
                         ->with('swal_success_crud', 'Tier berhasil dihapus.');
    }
}