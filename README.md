Mailer Framework for Contao
---------------------------

The Mailer Framework is simple Framework for create and send emails,
but more configurable then the Contao Email class.
The first problem of the Contao Email class is, that is it not flexible.
You cannot send the first mail via SMTP and the seccond one directly without SMTP or vise versa.
Or sending the second one over another SMTP then the first one. These _complex_ scenarios are impossible,
because the Contao Email class internally use a static SwiftMailer instances and reuse it,
even if the SMTP settings change.

## TODOs

- complete code documentation (urgent priority!)

## Features

The Mailer is designed to use custom implementations.
Swift is used in the build-in default implementation.
It is fully integrated into the Contao framework
and is developed in an API-driven layout.

- support multiple SMTP configurations in one request
- support easy custom Swift configurations, using HOOKs
- support custom Mailer implementations
- support enable or disable image embedding
- support image embedding by size, bigger images are linked
- auto absolutize relative hrefs
- API-driven layout

## Why a new Mailer Framework, why not using Swift?

Swift is a good mailer system, but more an _implementation_ than a _framework_.
But Swift is not integrated in Contao and can not be so simple.
The Mailer Framework is a Contao integraded system,
that makes handling email in Contao easier than Swift,
supporting custom implementations.
Even the default implementation using Swift,
a custom implementation can extend the default Swift implementation or use somethink else.
For example a full custom implementation can use a web service instead of sending the email by itself.
We know Swift supports custom transport implementation as well,
but not every web service may support emails, generated by Swift ;-)

## HOOKs

### swiftMailerCreateTransport

Is called before the Swift_Transport object is created.
Can be used to create a custom transport.

```php
$GLOBALS['TL_HOOKS']['swiftMailerCreateTransport'][] = array('MyClass', 'hookSwiftMailerCreateTransport');

class MyClass
{
	/**
	 * @param MailerConfig $objConfig
	 * @return Swift_Transport|false
	 */
	public function hookSwiftMailerCreateTransport(MailerConfig $objConfig)
	{
		// do somethink with $objConfig
		if (...) {
			// create a transport object
			return $objTransport;
		}
		return false;
	}
}
```

### swiftMailerConfigureTransport

Is called after the Swift_Transport object is created.

```php
$GLOBALS['TL_HOOKS']['swiftMailerConfigureTransport'][] = array('MyClass', 'hookSwiftMailerConfigureTransport');

class MyClass
{
	/**
	 * @param MailerConfig $objConfig
	 * @param Swift_Transport $objTransport
	 * @return void
	 */
	public function hookSwiftMailerConfigureTransport(MailerConfig $objConfig, Swift_Transport $objTransport)
	{
		// do somethink with $objTransport
	}
}
```

### swiftMailerCreateMailer

Is called before the Swift_Mailer object is created.
Can be used to create a custom mailer.

```php
$GLOBALS['TL_HOOKS']['swiftMailerCreateMailer'][] = array('MyClass', 'hookSwiftMailerCreateMailer');

class MyClass
{
	/**
	 * @param MailerConfig $objConfig
	 * @return Swift_Mailer|false
	 */
	public function hookSwiftMailerCreateMailer(MailerConfig $objConfig, Swift_Transport $objTransport)
	{
		// do somethink with $objConfig and $objTransport
		if (...) {
			// create a mailer object
			return $objMailer;
		}
		return false;
	}
}
```

### swiftMailerConfigureMailer

Is called after the Swift_Mailer object is created.

```php
$GLOBALS['TL_HOOKS']['swiftMailerConfigureMailer'][] = array('MyClass', 'hookSwiftMailerConfigureMailer');

class MyClass
{
	/**
	 * @param MailerConfig $objConfig
	 * @param Swift_Mailer $objMailer
	 * @return void
	 */
	public function hookSwiftMailerConfigureMailer(MailerConfig $objConfig, Swift_Mailer $objMailer)
	{
		// do somethink with $objMailer
	}
}
```

### swiftMailerCreateMessage

Is called before the Swift_Message object is created.
Can be used to create a custom message.

