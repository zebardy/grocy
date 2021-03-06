<?php

namespace Grocy\Controllers;

use \Grocy\Services\DatabaseService;
use \Grocy\Services\ApplicationService;
use \Grocy\Services\LocalizationService;
use \Grocy\Services\UsersService;
use \Grocy\Services\UserfieldsService;

class BaseController
{
	public function __construct(\Slim\Container $container) {
		#$fp = fopen('/config/data/sql.log', 'a');
        #$time_start = microtime(true);

		$this->AppContainer = $container;
		#fwrite($fp, "%%% Login controller - parent construstor total time : " . round((microtime(true) - $time_start),6) . "\n");
		#fclose($fp);
	}

	protected function render($response, $page, $data = [])
	{
		$container = $this->AppContainer;

		$versionInfo = $this->getApplicationService()->GetInstalledVersion();
		$container->view->set('version', $versionInfo->Version);
		$container->view->set('releaseDate', $versionInfo->ReleaseDate);
		#fwrite($fp, "%%% Login controller - parent construstor application service time : " . round((microtime(true) - $time_start),6) . "\n");

        $localizationService = $this->getLocalizationService();
		$container->view->set('__t', function(string $text, ...$placeholderValues) use($localizationService)
		{
			return $localizationService->__t($text, $placeholderValues);
		});
		$container->view->set('__n', function($number, $singularForm, $pluralForm) use($localizationService)
		{
			return $localizationService->__n($number, $singularForm, $pluralForm);
		});
		$container->view->set('GettextPo', $localizationService->GetPoAsJsonString());

		$container->view->set('U', function($relativePath, $isResource = false) use($container)
		{
			return $container->UrlManager->ConstructUrl($relativePath, $isResource);
		});

		$embedded = false;
		if (isset($container->request->getQueryParams()['embedded']))
		{
			$embedded = true;
		}
		$container->view->set('embedded', $embedded);

		$constants = get_defined_constants();
		foreach ($constants as $constant => $value)
		{
			if (substr($constant, 0, 19) !== 'GROCY_FEATURE_FLAG_')
			{
				unset($constants[$constant]);
			}
		}
		$container->view->set('featureFlags', $constants);

		$this->AppContainer = $container;

		return $this->AppContainer->view->render($response, $page, $data);
	}

	protected function renderPage($response, $page, $data = [])
	{
		$container = $this->AppContainer;
		$container->view->set('userentitiesForSidebar', $this->getDatabase()->userentities()->where('show_in_sidebar_menu = 1')->orderBy('name'));
		try
		{
			$usersService = $this->getUsersService();
			if (defined('GROCY_USER_ID'))
			{
				$container->view->set('userSettings', $usersService->GetUserSettings(GROCY_USER_ID));
			}
			else
			{
				$container->view->set('userSettings', null);
			}
		}
		catch (\Exception $ex)
		{
			// Happens when database is not initialised or migrated...
		}

		$this->AppContainer = $container;
		return $this->render($response, $page, $data);
	}

    protected function getDatabaseService()
	{
		return DatabaseService::getInstance();
	}

    protected function getDatabase()
	{
		return $this->getDatabaseService()->GetDbConnection();
	}

	protected function getLocalizationService()
	{
		return LocalizationService::getInstance(GROCY_CULTURE);
	}

	protected function getApplicationservice()
	{
		return ApplicationService::getInstance();
	}

	private $userfieldsService = null;

	protected function getUserfieldsService()
	{
		if($this->userfieldsService == null)
		{
			$this->userfieldsService = new UserfieldsService();
		}
		return $this->userfieldsService;
	}

	private $usersService = null;

	protected function getUsersService()
	{
		if($this->usersService == null)
		{
			$this->usersService = new UsersService();
		}
		return $this->usersService;
	}

	protected $AppContainer;
}
