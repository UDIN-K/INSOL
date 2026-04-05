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
        return redirect()->to('/dashboard?login=1&next=' . rawurlencode($next));
    }

    public function postLogin()
    {
        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');
        $isPopup = ((string) $this->request->getGet('popup')) === '1' || ((string) $this->request->getPost('popup')) === '1';
        $next = $this->getSafeNextTarget((string) $this->request->getPost('next'));

        $popupRedirect = '/dashboard?login=1&next=' . rawurlencode($next);

        if ($username === '' || $password === '') {
            if ($isPopup) {
                return redirect()->to($popupRedirect)->withInput()->with('error', 'Username dan password wajib diisi.');
            }

            return redirect()->to($popupRedirect)->withInput()->with('error', 'Username dan password wajib diisi.');
        }

        $user = (new UserModel())->where('username', $username)->first();

        if (! $user || ! password_verify($password, (string) $user['password'])) {
            if ($isPopup) {
                return redirect()->to($popupRedirect)->withInput()->with('error', 'Login gagal.');
            }

            return redirect()->to($popupRedirect)->withInput()->with('error', 'Login gagal.');
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
