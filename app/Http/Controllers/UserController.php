<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Timkerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['timkerja.subjek', 'creator', 'editor'])
            ->orderBy('nama')
            ->get();
        $timkerja = Timkerja::all();

        return view('pages.admin.user.index', compact('users', 'timkerja'));
    }

    public function create()
    {
        $timkerja = Timkerja::orderBy('nama_timkerja')->get();

        return view('pages.admin.user.tambah_user', compact('timkerja'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'username' => 'required|unique:tb_user,username',
            'nip' => 'required|unique:tb_user,nip',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:admin,operator,viewer',
            'id_timkerja' => 'nullable|required_if:role,operator|exists:tb_timkerja,id_timkerja',
        ]);

        $validated['role'] = $validated['role'] ?? 'viewer';

        if (in_array($validated['role'], ['admin', 'viewer'], true)) {
            $validated['id_timkerja'] = null;
        }

        User::create([
            'nama' => $validated['nama'],
            'username' => $validated['username'],
            'nip' => $validated['nip'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'id_timkerja' => $validated['id_timkerja'] ?? null,
            'created_date' => now(),
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'username' => 'required|unique:tb_user,username,' . $id . ',id_user',
            'nip' => 'required|unique:tb_user,nip,' . $id . ',id_user',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,operator,viewer',
            'id_timkerja' => 'nullable|required_if:role,operator|exists:tb_timkerja,id_timkerja',
        ]);

        if (in_array($validated['role'], ['admin', 'viewer'], true)) {
            $validated['id_timkerja'] = null;
        }

        $user = User::findOrFail($id);

        $data = [
            'nama' => $validated['nama'],
            'username' => $validated['username'],
            'nip' => $validated['nip'],
            'role' => $validated['role'],
            'id_timkerja' => $validated['id_timkerja'] ?? null,
            'modified_date' => now(),
            'modified_by' => Auth::id(),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return back()->with('success', 'User berhasil diupdate');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return back()->with('success', 'User berhasil dihapus');
    }

    public function show($id)
    {
        return redirect()->route('admin.user.index');
    }

    public function edit($id)
    {
        return redirect()->route('admin.user.index');
    }
}
