<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Mailer framework for Contao
 * Copyright (C) 2010,2011,2012 Tristan Lins
 *
 * Extension for:
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 * 
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  InfinitySoft 2010,2011,2012
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    Mailer
 * @license    LGPL
 * @filesource
 */


/**
 * Class Mailer
 *
 * A basic email that is just an email.
 *
 * @copyright  InfinitySoft 2010,2011,2012
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    Mailer
 */
abstract class Mailer extends System
{
	/**
	 * @static
	 * @param MailerConfig|null $config
	 * @return Mailer
	 */
	public static function getMailer(MailerConfig $config = null)
	{
		if ($config === null) {
			$config = MailerConfig::getDefault();
		}
		$strImpl = $config->getImplementation();
		$strClass = $GLOBALS['TL_MAILER'][$strImpl];
		$objMailer = new $strClass($config);
		return $objMailer;
	}

	/**
	 * The mailer configuration
	 *
	 * @var MailerConfig
	 */
	protected $config;

	/**
	 * Create a new mailer
	 *
	 * @param MailerConfig $config
	 */
	public function __construct(MailerConfig $config)
	{
		$this->config = $config;
	}

	/**
	 *
	 * @param mixed
	 * @return boolean
	 */
	public abstract function send(Mail $objEmail, $varTo, $varCC = null, $varBCC = null);
}
