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
	// Unwind the error handler stack until we're back at the built-in error handler.
	// We might want to export all of this into a abstract PHPUnit_Framework_TestCase_Contao base class
	protected function resetPHPErrorHandling()
	{
		restore_exception_handler();
		while (set_error_handler(create_function('$errno,$errstr', 'return false;'))) {
			// Unset the error handler we just set.
			restore_error_handler();
			// Unset the previous error handler.
			restore_error_handler();
		}
		// Restore the built-in error handler.
		restore_error_handler();
	}

	public function autoload($strClassName)
	{
		/**
		 * Thanks to Leo Feyer <http://www.contao.org>
		 * @see Contao __autoload function
		 */
		$strLibrary = TL_ROOT . '/system/libraries/' . $strClassName . '.php';

		// Check for libraries first
		if (file_exists($strLibrary)) {
			include_once($strLibrary);
			return;
		}

		// Then check the modules folder
		foreach (scan(TL_ROOT . '/system/modules/') as $strFolder)
		{
			if (substr($strFolder, 0, 1) == '.') {
				continue;
			}

			$strModule = TL_ROOT . '/system/modules/' . $strFolder . '/' . $strClassName . '.php';

			if (file_exists($strModule)) {
				include_once($strModule);
				return;
			}
		}

		// HOOK: include Swift classes
		if (class_exists('Swift', false))
		{
			Swift::autoload($strClassName);
			return;
		}
	}

	protected function setUp()
	{
		$_SERVER['HTTP_HOST']             = 'www.example.com';
		$_SERVER['HTTP_X_FORWARDED_HOST'] = '';
		$_SERVER['SSL_SESSION_ID']        = '';
		$_SERVER['HTTPS']                 = 'no';
		$_SERVER['ORIG_SCRIPT_NAME']      = 'index.php';
		$_SERVER['HTTP_ACCEPT_LANGUAGE']  = 'en-en';
		$_SERVER['REQUEST_URI']           = '/';

		// init contao config
		if (!defined('TL_ROOT')) {
			define('TL_MODE', 'FE');

			// custom autoloader is required, because __autoload is not called
			// after an spl_autoloader is registered!
			spl_autoload_register(array($this, 'autoload'));

			require('system/initialize.php');
			// registered __exception() handler from contao confuses PHPUnit code coverage generating.
			$this->resetPHPErrorHandling();

			require('plugins/swiftmailer/classes/Swift.php');
			require('plugins/swiftmailer/swift_init.php');
			require(__DIR__ . '/../src/system/modules/mailer/MailerConfig.php');
			require(__DIR__ . '/../src/system/modules/mailer/Mailer.php');
			require(__DIR__ . '/../src/system/modules/mailer/SwiftMailer.php');
			require(__DIR__ . '/../src/system/modules/mailer/Mail.php');
		}

		require(__DIR__ . '/../src/system/modules/mailer/config/config.php');

		// set some variables
		$_SESSION['FE_DATA']                              = '';
		$GLOBALS['objPage']                               = new stdClass();
		$GLOBALS['objPage']->adminEmail                   = 'webmaster@example.com';
		$GLOBALS['objPage']->staticFiles                  = 'http://static.example.com';
		$GLOBALS['objPage']->dns                          = 'www.example.com';
		$GLOBALS['TL_CONFIG']['adminEmail']               = 'admin@example.com';
		$GLOBALS['TL_CONFIG']['rewriteURL']               = true;
		$GLOBALS['TL_CONFIG']['websitePath']              = '';
		$GLOBALS['TL_CONFIG']['mailer_embed_images']      = true;
		$GLOBALS['TL_CONFIG']['mailer_embed_images_size'] = 4096;
		$GLOBALS['TL_CONFIG']['mailer_implementation']    = 'Implementation';
		$GLOBALS['TL_CONFIG']['useSMTP']                  = true;
		$GLOBALS['TL_CONFIG']['smtpHost']                 = 'mx.example.com';
		$GLOBALS['TL_CONFIG']['smtpUser']                 = 'mail';
		$GLOBALS['TL_CONFIG']['smtpPassword']             = 'passwd';
		$GLOBALS['TL_CONFIG']['smtpEnc']                  = 'ssl';
		$GLOBALS['TL_CONFIG']['smtpPort']                 = 465;
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
		echo "Testing MailerConfig::set* return \$this\n";

		$objConfig = new MailerConfig();

		$arrMethods = get_class_methods('MailerConfig');
		$arrSetter  = preg_grep('#^set#', $arrMethods);

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
		echo "Testing MailerConfig::set* return \$this\n";

		$objConfig = new Mail();

		$arrMethods = get_class_methods('Mail');
		$arrSetter  = preg_grep('#^set#', $arrMethods);

		foreach ($arrSetter as $strSetter) {
			echo "  - Mail::" . $strSetter . "()\n";
			$this->assertEquals(
				$objConfig,
				$objConfig->$strSetter(null),
				'Setter MailerConfig::' . $strSetter . ' does not return $this!'
			);
		}
	}

	public function testCreateMail()
	{
		echo "Testing Mail generation\n";

		$objConfig = MailerConfig::getDefault();

		$objMail = new Mail();
		$objMail->addHeader('X-Mailer', 'I am a funny test mailer!');
		$objMail->setSender('maildaemon@example.com');
		$objMail->setSenderName('Mail Daemon');
		$objMail->setReplyTo('reply@example.com');
		$objMail->setReplyToName('Support example.com');
		$objMail->setPriority(Mail::PRIORITY_HIGHEST);
		$objMail->setSubject('I am a test mail');
		$objMail->setText('I am a test :-)');
		$objMail->setHtml('<html>
				<body>
					I am a test :-)<br>
					With an existing image: <img src="system/themes/default/images/visible.gif" width="17" height="16"><br>
					With a non existing image: <img src="this/images/does/not/exists.jpg" width="16" height="16"><br>
					With an external image: <img src="http://demo.contao.org/system/themes/default/images/visible.gif" width="17" height="16"><br>
					With a static image: <img src="http://static.example.com/system/themes/default/images/visible.gif" width="17" height="16"><br>
					With an email link: <a href="mailto:alex@example.com">alex@example.com</a><br>
					With a relative link: <a href="index.html">index.html</a><br>
				</body>
			</html>');

		$arrContent = $objMail->getContents($objConfig);

		$this->assertEquals(array(
			'text'                                        => 'I am a test :-)',
			'html'                                        => '<html>
				<body>
					I am a test :-)<br>
					With an existing image: <img src="[[embed::f94ddaacd5fdaf9c3b8b8e1b5e2b1431]]" width="17" height="16"><br>
					With a non existing image: <img src="http://static.example.com/this/images/does/not/exists.jpg" width="16" height="16"><br>
					With an external image: <img src="http://demo.contao.org/system/themes/default/images/visible.gif" width="17" height="16"><br>
					With a static image: <img src="[[embed::f94ddaacd5fdaf9c3b8b8e1b5e2b1431]]" width="17" height="16"><br>
					With an email link: <a href="mailto:alex@example.com">alex@example.com</a><br>
					With a relative link: <a href="http://www.example.com/index.html">index.html</a><br>
				</body>
			</html>',
			'[[embed::f94ddaacd5fdaf9c3b8b8e1b5e2b1431]]' => 'system/themes/default/images/visible.gif'
		), $arrContent);
	}

	public function testSendMail()
	{
		echo "Testing Mail transmission\n";

		$objConfig = new MailerConfig();
		if (get_cfg_var('unittest_use_smtp')) {
			$objConfig->setUseSMTP(true);

			if (get_cfg_var('unittest_smtp_host') !== false) {
				$objConfig->setSmtpHost(get_cfg_var('unittest_smtp_host'));
			}
			if (get_cfg_var('unittest_smtp_port') !== false) {
				$objConfig->setSmtpPort(get_cfg_var('unittest_smtp_port'));
			}
			if (get_cfg_var('unittest_smtp_user') !== false) {
				$objConfig->setSmtpUser(get_cfg_var('unittest_smtp_user'));
			}
			if (get_cfg_var('unittest_smtp_password') !== false) {
				$objConfig->setSmtpPassword(get_cfg_var('unittest_smtp_password'));
			}
			if (get_cfg_var('unittest_smtp_encryption') !== false) {
				$objConfig->setSmtpEncryption(get_cfg_var('unittest_smtp_encryption'));
			}
		}
		$objConfig->setEmbedImages(true);
		$objConfig->setEmbedImageSize(1000000); // 1 MB
		$objConfig->setImageHref('http://st2.contao.org');
		$objConfig->setBaseHref('http://demo.contao.org');

		$varTo = get_cfg_var('unittest_email');

		if (!$varTo) {
			$this->markTestSkipped('Define a recipient mail with "-demail=bob@example.com" to test mail sending!');
			return;
		}

		$objMail = new Mail();
		$objMail->addHeader('X-Mailer', 'I am a funny test mailer!');
		$objMail->setSender('maildaemon@example.com');
		$objMail->setSenderName('Mail Daemon');
		$objMail->setReplyTo('reply@example.com');
		$objMail->setReplyToName('Support example.com');
		$objMail->setPriority(Mail::PRIORITY_HIGHEST);
		$objMail->setSubject('I am a test mail');
		$objMail->setText('I am a test :-)');
		$objMail->setHtml('<html>
				<body>
					I am a test :-)<br>
					With an existing image: <img src="system/themes/default/images/visible.gif" width="17" height="16"><br>
					With a non existing image: <img src="this/images/does/not/exists.jpg" width="16" height="16"><br>
					With an external image: <img src="http://demo.contao.org/system/themes/default/images/visible.gif" width="17" height="16"><br>
					With a static image: <img src="http://st2.contao.org/system/themes/default/images/visible.gif" width="17" height="16"><br>
					With an email link: <a href="mailto:alex@example.com">alex@example.com</a><br>
					With a relative link: <a href="index.html">index.html</a><br>
				</body>
			</html>');

		$objMailer = Mailer::getMailer($objConfig);
		$this->assertTrue($objMailer->send($objMail, $varTo));
	}
}
