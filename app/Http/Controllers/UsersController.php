<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        $u = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $u->role = $data['role'];
        $u->save();

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            abort(403);
        }

        $role = $request->query('role');
        $search = trim((string) $request->query('q', ''));
        $query = User::query();
        if (in_array($role, array_keys(User::ROLES), true)) {
            $query->where('role', $role);
        }
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $users = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(20)->withQueryString();
        $roleStats = User::query()
            ->selectRaw(
                "COUNT(*) as total, SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins, SUM(CASE WHEN role = 'director' THEN 1 ELSE 0 END) as directors, SUM(CASE WHEN role = 'docente' THEN 1 ELSE 0 END) as teachers, SUM(CASE WHEN role = 'alumno' THEN 1 ELSE 0 END) as students"
            )
            ->first();
        $stats = [
            'total' => (int) $roleStats->total,
            'admins' => (int) $roleStats->admins,
            'directors' => (int) $roleStats->directors,
            'teachers' => (int) $roleStats->teachers,
            'students' => (int) $roleStats->students,
        ];

        return view('dash.users_index', compact('users', 'role', 'search', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            abort(403);
        }

        return view('dash.users_create');
    }

    public function edit($id)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            abort(403);
        }

        $u = User::findOrFail($id);

        return view('dash.user_edit', compact('u'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            abort(403);
        }

        $u = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$u->id,
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        if ($u->studentProfile()->exists() && $data['role'] !== 'alumno') {
            return back()->withInput()->withErrors([
                'role' => 'Esta cuenta está vinculada a una ficha de alumno. Desvincúlala antes de cambiar su rol.',
            ]);
        }

        $u->name = $data['name'];
        $u->email = $data['email'];
        $u->role = $data['role'];
        if (! empty($data['password'])) {
            $u->password = Hash::make($data['password']);
        }
        $u->save();

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            abort(403);
        }

        $u = User::findOrFail($id);
        if ($u->id === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }
        $u->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado.');
    }
}
