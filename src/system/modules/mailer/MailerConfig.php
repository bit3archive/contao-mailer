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
class MailerConfig extends System
{
	const ENCRYPTION_SSL = 'ssl';
	const ENCRYPTION_TLS = 'tls';

	/**
	 * Sender e-mail address
	 * @var string
	 */
	protected $defaultSender = '';

	/**
	 * Sender name
	 * @var string
	 */
	protected $defaultSenderName = '';

	protected $defaultReplyTo = '';

	protected $defaultReplyToName = '';

	protected $defaultPriority = 3;

	/**
	 * @var bool
	 */
	protected $embedImages = false;

	/**
	 * @var int
	 */
	protected $embedImageSize = 0;

	/**
	 * @var string
	 */
	protected $imageHref = '';

	/**
	 * @var string
	 */
	protected $baseHref = '';

	/**
	 * @var string
	 */
	protected $implementation = 'swift';

	/**
	 * @var bool
	 */
	protected $useSMTP = false;

	/**
	 * @var string
	 */
	protected $smtpHost = 'localhost';

	/**
	 * @var string
	 */
	protected $smtpUser = '';

	/**
	 * @var string
	 */
	protected $smtpPassword = '';

	/**
	 * @var string
	 */
	protected $smtpEncryption = '';

	/**
	 * @var int
	 */
	protected $smtpPort = 25;

	/**
	 * @var array
	 */
	protected $headers = array(
		'X-Mailer' => 'Contao Mailer by InfinitySoft <http://www.infinitysoft.de>'
	);

	protected $logFile = 'mailer.log';

	/**
	 * @var array
	 */
	protected $attachments = array();

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @static
	 * @return MailerConfig
	 */
	public static function getDefault()
	{
		$objConfig = new MailerConfig();
		list($strSenderName, $strSender) = $objConfig->splitFriendlyName(isset($GLOBALS['objPage'])
			? $GLOBALS['objPage']->adminEmail
			: $GLOBALS['TL_CONFIG']['adminEmail']);
		$strImageHref = isset($GLOBALS['objPage']) && $GLOBALS['objPage']->staticFiles
			? $GLOBALS['objPage']->staticFiles . TL_PATH
			: $objConfig->Environment->base;
		$strBaseHref = isset($GLOBALS['objPage']) && $GLOBALS['objPage']->dns
			? ($objConfig->Environment->ssl ? 'https://' : 'http://') . $GLOBALS['objPage']->dns . TL_PATH
			: $objConfig->Environment->base;
		$objConfig->setDefaultSender($strSender);
		$objConfig->setDefaultSenderName($strSenderName);
		$objConfig->setEmbedImages($GLOBALS['TL_CONFIG']['mailer_embed_images']);
		$objConfig->setEmbedImageSize($GLOBALS['TL_CONFIG']['mailer_embed_images_size']);
		$objConfig->setImageHref($strImageHref);
		$objConfig->setBaseHref($strBaseHref);
		$objConfig->setImplementation($GLOBALS['TL_CONFIG']['mailer_implementation']);
		$objConfig->setUseSMTP($GLOBALS['TL_CONFIG']['useSMTP']);
		$objConfig->setSmtpHost($GLOBALS['TL_CONFIG']['smtpHost']);
		$objConfig->setSmtpUser($GLOBALS['TL_CONFIG']['smtpUser']);
		$objConfig->setSmtpPassword($GLOBALS['TL_CONFIG']['smtpPassword']);
		$objConfig->setSmtpEncryption($GLOBALS['TL_CONFIG']['smtpEnc']);
		$objConfig->setSmtpPort($GLOBALS['TL_CONFIG']['smtpPort']);
		return $objConfig;
	}

