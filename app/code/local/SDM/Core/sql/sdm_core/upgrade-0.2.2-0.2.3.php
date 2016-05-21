<?php
/**
 * Separation Degrees Media
 *
 * SDM's core extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Core
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

// No longer want twitter for now
$this->setConfigData('optimisewebcookienotice/menu/cookienotice_enabled', 0);
$this->setConfigData('optimisewebcookienotice/menu/optimiseweball_enabled', 0);
$this->setConfigData('optimisewebcookienotice/general/enabled', 0);
$this->setConfigData('optimisewebcookienotice/behaviour/autohide_time', 10);
$this->setConfigData('optimisewebcookienotice/general/enabled', 1, 'websites', 3);
$this->setConfigData('optimisewebcookienotice/behaviour/close_text', "<strong>&times;</strong>");
