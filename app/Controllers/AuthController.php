<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    private function getSafeNextTarget(string $next): string
    {
        $next = trim($next);
        if ($next === '' || ! str_starts_with($next, '/')) {
            return '/dashboard';
        }

        if (str_starts_with($next, '//')) {
            return '/dashboard';
        }

        return $next;
    }

    public function getLogin()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        $next = $this->getSafeNextTarget((string) $this->request->getGet('next'));
        return view('auth/login', ['next' => $next]);
    }

    public function postLogin()
    {
        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');
        $next = $this->getSafeNextTarget((string) $this->request->getPost('next'));
        $loginRedirect = '/login?next=' . rawurlencode($next);

        $validation = service('validation');
        $validation->setRules([
            'username' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[6]|max_length[100]',
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->to($loginRedirect)
                ->withInput()
                ->with('validation_errors', $validation->getErrors())
                ->with('error', 'Validasi login gagal.');
        }

        if ($username === '' || $password === '') {
            return redirect()->to($loginRedirect)->withInput()->with('error', 'Username dan password wajib diisi.');
        }

        $user = (new UserModel())->where('username', $username)->first();

        if (! $user || ! password_verify($password, (string) $user['password'])) {
            return redirect()->to($loginRedirect)->withInput()->with('error', 'Login gagal.');
        }

        session()->set([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'nama' => $user['nama'],
        ]);

        return redirect()->to($next);
    }

    public function getLogout()
    {
        session()->destroy();
        return redirect()->to('/dashboard');
    }
}
