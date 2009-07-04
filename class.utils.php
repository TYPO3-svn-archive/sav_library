<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Yolf (Laurent Foulloy) <yolf.typo3@orange.fr>
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
 * SAV Library: utils
 *
 * @author	Yolf <yolf.typo3@orange.fr>
 *
 */

final class utils {

  /**
   * Timer methods
   *
   */
   
	/**
   * Start a timer
	 *
	 * @param $extKey string (extension key)
	 * @param $index string (timer index)
	 *
	 * @return none
	 */
  public function startTimer($extKey, $index) {
    $timer = &$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['timer'];
    $timer[$index]['start'] = microtime(true);
    $timer[$index]['stop'] = 0;
  }

  /**
	 * Restart a timer
   *
	 * @param $extKey string (extension key)
	 * @param $index string (timer index)
	 *
	 * @return none
	 */
  public function restartTimer($extKey, $index) {
    $timer = &$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['timer'];
    if ($timer[$index]['stop']) {
      $timer[$index]['start'] = $timer[$index]['start'] +
        (microtime(true) - $timer[$index]['stop']);
      $timer[$index]['stop'] = 0;
    }
  }

	/**
	 * Stop a timer
	 *
	 * @param $extKey string (extension key)
	 * @param $index string (timer index)
	 *
	 * @return none
	 */
  public function stopTimer($extKey, $index) {
    $timer = &$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['timer'];
    $timer[$index]['stop'] = microtime(true);
  }
    
  /**
	 * Get a timer value
	 *
	 * @param $extKey string (extension key)
	 * @param $index string (timer index)
	 *
	 * @return float (timer value)
	 */
  public function getTimer($extKey, $index) {
    $timer = &$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['timer'];
      
    return (microtime(true) - $timer[$index]['start']);
  }
    
  /**
   * HTML methods
   *
   */
    
	/**
	 * Adds a HTML attribute
	 *
	 * @param $attributeName string
	 * @param $attributeValue string
	 *
	 * @return string
	 */
  public function htmlAddAttribute($attributeName, $attributeValue) {

    return $attributeName . '="' . $attributeValue . '"';
  }

	/**
	 * Adds a HTML attribute if not null
	 *
	 * @param $attributeName string
	 * @param $attributeValue string
	 *
	 * @return string 
	 */	      
  public function htmlAddAttributeIfNotNull($attributeName, $attributeValue) {

    return (
      $attributeValue ?
      $attributeName . '="' . $attributeValue . '"' :
      ''
    );
  } 

	/**
	 * Removes null items in the attributes array
	 *
	 * @param $attributes array
	 *
	 * @return array
	 */
  public function htmlCleanAttributesArray($attributes) {

    return array_diff($attributes, array(''));
  }

	/**
	 * Returns a HTML INPUT Text Element
	 *
	 * @param $attributes array
	 *
	 * @return string
	 */
  public function htmlInputTextElement($attributes) {

    return '<input type="text" '.
      implode(' ', utils::htmlCleanAttributesArray($attributes)).
      ' />';
  }

	/**
	 * Returns a HTML INPUT Password Element
	 *
	 * @param $attributes array
	 *
	 * @return string
	 */
  public function htmlInputPasswordElement($attributes) {

    return '<input type="password" '.
      implode(' ', utils::htmlCleanAttributesArray($attributes)).
      ' />';
  }

	/**
	 * Returns a HTML INPUT Hidden Element
	 *
	 * @param $attributes array
	 *
	 * @return string
	 */
  public function htmlInputHiddenElement($attributes) {

    return '<input type="hidden" '.
      implode(' ', utils::htmlCleanAttributesArray($attributes)).
      ' />';
  }

	/**
	 * Returns a HTML INPUT File Element
	 *
	 * @param $attributes array
	 *
	 * @return string
	 */
  public function htmlInputFileElement($attributes) {

    return '<input type="file" '.
      implode(' ', utils::htmlCleanAttributesArray($attributes)).
      ' />';
  }

