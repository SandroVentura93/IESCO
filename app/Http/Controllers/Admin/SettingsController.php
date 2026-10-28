<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        // Load common keys for the settings form
        $keys = [
            'site_name','site_short_name','site_motto','site_year','site_director',
            'color_primary','color_secondary','social_facebook','social_instagram','social_youtube','social_twitter',
            // Homepage editable elements (inicio)
            'inicio_content','inicio_video','inicio_image',
            // Quiénes somos
            'quienes_content','quienes_image','quienes_image_alt',
            // Proyecto IJENT
            'proyecto_content','proyecto_image',
            // Novedades / Publicaciones (editable sections)
            'novedades_content','publicaciones_content'
        ];

        $data = [];
        foreach ($keys as $k) {
            $data[$k] = Setting::get($k, config('school.'.$k) ?? '');
        }

        return view('admin.settings.index', ['settings' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_short_name' => 'nullable|string|max:50',
            'site_motto' => 'nullable|string|max:255',
            'site_year' => 'nullable|numeric',
            'site_director' => 'nullable|string|max:255',
            'color_primary' => 'nullable|string|max:50',
            'color_secondary' => 'nullable|string|max:50',
            'social_facebook' => 'nullable|url',
            'social_instagram' => 'nullable|url',
            'social_youtube' => 'nullable|url',
            'social_twitter' => 'nullable|url',
            // homepage fields
            'inicio_content' => 'nullable|string',
            'inicio_video' => 'nullable|url',
            'inicio_image' => 'nullable|file|image|max:5120',
            'quienes_content' => 'nullable|string',
            'quienes_image' => 'nullable|file|image|max:5120',
            'quienes_image_alt' => 'nullable|string|max:255',
            'proyecto_content' => 'nullable|string',
            'proyecto_image' => 'nullable|file|image|max:5120',
            'novedades_content' => 'nullable|string',
            'publicaciones_content' => 'nullable|string',
        ]);

        // Handle file uploads for homepage images
        if ($request->hasFile('inicio_image')) {
            $path = $request->file('inicio_image')->store('public/settings');
            Setting::set('inicio_image', 
                \Illuminate\Support\Facades\Storage::url($path)
            );
        }
        if ($request->hasFile('quienes_image')) {
            $path = $request->file('quienes_image')->store('public/settings');
            Setting::set('quienes_image', \Illuminate\Support\Facades\Storage::url($path));
        }
        if ($request->hasFile('proyecto_image')) {
            $path = $request->file('proyecto_image')->store('public/settings');
            Setting::set('proyecto_image', \Illuminate\Support\Facades\Storage::url($path));
        }

        // Save other scalar settings
        $allowed = [
            'site_name','site_short_name','site_motto','site_year','site_director',
            'color_primary','color_secondary','social_facebook','social_instagram','social_youtube','social_twitter',
            'inicio_content','inicio_video','quienes_content','quienes_image_alt','proyecto_content','novedades_content','publicaciones_content'
        ];
        foreach ($request->only($allowed) as $k => $v) {
            Setting::set($k, $v);
        }

        return back()->with('success','Configuración guardada.');
    }
}
