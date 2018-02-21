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

            $router[] = new Routers\Route('/sitemap.xml', array(
                'presenter' => 'Frontend',
                'action' => 'sitemap',
            ));

            $router[] = new Routers\Route('/ajax/', array(
                'presenter' => 'Ajax',
                'action' => 'default',
            ));

            $router[] = new Routers\Route('/download/<hash>', array(
                'presenter' => 'Frontend',
                'action' => 'download',
            ));

            $router[] = new Routers\Route('/admin/', array(
                'presenter' => 'Frontend',
                'action' => 'admin',
            ));

            $router[] = new Routers\Route('/<.+>', 'Frontend:default', Routers\Route::ONE_WAY);

            $router[] = new Routers\Route('', array(
                'presenter' => 'Frontend',
                'action' => 'default'
            ));
        }

		return $router;
	}

}
