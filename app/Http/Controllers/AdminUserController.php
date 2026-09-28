<?php

namespace App\Http\Controllers;

use App\Models\Dinas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna sistem (Super Admin only).
     */
    public function index(Request $request)
    {
        $query = User::with('dinas')->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('dinas_id')) {
            $query->where('dinas_id', $request->query('dinas_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();
        $dinasList = Dinas::orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'dinasList' => $dinasList,
        ]);
    }

    /**
     * Tampilkan formulir pembuatan pengguna baru.
     */
    public function create()
    {
        $dinasList = Dinas::orderBy('name')->get();

        return view('admin.users.create', [
            'dinasList' => $dinasList,
        ]);
    }

    /**
     * Simpan pengguna baru ke database (PRD 4.2 & ROLES_RBAC 4.3).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['super_admin', 'admin_dinas'])],
            'dinas_id' => [
                'required_if:role,admin_dinas',
                'nullable',
                'exists:dinas,id',
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'dinas_id.required_if' => 'Dinas wajib dipilih untuk akun Admin Kedinasan.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'dinas_id' => $validated['role'] === 'admin_dinas' ? $validated['dinas_id'] : null,
            'code' => $validated['code'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$validated['name']} berhasil dibuat.");
    }

    /**
     * Tampilkan formulir ubah data pengguna.
     */
    public function edit(User $user)
    {
        $dinasList = Dinas::orderBy('name')->get();

        return view('admin.users.edit', [
            'user' => $user,
            'dinasList' => $dinasList,
        ]);
    }

    /**
     * Simpan pembaruan data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['super_admin', 'admin_dinas'])],
            'dinas_id' => [
                'required_if:role,admin_dinas',
                'nullable',
                'exists:dinas,id',
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'dinas_id.required_if' => 'Dinas wajib dipilih untuk akun Admin Kedinasan.',
        ]);

        // Proteksi diri: cegah Super Admin mengunci dirinya sendiri
        if ($user->id === auth()->id()) {
            if ($validated['role'] !== 'super_admin') {
                return redirect()->back()->withInput()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
            }
            if ($validated['status'] !== 'aktif') {
                return redirect()->back()->withInput()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'dinas_id' => $validated['role'] === 'admin_dinas' ? $validated['dinas_id'] : null,
            'code' => $validated['code'] ?? null,
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        return redirect()->route('admin.users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus akun pengguna sistem.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$userName} berhasil dihapus.");
    }
}
