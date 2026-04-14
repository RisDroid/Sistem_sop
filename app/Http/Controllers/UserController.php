<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subjek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['subjek'])->get();
        $subjek = Subjek::all();
        return view('pages.admin.user.index', compact('users', 'subjek'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'username' => 'required|unique:tb_user,username',
            'nip' => 'required|unique:tb_user,nip',
            'password' => 'required',
            'role' => 'required',
        ]);

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'nip' => $request->nip,
            'password' => $request->password, // Plain Text
            'role' => $request->role,
            'id_subjek' => $request->id_subjek,
            'created_date' => now(),
            'created_by' => Auth::id(),
            'modified_date' => now(),
            'modified_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'username' => 'required|unique:tb_user,username,'.$id.',id_user',
            'nip' => 'required|unique:tb_user,nip,'.$id.',id_user',
            'role' => 'required',
        ]);

        $user = User::findOrFail($id);

        $data = [
            'nama' => $request->nama,
            'username' => $request->username,
            'nip' => $request->nip,
            'role' => $request->role,
            'id_subjek' => $request->id_subjek,
            'modified_date' => now(),
            'modified_by' => Auth::id(),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password; // Plain Text
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Data user berhasil diupdate!');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus!');
    }
}
