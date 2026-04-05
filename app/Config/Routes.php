<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('login', 'AuthController::getLogin');
$routes->post('login', 'AuthController::postLogin');
$routes->get('logout', 'AuthController::getLogout');
$routes->get('login.php', 'AuthController::getLogin');
$routes->post('login.php', 'AuthController::postLogin');
$routes->get('logout.php', 'AuthController::getLogout');
$routes->get('index.php/login', 'AuthController::getLogin');
$routes->post('index.php/login', 'AuthController::postLogin');
$routes->get('index.php/login.php', 'AuthController::getLogin');
$routes->post('index.php/login.php', 'AuthController::postLogin');
$routes->get('index.php/logout', 'AuthController::getLogout');
$routes->get('index.php/logout.php', 'AuthController::getLogout');

$routes->group('', static function ($routes): void {
	$routes->get('/', 'DashboardController::getIndex');
	$routes->get('dashboard', 'DashboardController::getIndex');

	$routes->get('mahasiswa', 'MahasiswaController::getIndex');
	$routes->get('mahasiswa/create', 'MahasiswaController::getCreate');
	$routes->post('mahasiswa/store', 'MahasiswaController::postStore');
	$routes->get('mahasiswa/edit/(:num)', 'MahasiswaController::getEdit/$1');
	$routes->post('mahasiswa/update/(:num)', 'MahasiswaController::postUpdate/$1');
	$routes->post('mahasiswa/delete/(:num)', 'MahasiswaController::postDelete/$1');

	$routes->get('kriteria', 'KriteriaController::getIndex');
	$routes->get('kriteria/create', 'KriteriaController::getCreate');
	$routes->post('kriteria/store', 'KriteriaController::postStore');
	$routes->get('kriteria/edit/(:num)', 'KriteriaController::getEdit/$1');
	$routes->post('kriteria/update/(:num)', 'KriteriaController::postUpdate/$1');
	$routes->post('kriteria/delete/(:num)', 'KriteriaController::postDelete/$1');
	$routes->get('kriteria/detail/create', 'KriteriaController::getDetailCreate');
	$routes->post('kriteria/detail/store', 'KriteriaController::postDetailStore');
	$routes->get('kriteria/detail/edit/(:num)', 'KriteriaController::getDetailEdit/$1');
	$routes->post('kriteria/detail/update/(:num)', 'KriteriaController::postDetailUpdate/$1');
	$routes->post('kriteria/detail/delete/(:num)', 'KriteriaController::postDetailDelete/$1');

	$routes->get('penilaian', 'PenilaianController::getIndex');
	$routes->get('penilaian/input/(:num)', 'PenilaianController::getInput/$1');
	$routes->post('penilaian/save/(:num)', 'PenilaianController::postSave/$1');

	$routes->get('hasil', 'HasilController::getIndex');
	$routes->post('hasil/proses', 'HasilController::postProses');
});
