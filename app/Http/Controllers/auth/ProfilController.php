<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\PasswordRequest;
use App\Http\Requests\auth\ProfilRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('auth.profil.edit', [
            'user' => $user,
            'layout' => $user->area()->layout(),
        ]);
    }

    public function update(ProfilRequest $request): RedirectResponse
    {
        $user = $request->user();
        $attributes = ['username' => $request->validated('username')];

        if ($request->hasFile('photo')) {
            $attributes['photo_path'] = $request->file('photo')->store('profile-photos', 'public');

            if ($user->photo_path !== null) {
                Storage::disk('public')->delete($user->photo_path);
            }
        }

        $user->update($attributes);

        return redirect()->route('profil.edit')->with('status', 'Profil disimpan.');
    }

    public function updatePassword(PasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return redirect()->route('profil.edit')->with('status', 'Password diganti.');
    }
}
