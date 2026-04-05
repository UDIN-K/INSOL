<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function getLogin()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function postLogin()
    {
        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        if ($username === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Username dan password wajib diisi.');
        }

        $user = (new UserModel())->where('username', $username)->first();

        if (! $user || ! password_verify($password, (string) $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Login gagal.');
        }

        session()->set([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'nama' => $user['nama'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function getLogout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
