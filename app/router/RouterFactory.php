<?php

use Nette\Application\Routers;

/**
 * Router factory.
 */
class RouterFactory
{
    private $container;

    public function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

	/**
	 * @return Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new Routers\RouteList();

        if ($this->container->parameters['consoleMode']) {
            $router[] = new Routers\CliRouter(['action' => 'Cli:default']);
        } else {

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

		$router[] = new Routers\Route('/<url kontakty-a-chat>.html',array(
			'presenter' => 'Frontend',
			'action' => 'contact'
		), Routers\Route::SECURED);

		$router[] = new Routers\Route('/<url uvod>.html',array(
			'presenter' => 'Frontend',
			'action' => 'home'
		), Routers\Route::SECURED);

		$router[] = new Routers\Route('/<url koncerty>.html',array(
			'presenter' => 'Frontend',
			'action' => 'concerts'
		), Routers\Route::SECURED);

		$router[] = new Routers\Route('/<url .+>.html',array(
				'presenter' => 'Frontend',
				'action' => 'default'
		), Routers\Route::SECURED);

		$router[] = new Routers\Route('',array(
				'presenter' => 'Frontend',
				'action' => 'home'
		), Routers\Route::SECURED);
        }

		return $router;
	}

}
