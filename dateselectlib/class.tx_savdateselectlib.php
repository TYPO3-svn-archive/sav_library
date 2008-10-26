<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Your Name (your@email.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'sav_dateselectlib' for the 'sav_dateselectlib' extension.
 *
 * @author	Yolf <yolf.typo3@orange.fr>
 *
 * This plugin uses the same functions as in
 *
 * Plugin 'rlmp_dateselectlib' for the 'rlmp_dateselectlib' extension.
 *
 * @author	Robert Lemke <rl@robertlemke.de>
 */



require_once ('class.DHTML_Calendar.php');

class tx_savdateselectlib {
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
	}

	/**
	 * include the library and other data for page rendering
	 */
	function includeLib($css='calendar-win2k-2')	{

		// Check if another language than 'default' was selected
		$LLkey = 'en';
		if ($GLOBALS['TSFE']->config['config']['language'])	{
			$LLkey = $GLOBALS['TSFE']->config['config']['language'];
		}

		// parameters to constructor:
		//     1. the absolute URL path to the calendar files
		//     2. the languate used for the calendar (see the lang/ dir)
		//     3. the theme file used for the clanedar, without the ".css" extension
		//     4. boolean that specifies if the "_stripped" files are to be loaded
		//        The stripped files are smaller as they have no whitespace and comments
		$calendar = new DHTML_Calendar(t3lib_extMgm::siteRelPath('sav_library').'dateselectlib/', $LLkey, $css, false);

		// Add the calendar JavaScripts to page header
		if (!$GLOBALS['tx_savcalendar']['tx_savdateselectlib']) {
			$GLOBALS['TSFE']->additionalHeaderData['tx_savdateselectlib'] = chr(9).$calendar->get_load_files_code();			
		}
	}

	/**
	 * returns an input button which contains an onClick handler for opening the calendar
	 */
	
	function getInputButton ($id,$val,$conf) {

		// Default configuration
		$_conf['format'] = ($conf['eval']=='date'? '%d/%m/%Y' : '%d/%m/%Y %H:%M');
		$_conf['showsTime'] = 'true';
		$_conf['singleClick'] = 'false';
		$_conf['step'] = '1';
		$_conf['firstDay'] = '1';

		// Get configuration and overwrite
		if (is_array ($conf)) {
			foreach($_conf as $key => $value) {
				if ($conf[$key]) {
			 		$_conf[$key] = $conf[$key];
				}
			}
		}

    $htmlArray = array();
    
    $class = ($conf['errorDateValue'] ? ' class="error"' : '');
    
		$htmlArray[] = '<input type="text" name="'.$id.'"'.$class.' value="'.($conf['nodefault']&&!$val ? '' : ($conf['errorDateValue'] ? $conf['errorDateValue'] : strftime($_conf['format'],$val))).'" id="'.strtr($id,'[]','__').'_if" onchange="document.changed=1" />';
    $htmlArray[] = '<button type="reset" id="'.strtr($id,'[]','__').'_bt">...</button>';

		$htmlArray[] = '<script type="text/javascript">';
		$htmlArray[] = '  Calendar.setup({';
		$htmlArray[] = '    inputField     :    "'.strtr($id,'[]','__').'_if",';
		$htmlArray[] = '    ifFormat       :    "'.$_conf['format'].'",';
		$htmlArray[] = '    showsTime      :    '.$_conf['showsTime'].',';
		$htmlArray[] = '    button         :    "'.strtr($id,'[]','__').'_bt",';
		$htmlArray[] = '    singleClick    :    '.$_conf['singleClick'].',';
		$htmlArray[] = '    step           :    '.$_conf['step'].',';
		$htmlArray[] = '    firstDay       :    '.$_conf['firstDay'];
		$htmlArray[] = '    });';
		$htmlArray[] = '</script>';
    
		$out = implode(chr(10), $htmlArray);

		return $out;
	}


}



?>
