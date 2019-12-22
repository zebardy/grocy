<?php

namespace Grocy\Controllers;

use \Grocy\Services\CalendarService;

class CalendarController extends BaseController
{
	public function __construct(\Slim\Container $container)
	{
		parent::__construct($container);
	}

	protected $CalendarService = null;

	protected function getCalendarService()
	{
		if($this->CalendarService == null)
		{
			$this->CalendarService = new CalendarService();
		}
		return $this->CalendarService;
	}

	public function Overview(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		return $this->renderPage($response, 'calendar', [
			'fullcalendarEventSources' => $this->getCalendarService()->GetEvents()
		]);
	}
}