	/**
	 * @param string $defaultSender
	 */
	public function setDefaultSender($defaultSender)
	{
		$this->defaultSender = $defaultSender;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultSender()
	{
		return $this->defaultSender;
	}

	/**
	 * @param string $defaultSenderName
	 */
	public function setDefaultSenderName($defaultSenderName)
	{
		$this->defaultSenderName = $defaultSenderName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultSenderName()
	{
		return $this->defaultSenderName;
	}

	public function setDefaultReplyTo($defaultReplyTo)
	{
		$this->defaultReplyTo = $defaultReplyTo;
		return $this;
	}

	public function getDefaultReplyTo()
	{
		return $this->defaultReplyTo;
	}

	public function setDefaultReplyToName($defaultReplyToName)
	{
		$this->defaultReplyToName = $defaultReplyToName;
		return $this;
	}

	public function getDefaultReplyToName()
	{
		return $this->defaultReplyToName;
	}

	public function setDefaultPriority($defaultPriority)
	{
		$this->defaultPriority = $defaultPriority;
		return $this;
	}

	public function getDefaultPriority()
	{
		return $this->defaultPriority;
	}

	/**
	 * @param boolean $embedImages
	 */
	public function setEmbedImages($embedImages)
	{
		$this->embedImages = $embedImages ? true : false;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEmbedImages()
	{
		return $this->embedImages;
	}

	/**
	 * @param int $embedImageSize
	 */
	public function setEmbedImageSize($embedImageSize)
	{
		$this->embedImageSize = intval($embedImageSize);
		return $this;
	}

	/**
	 * @return int
	 */
	public function getEmbedImageSize()
	{
		return $this->embedImageSize;
	}

	/**
	 * @param string $imageHref
	 */
	public function setImageHref($imageHref)
	{
		$imageHref = preg_replace('#/+$#', '', $imageHref);
		$imageHref .= '/';
		$this->imageHref = $imageHref;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getImageHref()
	{
		return $this->imageHref;
	}

	/**
	 * @param string $baseHref
	 */
	public function setBaseHref($baseHref)
	{
		$baseHref = preg_replace('#/+$#', '', $baseHref);
		$baseHref .= '/';
		$this->baseHref = $baseHref;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getBaseHref()
	{
		return $this->baseHref;
	}

	/**
	 * @param string $implementation
	 */
	public function setImplementation($implementation)
	{
		$this->implementation = $implementation;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getImplementation()
	{
		return $this->implementation;
	}

	/**
	 * @param boolean $useSMTP
	 */
	public function setUseSMTP($useSMTP)
	{
		$this->useSMTP = $useSMTP ? true : false;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getUseSMTP()
	{
		return $this->useSMTP;
	}

	/**
	 * @param string $smtpHost
	 */
	public function setSmtpHost($smtpHost)
	{
		$this->smtpHost = $smtpHost;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSmtpHost()
	{
		return $this->smtpHost;
	}

	/**
	 * @param string $smtpUser
	 */
	public function setSmtpUser($smtpUser)
	{
		$this->smtpUser = $smtpUser;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSmtpUser()
	{
		return $this->smtpUser;
	}

	/**
	 * @param string $smtpPassword
	 */
	public function setSmtpPassword($smtpPassword)
	{
		$this->smtpPassword = $smtpPassword;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSmtpPassword()
	{
		return $this->smtpPassword;
	}

	/**
	 * @param string $smtpEnc
	 */
	public function setSmtpEncryption($smtpEnc)
	{
		$this->smtpEncryption = $smtpEnc;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSmtpEncryption()
	{
		return $this->smtpEncryption;
	}

	/**
	 * @param int $smtpPort
	 */
	public function setSmtpPort($smtpPort)
	{
		$this->smtpPort = intval($smtpPort);
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSmtpPort()
	{
		return $this->smtpPort;
	}

	/**
	 * @param array $headers
	 */
	public function setHeaders($headers)
	{
		$this->headers = $headers;
		return $this;
	}

	/**
	 * @param string $header
	 * @param string $content
	 */
	public function addHeader($header, $content)
	{
		$this->headers[$header] = $content;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @param array $attachments
	 */
	public function setAttachments($attachments)
	{
		$this->attachments = $attachments;
		return $this;
	}

	public function addAttachment(MailAttachment $attachment)
	{
		$this->attachments[] = $attachment;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getAttachments()
	{
		return $this->attachments;
	}

	public function setLogFile($logFile)
	{
		$this->logFile = $logFile;
		return $this;
	}

	public function getLogFile()
	{
		return $this->logFile;
	}
}