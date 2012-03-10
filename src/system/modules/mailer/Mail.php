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
 * Class Mail
 *
 * A basic email that is just an email.
 *
 * @copyright  InfinitySoft 2010,2011,2012
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    Mailer
 */
class Mail
{
	const PRIORITY_HIGHEST = 1;
	const PRIORITY_HIGH = 2;
	const PRIORITY_NORMAL = 3;
	const PRIORITY_LOW = 4;
	const PRIORITY_LOWEST = 5;

	/**
	 * List of email headers
	 * @var array
	 */
	protected $headers = array();

	/**
	 * Sender e-mail address
	 * @var string
	 */
	protected $sender = '';

	/**
	 * Sender name
	 * @var string
	 */
	protected $senderName = '';

	/**
	 * The reply to address.
	 */
	protected $replyTo = '';

	/**
	 * The reply to name.
	 */
	protected $replyToName = '';

	/**
	 * E-mail priority
	 * @var integer
	 */
	protected $priority = 0;

	/**
	 * E-mail subject
	 * @var string
	 */
	protected $subject = '';

	/**
	 * Text part of the e-mail
	 * @var string
	 */
	protected $text = '';

	/**
	 * HTML part of the e-mail
	 * @var string
	 */
	protected $html = '';

	/**
	 * Character set
	 * @var string
	 */
	protected $charset = 'UTF-8';

	/**
	 * @var array
	 */
	protected $attachments = array();

	/**
	 * @param array $headers
	 */
	public function setHeaders($headers)
	{
		$this->headers = $headers;
		return $this;
	}

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
	 * @param string $sender
	 */
	public function setSender($sender)
	{
		$this->sender = $sender;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSender()
	{
		return $this->sender;
	}

	/**
	 * @param string $senderName
	 */
	public function setSenderName($senderName)
	{
		$this->senderName = $senderName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSenderName()
	{
		return $this->senderName;
	}

	public function setReplyTo($replyTo)
	{
		$this->replyTo = $replyTo;
		return $this;
	}

	public function getReplyTo()
	{
		return $this->replyTo;
	}

	public function setReplyToName($replyToName)
	{
		$this->replyToName = $replyToName;
		return $this;
	}

	public function getReplyToName()
	{
		return $this->replyToName;
	}

	/**
	 * @param int $priority
	 */
	public function setPriority($priority)
	{
		switch ($priority) {
			case 1:
			case 'highest':
				$this->priority = 1;
				break;
			case 2:
			case 'high':
				$this->priority = 2;
				break;
			case 3:
			case 'normal':
				$this->priority = 3;
				break;
			case 4:
			case 'low':
				$this->priority = 4;
				break;
			case 5:
			case 'lowest':
				$this->priority = 5;
				break;
			default:
				$this->priority = 0;
				break;
		}
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param string $html
	 */
	public function setHtml($html)
	{
		$this->html = $html;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getHtml()
	{
		return $this->html;
	}

	/**
	 * Parse the content and return an array with all content parts (excluding attachments).
	 *
	 * @return array
	 */
	public function getContent(MailerConfig $objConfig)
	{
		$arrContent = array();

		if ($this->text) {
			$arrContent['text'] = $this->getText();
		}

		if ($this->html) {
			$arrContent['html'] = $this->getHtml();

			$arrMatches = array();
			preg_match_all(
				'/(background|src)="([^"]+\.(jpe?g|png|gif|bmp|tiff?|swf))"/Ui',
				$arrContent['html'],
				$arrMatches,
				PREG_SET_ORDER
			);
			$strImageHref = $objConfig->getImageHref();

			$arrSrcEmbeded = array();
			// Check for internal images
			foreach ($arrMatches as $url)
			{
				$strUrl = $url[2];
				$strPath = str_replace(
					array($strImageHref, Environment::getInstance()->base),
					'',
					$url[2]
				);

				// skip replaced urls
				if (isset($arrSrcEmbeded[$strUrl]))
				{
					continue;
				}

				// Embed the image if the URL is now relative
				if (!preg_match('@^https?://@', $strPath) &&
					file_exists(TL_ROOT . '/' . $strPath) &&
					filesize($strPath) <= $objConfig->getEmbedImageSize()) {
					$arrSrcEmbeded[$strPath] = '[[embed::' . md5($url[2]) . ']]';

					$arrContent['html'] = preg_replace(
						'#(background|src)=("' . preg_quote($url[2]) . '"|\'' . preg_quote($url[2]) . '\')#',
						'$1="' . $arrSrcEmbeded[$strPath] . '"',
						$arrContent['html']
					);
				}
				else {
					$arrSrcEmbeded[$strUrl] = false;
				}
			}

			foreach ($arrSrcEmbeded as $strPath => $strEmbedId) {
				if ($strEmbedId !== false) {
					$arrContent[$strEmbedId] = $strPath;
				}
			}
		}

		return $arrContent;
	}

	/**
	 * @param string $charset
	 */
	public function setCharset($charset)
	{
		$this->charset = $charset;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCharset()
	{
		return $this->charset;
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
}
