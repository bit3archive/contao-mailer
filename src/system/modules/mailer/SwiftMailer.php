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
class SwiftMailer extends Mailer
{
	/**
	 * @var Swift_Mailer
	 */
	protected $mailer;

	public function __construct(MailerConfig $config)
	{
		parent::__construct($config);

		$this->mailer = $this->createMailer();
	}

	/**
	 *
	 * @param mixed
	 *
	 * @return boolean
	 */
	public function send(Mail $objEmail, $varTo, $varCC = null, $varBCC = null)
	{
		$objMessage = $this->createMail();

		// add the headers, email headers are more important than config headers
		$this->setHeaders($objMessage, $objEmail);

		// set the recipients
		$this->setRecipients($objMessage, $objEmail, $varTo, $varCC, $varBCC);

		// set attachments
		$this->setAttachments($objMessage, $objEmail);

		// set content
		$this->setContent($objMessage, $objEmail);

		// TODO
	}

	protected function createMailer()
	{
		$objTransport = false;
		$objMailer = false;

		if (isset($GLOBALS['TL_HOOKS']['swiftMailerCreateTransport']) && is_array($GLOBALS['TL_HOOKS']['swiftMailerCreateTransport']))
		{
			foreach ($GLOBALS['TL_HOOKS']['swiftMailerCreateTransport'] as $callback)
			{
				$this->import($callback[0]);
				$objTransport = $this->$callback[0]->$callback[1]($this->config);
			}
		}

		if (!($objTransport instanceof Swift_Transport)) {
			/**
			 * Thanks to Leo Feyer <http://www.contao.org>
			 * @see Email class of the Contao Open Source CMS
			 */
			if ($this->config->getUseSMTP()) {
				// SMTP
				$objTransport = Swift_SmtpTransport::newInstance($this->config->getSmtpHost(), $this->config->getSmtpPort());

				// Encryption
				if ($this->config->getSmtpEncryption() == MailerConfig::ENCRYPTION_SSL ||
					$this->config->getSmtpEncryption() == MailerConfig::ENCRYPTION_TLS) {
					$objTransport->setEncryption($this->config->getSmtpEncryption());
				}

				// Authentication
				if ($this->config->getSmtpUser() != '') {
					$objTransport
						->setUsername($this->config->getSmtpUser())
						->setPassword($this->config->getSmtpPassword());
				}
			}
			else
			{
				// Mail
				$objTransport = Swift_MailTransport::newInstance();
			}
		}

		if (isset($GLOBALS['TL_HOOKS']['swiftMailerConfigureTransport']) && is_array($GLOBALS['TL_HOOKS']['swiftMailerConfigureTransport']))
		{
			foreach ($GLOBALS['TL_HOOKS']['swiftMailerConfigureTransport'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($this->config, $objTransport);
			}
		}

		if (isset($GLOBALS['TL_HOOKS']['swiftMailerCreateMailer']) && is_array($GLOBALS['TL_HOOKS']['swiftMailerCreateMailer']))
		{
			foreach ($GLOBALS['TL_HOOKS']['swiftMailerCreateMailer'] as $callback)
			{
				$this->import($callback[0]);
				$objMailer = $this->$callback[0]->$callback[1]($this->config, $objTransport);
			}
		}

		if (!($objMailer instanceof Swift_Mailer)) {
			$objMailer = Swift_Mailer::newInstance($objTransport);
		}

		if (isset($GLOBALS['TL_HOOKS']['swiftMailerConfigureMailer']) && is_array($GLOBALS['TL_HOOKS']['swiftMailerConfigureMailer']))
		{
			foreach ($GLOBALS['TL_HOOKS']['swiftMailerConfigureMailer'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($this->config, $objMailer);
			}
		}

		return $objMailer;
	}

	protected function createMail()
	{
		$objMessage = false;

		if (isset($GLOBALS['TL_HOOKS']['swiftMailerCreateMessage']) && is_array($GLOBALS['TL_HOOKS']['swiftMailerCreateMessage']))
		{
			foreach ($GLOBALS['TL_HOOKS']['swiftMailerCreateMessage'] as $callback)
			{
				$this->import($callback[0]);
				$objMessage = $this->$callback[0]->$callback[1]($this->config);
			}
		}

		if (!($objMessage instanceof Swift_Message)) {
			$objMessage = Swift_Message::newInstance();
		}

		return $objMessage;
	}

	protected function setHeaders(Swift_Message $objMessage, Mail $objEmail)
	{
		// set the headers
		$arrHeaders = array_merge(
			$this->config->getHeaders(),
			$objEmail->getHeaders()
		);
		foreach ($arrHeaders as $header=>$content) {
			$objMessage->getHeaders()->addTextHeader($header, $content);
		}

		// set the charset
		$objMessage->setCharset($objEmail->getCharset());

		// set the subject
		$objMessage->setSubject($objEmail->getSubject());

		// set sender
		$strSender = ($objEmail->getSender() ? $objEmail->getSender() : $this->config->getDefaultSender());
		$strSenderName = ($objEmail->getSenderName() ? $objEmail->getSenderName() : $this->config->getDefaultSenderName());
		$objMessage->setFrom($strSender, $strSenderName);

		// set reply to
		$strReplyTo = ($objEmail->getReplyTo() ? $objEmail->getReplyTo() : $this->config->getDefaultReplyTo());
		$strReplyToName = ($objEmail->getReplyToName() ? $objEmail->getReplyToName() : $this->config->getDefaultReplyToName());
		if ($strReplyTo) {
			$objMessage->setReplyTo($strReplyTo, $strReplyToName);
		}

		// set priority
		$objMessage->setPriority($objEmail->getPriority() ? $objEmail->getPriority() : $this->config->getDefaultPriority());
	}

	protected function setRecipients(Swift_Message $objMessage, Mail $objEmail, $varTo, $varCC, $varBCC)
	{
		$objMessage->setTo($this->compileRecipients($varTo));

		// set the copy recipients
		if ($varCC) {
			$objMessage->setCc($this->compileRecipients($varCC));
		}

		// set the blind copy recipients
		if ($varBCC) {
			$objMessage->setBcc($this->compileRecipients($varBCC));
		}
	}

	/**
	 * Compile e-mail addresses from an array of (different) arguments
	 *
	 * Thanks to Leo Feyer <http://www.contao.org>.
	 * @author     Leo Feyer <http://www.contao.org>
	 * @see    Email class of the Contao Open Source CMS
	 * @param array
	 * @return array
	 */
	protected function compileRecipients($arrRecipients)
	{
		$arrReturn = array();

		foreach ($arrRecipients as $varRecipients)
		{
			if (!is_array($varRecipients))
			{
				$varRecipients = $this->String->splitCsv($varRecipients);
			}

			// Support friendly name addresses and internationalized domain names
			foreach ($varRecipients as $v)
			{
				list($strName, $strEmail) = $this->splitFriendlyName($v);

				$strName = trim($strName, ' "');
				$strEmail = $this->idnaEncodeEmail($strEmail);

				if ($strName != '')
				{
					$arrReturn[$strEmail] = $strName;
				}
				else
				{
					$arrReturn[] = $strEmail;
				}
			}
		}

		return $arrReturn;
	}

	protected function setAttachments(Swift_Message $objMessage, Mail $objEmail)
	{
		$this->addAttachments($objMessage, $objEmail, $this->config->getAttachments());
		$this->addAttachments($objMessage, $objEmail, $objEmail->getAttachments());
	}

	protected function addAttachments(Swift_Message $objMessage, Mail $objEmail, array $arrAttachments)
	{
		foreach ($arrAttachments as $objAttachment) {
			$this->addAttachment($objMessage, $objEmail, $objAttachment);
		}
	}

	protected function addAttachment(Swift_Message $objMessage, Mail $objEmail, MailAttachment $objAttachment)
	{
		$objSwiftAttachment = false;

		if ($objAttachment instanceof MailFileAttachment) {
			/** @var MailFileAttachment $objAttachment */
			$objSwiftAttachment = Swift_Attachment::fromPath(
				$objAttachment->getFile(),
				$objAttachment->getMime()
			);
		}

		else if ($objAttachment instanceof MailDataAttachment) {
			/** @var MailDataAttachment $objAttachment */
			$objSwiftAttachment = Swift_Attachment::newInstance(
				$objAttachment->getData(),
				$objAttachment->getFilename(),
				$objAttachment->getMime()
			);
		}

		else {
			if (isset($GLOBALS['TL_HOOKS']['swiftMailerCreateAttachment']) && is_array($GLOBALS['TL_HOOKS']['swiftMailerCreateAttachment']))
			{
				foreach ($GLOBALS['TL_HOOKS']['swiftMailerCreateAttachment'] as $callback)
				{
					$this->import($callback[0]);
					$objSwiftAttachment = $this->$callback[0]->$callback[1]($this->config, $objEmail, $objAttachment);

					if ($objSwiftAttachment) {
						break;
					}
				}
			}
		}

		if (!($objSwiftAttachment instanceof Swift_Attachment)) {
			throw new Exception('Do not know how to handle attachment of type ' . get_class($objAttachment));
		}

		$objMessage->attach($objSwiftAttachment);
	}

	protected function setContent(Swift_Message $objMessage, Mail $objEmail)
	{
		$arrContent = $objEmail->getContent($this->config);

		$arrEmbedded = array();
		foreach ($arrContent as $strKey=>$strPath) {
			if ($strKey == 'html' || $strKey == 'text') {
				continue;
			}

			$arrEmbedded[$strKey] = $objMessage->embed(Swift_EmbeddedFile::fromPath(TL_ROOT . '/' . $strPath));
		}

		if (isset($arrContent['html'])) {
			foreach ($arrEmbedded as $strKey=>$strContentId) {
				$arrContent['html'] = str_replace($strKey, $strContentId, $arrContent['html']);
			}

			$objMessage->setBody($arrContent['html'], 'text/html');
		}

		if (isset($arrContent['text'])) {
			if (isset($arrContent['html'])) {
				$objMessage->addPart($arrContent['text'], 'text/plain');
			}
			else {
				$objMessage->setBody($arrContent['text'], 'text/plain');
			}
		}
	}
}