```php
$GLOBALS['TL_HOOKS']['swiftMailerCreateMessage'][] = array('MyClass', 'hookSwiftMailerCreateMessage');

class MyClass
{
	/**
	 * @param MailerConfig $objConfig
	 * @return Swift_Message|false
	 */
	public function hookSwiftMailerCreateMessage(MailerConfig $objConfig)
	{
		if (...) {
			$objMessage = Swift_Message::newInstance();
			// do somethink with $objMessage
			return $objMessage;
		}
		return false;
	}
}
```

### swiftMailerCreateAttachment

Is called if an attachment is added, that is not supported by the mailer framework.
Can be used to make custom attachments.

```php
$GLOBALS['TL_HOOKS']['swiftMailerCreateAttachment'][] = array('MyClass', 'hookSwiftMailerCreateAttachment');

class MyMailAttachment extends MailAttachment
{
	...
}

class MyClass
{
	/**
	 * @param MailerConfig $objConfig
	 * @param Mail $objEmail
	 * @param MailAttachment $objAttachment
	 * @return Swift_Attachment|false
	 */
	public function hookSwiftMailerCreateAttachment(MailerConfig $objConfig, Mail $objEmail, MailAttachment $objAttachment)
	{
		if ($objAttachment instanceof MyMailAttachment) {
			// do somethink with $objAttachment and create a Swift_Attachment object $objSwiftAttachment
			return $objSwiftAttachment;
		}
		return false;
	}
}
```

## Usage examples

### Simple usage scenario

```php
<?php
class MyClass
{
	public function myFunction()
	{
		// create the Mailer, using the default settings (depending on the Contao settings)
		$objMailer = Mailer::getMailer();

		// create the email
		$objEmail = new Mail()
			->setSubject('I am an email')
			->setText('I am the plain text')
			->setHtml('<html><body>I am the html text!</body></html>');

		// send the email
		$objMailer->send($objEmail, 'alex@example.com');
	}
}
```

### Complex usage scenario

```php
<?php
class MyClass
{
	public function myFunction()
	{
		// get default settings
		$objConfig = MailerConfig::getDefault();

		// set some config settings
		$objConfig->setEmbedImages(true);
		$objConfig->setEmbedImageSize(1000000); // 1 MB
		$objConfig->addHeader('X-Mailer', 'My Extension Mailer');

		// create the Mailer
		$objMailer = Mailer::getMailer($objConfig);

		// create the email
		$objEmail = new Mail()
			->setSubject('I am an email')
			->setPriority(Mail::PRIORITY_HIGHEST)
			->setReplyTo('noreply@example.com');

		$objEmail->setText($this->getPlainText());
		$objEmail->setHtml($this->getHtml());

		// send the email
		$objMailer->send(
			$objEmail,
			array('alex@example.com', 'bob@example.com'), // To
			array('charlie@example.com', 'dora@example.com'), // CC
			'eric@example.com' // BCC
		);
	}

	protected function getPlainText()
	{
		return 'I am the plain text';
	}

	protected function getHtml()
	{
		return '<html><body>I am the html text!</body></html>';
	}
}
```

### Using custom config

```php
<?php
class MyClass
{
	public function myFunction()
	{
		// get default settings
		$objConfig = new MailerConfig();

		// set some config settings
		$objConfig->setUseSMTP(true);
		$objConfig->setSmtpHost('mx.example.com');
		$objConfig->setSmtpEncryption(MailerConfig::ENCRYPTION_SSL);
		$objConfig->setSmtpUser('maildaemon');
		$objConfig->setSmtpPassword('password');

		// create the Mailer
		$objMailer = Mailer::getMailer($objConfig);

		// create the email
		$objEmail = new Mail()
			->setSubject('I am an email')
			->setText('I am the plain text')
			->setHtml('<html><body>I am the html text!</body></html>');

		// send the email
		$objMailer->send($objEmail, 'alex@example.com');
	}
}
```

## Unit Tests

If you try to run the unit tests, keep in mind that you have to add a contao into the include path,
for example like this: `php -dinclude_path=".:/usr/share/pear:/path/to/contao-2.11.1"`
