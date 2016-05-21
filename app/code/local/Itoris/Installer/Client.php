<?php
/**
 * Separation Degrees One
 *
 * Fixed issue with the Itoris_Installer.
 *
 * @category  SDM
 * @package   Itoris_Installer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

class Itoris_Installer_Client
{
	static function isRegisteredAutonomous()
	{
		return true;
	}

	static function isAdminRegistered()
	{
		return true;
	}

	static function registerCurrentStoreHost()
	{
		return 0;
	}
}
