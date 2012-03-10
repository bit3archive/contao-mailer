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

MetaPalettes::appendBefore('tl_settings', 'smtp', array(
	'mailer' => array(':hide', 'mailer_embed_images')
));

$GLOBALS['TL_DCA']['tl_settings']['metasubpalettes']['mailer_embed_images'] = array('mailer_embed_images_size');

$GLOBALS['TL_DCA']['tl_settings']['fields']['mailer_embed_images'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailer_embed_images'],
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'      => 'w50 m12',
	                                   'submitOnChange'=> true)
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['mailer_embed_images_size'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailer_embed_images_size'],
	'inputType'               => 'select',
	'options_callback'        => array('tl_settings_mailer', 'getSizes'),
	'eval'                    => array('tl_class'=> 'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['mailer_implementation'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['mailer_implementation'],
	'inputType'               => 'select',
	'options'        => array_keys($GLOBALS['TL_MAILER']),
	'reference'               => &$GLOBALS['TL_LANG']['MAILER'],
	'eval'                    => array('tl_class'=> 'w50')
);


/**
 * Class tl_settings_mailer
 *
 * @copyright  InfinitySoft 2010,2011,2012
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    Mailer
 */
class tl_settings_mailer extends Backend
{
	/**
	 * @return array
	 */
	public function getSizes()
	{
		$options = array();
		foreach (array(
			         1000, 2500, 5000,
			         10000, 25000, 50000,
			         100000, 250000, 500000,
			         1000000, 2500000, 5000000,
			         10000000) as $size) {
			$options[$size] = $this->getReadableSize($size, 1);
		}
		return $options;
	}
}

?>