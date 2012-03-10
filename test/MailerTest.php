<?php

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
 * Class MailerTest
 *
 * A basic email that is just an email.
 *
 * @copyright  InfinitySoft 2010,2011,2012
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    Mailer
 */
class MailerTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		// init contao config
		if (!defined('TL_ROOT')) {
			define('TL_MODE', 'FE');

			// custom autoloader is required, because __autoload is not called
			// after an spl_autoloader is registered!
			spl_autoload_register(function($strClassName) {
				/**
				 * Thanks to Leo Feyer <http://www.contao.org>
				 * @see Contao __autoload function
				 */
				$strLibrary = TL_ROOT . '/system/libraries/' . $strClassName . '.php';

				// Check for libraries first
				if (file_exists($strLibrary))
				{
					include_once($strLibrary);
					return;
				}

				// Then check the modules folder
				foreach (scan(TL_ROOT . '/system/modules/') as $strFolder)
				{
					if (substr($strFolder, 0, 1) == '.')
					{
						continue;
					}

					$strModule = TL_ROOT . '/system/modules/' . $strFolder . '/' . $strClassName . '.php';

					if (file_exists($strModule))
					{
						include_once($strModule);
						return;
					}
				}
			});

			$_SERVER['ORIG_SCRIPT_NAME'] = '';
			$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-en';
			require('system/initialize.php');
			$_SESSION['FE_DATA'] = '';

			// set some variables used by MailerConfig
			$GLOBALS['objPage'] = new stdClass();
			$GLOBALS['objPage']->adminEmail = 'webmaster@example.com';
			$GLOBALS['TL_CONFIG']['adminEmail'] = 'admin@example.com';
			$GLOBALS['TL_CONFIG']['mailer_embed_images'] = true;
			$GLOBALS['TL_CONFIG']['mailer_embed_images_size'] = 4096;
			$GLOBALS['TL_CONFIG']['mailer_implementation'] = 'Implementation';
			$GLOBALS['TL_CONFIG']['useSMTP'] = true;
			$GLOBALS['TL_CONFIG']['smtpHost'] = 'mx.example.com';
			$GLOBALS['TL_CONFIG']['smtpUser'] = 'mail';
			$GLOBALS['TL_CONFIG']['smtpPassword'] = 'passwd';
			$GLOBALS['TL_CONFIG']['smtpEnc'] = 'ssl';
			$GLOBALS['TL_CONFIG']['smtpPort'] = 465;

			require('plugins/swiftmailer/classes/Swift/DependencyContainer.php');
			require('plugins/swiftmailer/classes/Swift/Preferences.php');
			require('plugins/swiftmailer/swift_init.php');
			require(__DIR__ . '/../src/system/modules/mailer/MailerConfig.php');
			require(__DIR__ . '/../src/system/modules/mailer/Mailer.php');
			require(__DIR__ . '/../src/system/modules/mailer/SwiftMailer.php');
			require(__DIR__ . '/../src/system/modules/mailer/Mail.php');
		}
	}

	public function testDefaultMailerConfig()
	{
		echo "Testing MailerConfig::getDefault()\n";

		$objConfig = MailerConfig::getDefault();
		$this->assertEquals('webmaster@example.com', $objConfig->getDefaultSender());
		$this->assertTrue($objConfig->getEmbedImages());
		$this->assertEquals(4096, $objConfig->getEmbedImageSize());
		$this->assertEquals('Implementation', $objConfig->getImplementation());
		$this->assertTrue($objConfig->getUseSMTP());
		$this->assertEquals('mx.example.com', $objConfig->getSmtpHost());
		$this->assertEquals('mail', $objConfig->getSmtpUser());
		$this->assertEquals('passwd', $objConfig->getSmtpPassword());
		$this->assertEquals('ssl', $objConfig->getSmtpEncryption());
		$this->assertEquals(465, $objConfig->getSmtpPort());
	}

	public function testCustomMailerConfig()
	{
		echo "Testing new MailerConfig()\n";

		$objConfig = new MailerConfig();

		$objConfig->setDefaultSender('mail@example.com');
		$objConfig->setDefaultSenderName('Mailer Daemon');
		$objConfig->setEmbedImages('yes');
		$objConfig->setEmbedImageSize(2048);
		$objConfig->setImplementation('Impl');
		$objConfig->setUseSMTP('no');
		$objConfig->setSmtpHost('mail.example.com');
		$objConfig->setSmtpUser('maildaemon');
		$objConfig->setSmtpPassword('secret');
		$objConfig->setSmtpEncryption('none');
		$objConfig->setSmtpPort(12345);

		$this->assertEquals('mail@example.com', $objConfig->getDefaultSender());
		$this->assertEquals('Mailer Daemon', $objConfig->getDefaultSenderName());
		$this->assertTrue($objConfig->getEmbedImages());
		$this->assertEquals(2048, $objConfig->getEmbedImageSize());
		$this->assertEquals('Impl', $objConfig->getImplementation());
		$this->assertTrue($objConfig->getUseSMTP());
		$this->assertEquals('mail.example.com', $objConfig->getSmtpHost());
		$this->assertEquals('maildaemon', $objConfig->getSmtpUser());
		$this->assertEquals('secret', $objConfig->getSmtpPassword());
		$this->assertEquals('none', $objConfig->getSmtpEncryption());
		$this->assertEquals(12345, $objConfig->getSmtpPort());

		$objConfig->setUseSMTP(false);

		$this->assertFalse($objConfig->getUseSMTP());
	}

	public function testMailerConfigSettersReturnThis()
	{
		echo "Testing MailerConfig::set* return $this\n";

		$objConfig = new MailerConfig();

		$arrMethods = get_class_methods('MailerConfig');
		$arrSetter = preg_grep('#^set#', $arrMethods);

		foreach ($arrSetter as $strSetter) {
			echo "  - MailerConfig::" . $strSetter . "()\n";
			$this->assertEquals(
				$objConfig,
				$objConfig->$strSetter(null),
				'Setter MailerConfig::' . $strSetter . ' does not return $this!'
			);
		}
	}

	public function testMailSettersReturnThis()
	{
		echo "Testing MailerConfig::set* return $this\n";

		$objConfig = new Mail();

		$arrMethods = get_class_methods('Mail');
		$arrSetter = preg_grep('#^set#', $arrMethods);

		foreach ($arrSetter as $strSetter) {
			echo "  - Mail::" . $strSetter . "()\n";
			$this->assertEquals(
				$objConfig,
				$objConfig->$strSetter(null),
				'Setter MailerConfig::' . $strSetter . ' does not return $this!'
			);
		}
	}
}
