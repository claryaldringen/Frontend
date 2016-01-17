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

		$router[] = new Routers\Route('/ajax/',array(
			'presenter' => 'Ajax',
			'action' => 'default',
		));

		$router[] = new Routers\Route('/<url kontakt|email|contact|e-mail>.html',array(
			'presenter' => 'Frontend',
			'action' => 'contact'
		));

		$router[] = new Routers\Route('/download/<hash>',array(
			'presenter' => 'Frontend',
			'action' => 'download',
		));

		$router[] = new Routers\Route('/admin/',array(
			'presenter' => 'Frontend',
			'action' => 'admin',
		));

		$router[] = new Routers\Route('/<url .+>.html',array(
				'presenter' => 'Frontend',
				'action' => 'default'
		));

		$router[] = new Routers\Route('',array(
				'presenter' => 'Frontend',
				'action' => 'default'
		));

		return $router;
	}

}
