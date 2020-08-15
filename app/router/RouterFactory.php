<?php

use Nette\Application\Routers;

/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new Routers\RouteList();

		$router[] = new Routers\Route('/sitemap.xml',array(
			'presenter' => 'Frontend',
			'action' => 'sitemap',
		), Routers\Route::SECURED);

		$router[] = new Routers\Route('/ajax/',array(
			'presenter' => 'Ajax',
			'action' => 'default',
		), Routers\Route::SECURED);

		$router[] = new Routers\Route('/download/<hash>',array(
			'presenter' => 'Frontend',
			'action' => 'download',
		), Routers\Route::SECURED);

		$router[] = new Routers\Route('/admin/',array(
			'presenter' => 'Frontend',
			'action' => 'admin',
		), Routers\Route::SECURED);

		$router[] = new Routers\Route('/<url .+>.html',array(
				'presenter' => 'Frontend',
				'action' => 'default'
		), Routers\Route::SECURED);

		$router[] = new Routers\Route('',array(
				'presenter' => 'Frontend',
				'action' => 'default'
		), Routers\Route::SECURED);

		return $router;
	}

}
