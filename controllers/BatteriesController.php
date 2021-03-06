<?php

namespace Grocy\Controllers;

use \Grocy\Services\BatteriesService;

class BatteriesController extends BaseController
{
	public function __construct(\Slim\Container $container)
	{
		parent::__construct($container);
	}

	protected $BatteriesService = null;

	protected function getBatteriesService()
	{
		if($this->BatteriesService == null)
		{
			$this->BatteriesService = new BatteriesService();
		}
		return $this->BatteriesService;
	}

	public function Overview(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		$usersService = $this->getUsersService();
		$nextXDays = $usersService->GetUserSettings(GROCY_USER_ID)['batteries_due_soon_days'];

		return $this->renderPage($response, 'batteriesoverview', [
			'batteries' => $this->getDatabase()->batteries()->orderBy('name'),
			'current' => $this->getBatteriesService()->GetCurrent(),
			'nextXDays' => $nextXDays,
			'userfields' => $this->getUserfieldsService()->GetFields('batteries'),
			'userfieldValues' => $this->getUserfieldsService()->GetAllValues('batteries')
		]);
	}

	public function TrackChargeCycle(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		return $this->renderPage($response, 'batterytracking', [
			'batteries' =>  $this->getDatabase()->batteries()->orderBy('name')
		]);
	}

	public function BatteriesList(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		return $this->renderPage($response, 'batteries', [
			'batteries' => $this->getDatabase()->batteries()->orderBy('name'),
			'userfields' => $this->getUserfieldsService()->GetFields('batteries'),
			'userfieldValues' => $this->getUserfieldsService()->GetAllValues('batteries')
		]);
	}

	public function BatteryEditForm(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		if ($args['batteryId'] == 'new')
		{
			return $this->renderPage($response, 'batteryform', [
				'mode' => 'create',
				'userfields' => $this->getUserfieldsService()->GetFields('batteries')
			]);
		}
		else
		{
			return $this->renderPage($response, 'batteryform', [
				'battery' =>  $this->getDatabase()->batteries($args['batteryId']),
				'mode' => 'edit',
				'userfields' => $this->getUserfieldsService()->GetFields('batteries')
			]);
		}
	}

	public function Journal(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		return $this->renderPage($response, 'batteriesjournal', [
			'chargeCycles' => $this->getDatabase()->battery_charge_cycles()->orderBy('tracked_time', 'DESC'),
			'batteries' => $this->getDatabase()->batteries()->orderBy('name')
		]);
	}

	public function BatteriesSettings(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		return $this->renderPage($response, 'batteriessettings');
	}
}