	/**
	 * Returns a HTML INPUT Checkbox Element
	 *
	 * @param $attributes array
	 *
	 * @return string
	 */
   public function htmlInputCheckboxElement($attributes) {

    return '<input type="checkbox" '.
      implode(' ', utils::htmlCleanAttributesArray($attributes)).
      ' />';
  }

	/**
	 * Returns a HTML INPUT Radio Element
	 *
	 * @param $attributes array
	 *
	 * @return string
	 */
   public function htmlInputRadioElement($attributes) {

    return '<input type="radio" '.
      implode(' ', utils::htmlCleanAttributesArray($attributes)).
      ' />';
  }

	/**
	 * Returns a HTML INPUT Image Element
	 *
	 * @param $attributes array
	 *
	 * @return string
	 */
   public function htmlInputImageElement($attributes) {

    return '<input type="image" '.
      implode(' ', utils::htmlCleanAttributesArray($attributes)).
      ' />';
  }

	/**
	 * Returns a HTML INPUT Submit Element
	 *
	 * @param $attributes array
	 *
	 * @return string
	 */
   public function htmlInputSubmitElement($attributes) {

    return '<input type="submit" '.
      implode(' ', utils::htmlCleanAttributesArray($attributes)).
      ' />';
  }
  
	/**
	 * Returns a HTML BR Element
	 *
	 * @param $attributes array
	 *
	 * @return string
	 */
   public function htmlBrElement($attributes) {
	 
    $attributesString = implode(
      ' ',
      utils::htmlCleanAttributesArray($attributes)
    );
    return '<br' .
      ($attributesString ? ' ' . $attributesString : '') .
      ' />';
  }

	/**
	 * Returns a HTML SPAN Element
	 *
	 * @param $attributes array
	 * @param $content string
	 *
	 * @return string
	 */
   public function htmlSpanElement($attributes, $content) {

    $attributesString = implode(
      ' ',
      utils::htmlCleanAttributesArray($attributes)
    );
    return '<span' .
      ($attributesString ? ' ' . $attributesString : '') .
      '>' .
      $content .
      '</span>';
  }

	/**
	 * Returns a HTML OPTION Element
	 *
	 * @param $attributes array
	 * @param $content string
	 *
	 * @return string
	 */
   public function htmlOptionElement($attributes, $content) {

    $attributesString = implode(
      ' ',
      utils::htmlCleanAttributesArray($attributes)
    );
    return '<option' .
      ($attributesString ? ' ' . $attributesString : '') .
      '>' .
      $content .
      '</option>';
  }

	/**
	 * Returns a HTML SELECT Element
	 *
	 * @param $attributes array
	 * @param $content string
	 *
	 * @return string
	 */
   public function htmlSelectElement($attributes, $content) {

    $attributesString = implode(
      ' ',
      utils::htmlCleanAttributesArray($attributes)
    );
    return '<select' .
      ($attributesString ? ' ' . $attributesString : '') .
      '>' .
      $content .
      '</select>';
  }

	/**
	 * Returns a HTML IFRAME Element
	 *
	 * @param $attributes array
	 * @param $content string
	 *
	 * @return string
	 */
   public function htmlIframeElement($attributes, $content) {

    $attributesString = implode(
      ' ',
      utils::htmlCleanAttributesArray($attributes)
    );
    return '<iframe' .
      ($attributesString ? ' ' . $attributesString : '') .
      '>' .
      $content .
      '</iframe>';
  }

	/**
	 * Returns a HTML IMG Element
	 *
	 * @param $attributes array
	 *
	 * @return string
	 */
   public function htmlImgElement($attributes) {

    $attributesString = implode(
      ' ',
      utils::htmlCleanAttributesArray($attributes)
    );
    return '<img' .
      ($attributesString ? ' ' . $attributesString : '') .
      ' />';
  }

	/**
	 * Returns a HTML TEXTAREA Element
	 *
	 * @param $attributes array
	 * @param $content string
	 *
	 * @return string
	 */
   public function htmlTextareaElement($attributes, $content) {

    $attributesString = implode(
      ' ',
      utils::htmlCleanAttributesArray($attributes)
    );
    return '<textarea' .
      ($attributesString ? ' ' . $attributesString : '') .
      '>' .
      $content .
      '</textarea>';
  }

}


?>
