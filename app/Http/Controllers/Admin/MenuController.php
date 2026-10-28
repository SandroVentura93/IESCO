<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MenuItem;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('items')->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        return view('admin.menus.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menus,slug',
        ]);

        Menu::create($request->only(['title','slug']));
        return redirect()->route('admin.menus.index')->with('success','Menu creado.');
    }

    public function edit($id)
    {
        $menu = Menu::with('items')->findOrFail($id);
        return view('admin.menus.edit', compact('menu'));
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menus,slug,'.$menu->id,
        ]);

        $menu->update($request->only(['title','slug']));

        // handle items arrays if provided
        $titles = $request->input('item_title', []);
        $urls = $request->input('item_url', []);
        $positions = $request->input('item_position', []);

        if (!empty($titles)) {
            // delete existing and recreate for simplicity
            $menu->items()->delete();
            foreach ($titles as $i => $t) {
                if (!trim($t)) continue;
                MenuItem::create([
                    'menu_id' => $menu->id,
                    'title' => $t,
                    'url' => $urls[$i] ?? '#',
                    'position' => intval($positions[$i] ?? $i),
                ]);
            }
        }

        return back()->with('success','Menú actualizado.');
    }

    public function destroy($id)
    {
        Menu::findOrFail($id)->delete();
        return back()->with('success','Menú eliminado.');
    }
}
