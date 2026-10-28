<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StoredFile;
use App\Models\Course;

class ActivityController extends Controller
{
    /**
     * Return a JSON list of recent activity composed from users, files and courses.
     */
    public function recentJson(Request $request)
    {
        $users = User::select('id','name','created_at')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function($u){
                return [
                    'title' => "Nuevo usuario: {$u->name}",
                    'subtitle' => 'Cuenta creada',
                    'type' => 'usuario',
                    'actor_name' => $u->name,
                    'created_at' => $u->created_at->toDateTimeString(),
                ];
            })->toArray();

        $files = [];
        try {
            $files = StoredFile::select('id','name','created_at')
                ->where(function ($query) {
                    $query->whereNull('section')
                        ->orWhere('section', '!=', StoredFile::MEDIA_LIBRARY_SECTION);
                })
                ->orderBy('created_at','desc')
                ->limit(6)
                ->get()
                ->map(function($f){
                    return [
                        'file_id' => $f->id,
                        'delete_url' => route('admin.files.destroy', $f->id),
                        'title' => "Archivo: {$f->name}",
                        'subtitle' => 'Subido al repositorio',
                        'type' => 'archivo',
                        'actor_name' => 'Sistema',
                        'created_at' => $f->created_at->toDateTimeString(),
                    ];
                })->toArray();
        } catch (\Throwable $e) { /* ignore if model/table missing */ }

        $courses = [];
        try {
            $courses = Course::select('id','title','created_at')
                ->orderBy('created_at','desc')
                ->limit(6)
                ->get()
                ->map(function($c){
                    return [
                        'title' => "Curso: {$c->title}",
                        'subtitle' => 'Creado',
                        'type' => 'curso',
                        'actor_name' => 'Profesor',
                        'created_at' => $c->created_at->toDateTimeString(),
                    ];
                })->toArray();
        } catch (\Throwable $e) { }

        // Merge and sort by created_at desc
        $merged = array_merge($users, $files, $courses);
        usort($merged, function($a,$b){
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        $result = array_slice($merged, 0, 8);

        return response()->json(['data' => $result]);
    }
}
