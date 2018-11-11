<?php

/**
 * Frontpage controller.
 */
class FrontpageController extends Core\Controller
{
	/**************************************************************************/
	public function indexAction()
	{
		if (!$this->authorize('role:user'))
		{
			/* unauthorized, forward to login */
			// throw new Exception403();
		}
		return $this->render('frontpage.html');
	}

	/**************************************************************************/
	public function notFoundAction()
	{
		return $this->render('404.html');
	}

}
