<?php

########################################################################
# Extension Manager/Repository config file for ext: "sav_library"
#
# Auto generated 11-11-2008 19:01
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'SAV Library Extension Generator',
	'description' => 'The SAV Library Extension Generator makes it possible to directly build extensions without any PHP coding, thanks to simple configuration parameters using the Kickstarter as an extension editor. Multiple views of the data including forms can be generated.',
	'category' => 'misc',
	'author' => 'Yolf',
	'author_email' => 'yolf.typo3@orange.fr',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '2.0.6',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:140:{s:9:"ChangeLog";s:4:"338d";s:10:"README.txt";s:4:"ee2d";s:23:"class.tx_savlibrary.php";s:4:"e68a";s:42:"class.tx_savlibrary_defaultItemviewers.php";s:4:"1d49";s:39:"class.tx_savlibrary_defaultQueriers.php";s:4:"4b10";s:40:"class.tx_savlibrary_defaultVerifiers.php";s:4:"1bd6";s:38:"class.tx_savlibrary_defaultViewers.php";s:4:"a4c4";s:30:"class.tx_savlibrary_filter.php";s:4:"8751";s:21:"ext_conf_template.txt";s:4:"a243";s:12:"ext_icon.gif";s:4:"41df";s:17:"ext_localconf.php";s:4:"9092";s:14:"ext_tables.php";s:4:"e639";s:14:"ext_tables.sql";s:4:"1a9a";s:28:"ext_typoscript_constants.txt";s:4:"18a9";s:24:"ext_typoscript_setup.txt";s:4:"1ca3";s:43:"icon_tx_savlibrary_export_configuration.gif";s:4:"475a";s:13:"locallang.xml";s:4:"cd46";s:24:"locallang_csh_server.xml";s:4:"0f70";s:16:"locallang_db.xml";s:4:"7896";s:7:"tca.php";s:4:"c131";s:31:"dateselectlib/calendar-setup.js";s:4:"1b62";s:40:"dateselectlib/calendar-setup_stripped.js";s:4:"9a98";s:25:"dateselectlib/calendar.js";s:4:"d640";s:34:"dateselectlib/calendar_stripped.js";s:4:"bd39";s:38:"dateselectlib/class.DHTML_Calendar.php";s:4:"9b3f";s:43:"dateselectlib/class.tx_savdateselectlib.php";s:4:"c41c";s:28:"dateselectlib/ext_emconf.php";s:4:"b0da";s:26:"dateselectlib/ext_icon.gif";s:4:"f4f5";s:35:"dateselectlib/css/calendar-blue.css";s:4:"99e9";s:36:"dateselectlib/css/calendar-blue2.css";s:4:"5191";s:36:"dateselectlib/css/calendar-brown.css";s:4:"56a8";s:36:"dateselectlib/css/calendar-green.css";s:4:"6cf6";s:37:"dateselectlib/css/calendar-system.css";s:4:"1317";s:34:"dateselectlib/css/calendar-tas.css";s:4:"4d62";s:38:"dateselectlib/css/calendar-win2k-1.css";s:4:"5319";s:38:"dateselectlib/css/calendar-win2k-2.css";s:4:"67f1";s:43:"dateselectlib/css/calendar-win2k-cold-1.css";s:4:"ee89";s:43:"dateselectlib/css/calendar-win2k-cold-2.css";s:4:"e3d9";s:31:"dateselectlib/css/menuarrow.gif";s:4:"b5a9";s:31:"dateselectlib/doc/reference.pdf";s:4:"8abd";s:33:"dateselectlib/doc/wizard_form.dat";s:4:"b159";s:34:"dateselectlib/doc/wizard_form.html";s:4:"b5d3";s:40:"dateselectlib/doc/html/reference-Z-S.css";s:4:"d41d";s:36:"dateselectlib/doc/html/reference.css";s:4:"ab36";s:37:"dateselectlib/doc/html/reference.html";s:4:"0c20";s:33:"dateselectlib/lang/calendar-af.js";s:4:"65fc";s:33:"dateselectlib/lang/calendar-br.js";s:4:"bd1e";s:33:"dateselectlib/lang/calendar-ca.js";s:4:"00e8";s:37:"dateselectlib/lang/calendar-cs-win.js";s:4:"3556";s:33:"dateselectlib/lang/calendar-da.js";s:4:"ed4a";s:33:"dateselectlib/lang/calendar-de.js";s:4:"6b40";s:33:"dateselectlib/lang/calendar-du.js";s:4:"82ab";s:33:"dateselectlib/lang/calendar-el.js";s:4:"ef49";s:33:"dateselectlib/lang/calendar-en.js";s:4:"7ff3";s:33:"dateselectlib/lang/calendar-es.js";s:4:"d329";s:33:"dateselectlib/lang/calendar-fi.js";s:4:"8d01";s:33:"dateselectlib/lang/calendar-fr.js";s:4:"2e91";s:38:"dateselectlib/lang/calendar-hr-utf8.js";s:4:"05f1";s:33:"dateselectlib/lang/calendar-hr.js";s:4:"48e3";s:33:"dateselectlib/lang/calendar-hu.js";s:4:"1040";s:33:"dateselectlib/lang/calendar-it.js";s:4:"a947";s:33:"dateselectlib/lang/calendar-jp.js";s:4:"b47d";s:38:"dateselectlib/lang/calendar-ko-utf8.js";s:4:"a986";s:33:"dateselectlib/lang/calendar-ko.js";s:4:"22bc";s:38:"dateselectlib/lang/calendar-lt-utf8.js";s:4:"c11f";s:33:"dateselectlib/lang/calendar-lt.js";s:4:"06b3";s:33:"dateselectlib/lang/calendar-nl.js";s:4:"fd80";s:33:"dateselectlib/lang/calendar-no.js";s:4:"4ac0";s:38:"dateselectlib/lang/calendar-pl-utf8.js";s:4:"4df2";s:33:"dateselectlib/lang/calendar-pl.js";s:4:"07b7";s:33:"dateselectlib/lang/calendar-pt.js";s:4:"177f";s:33:"dateselectlib/lang/calendar-ro.js";s:4:"b2e4";s:33:"dateselectlib/lang/calendar-ru.js";s:4:"b38f";s:33:"dateselectlib/lang/calendar-si.js";s:4:"3083";s:33:"dateselectlib/lang/calendar-sk.js";s:4:"782c";s:33:"dateselectlib/lang/calendar-sp.js";s:4:"b8fd";s:33:"dateselectlib/lang/calendar-sv.js";s:4:"76d3";s:33:"dateselectlib/lang/calendar-tr.js";s:4:"52df";s:33:"dateselectlib/lang/calendar-zh.js";s:4:"c443";s:14:"doc/manual.sxw";s:4:"2e3f";s:19:"doc/wizard_form.dat";s:4:"04ce";s:20:"doc/wizard_form.html";s:4:"13f9";s:56:"kickstarter/class.tx_kickstarter_section_formqueries.php";s:4:"328f";s:50:"kickstarter/class.tx_kickstarter_section_forms.php";s:4:"7196";s:54:"kickstarter/class.tx_kickstarter_section_formviews.php";s:4:"04b6";s:51:"kickstarter/class.tx_kickstarter_section_savext.php";s:4:"0a96";s:54:"kickstarter/class.ux_tx_kickstarter_section_fields.php";s:4:"c1b5";s:50:"kickstarter/class.ux_tx_kickstarter_section_pi.php";s:4:"a6a1";s:54:"kickstarter/class.ux_tx_kickstarter_section_tables.php";s:4:"8247";s:46:"kickstarter/class.ux_tx_kickstarter_wizard.php";s:4:"5e6e";s:24:"kickstarter/ext_icon.gif";s:4:"41df";s:28:"kickstarter/taMenuBorder.gif";s:4:"a22e";s:26:"kickstarter/taMenuLeft.gif";s:4:"749f";s:27:"kickstarter/taMenuRight.gif";s:4:"9802";s:23:"res/flexform_ds_pi1.xml";s:4:"7c8f";s:30:"res/locallang_csh_flexform.xml";s:4:"0d78";s:33:"res/locallang_csh_kickstarter.xml";s:4:"eda0";s:18:"res/sav_library.js";s:4:"c2f4";s:20:"res/sav_library.tmpl";s:4:"e83c";s:19:"res/sav_library.xml";s:4:"f8ca";s:23:"res/fileicons/Thumbs.db";s:4:"766b";s:21:"res/fileicons/ppt.gif";s:4:"81bd";s:19:"res/icons/Thumbs.db";s:4:"ec5f";s:22:"res/icons/backward.png";s:4:"e567";s:27:"res/icons/backwardFirst.png";s:4:"1790";s:22:"res/icons/closedok.gif";s:4:"1443";s:41:"res/icons/delete_export_configuration.gif";s:4:"6de8";s:23:"res/icons/deletedok.gif";s:4:"6de8";s:18:"res/icons/down.gif";s:4:"c27e";s:18:"res/icons/edit.gif";s:4:"651a";s:20:"res/icons/export.gif";s:4:"254c";s:22:"res/icons/exportok.gif";s:4:"9cb5";s:21:"res/icons/forward.png";s:4:"46f7";s:25:"res/icons/forwardLast.png";s:4:"4e92";s:25:"res/icons/generatertf.gif";s:4:"9389";s:24:"res/icons/helpbubble.gif";s:4:"7e7e";s:23:"res/icons/leftarrow.gif";s:4:"b5a7";s:39:"res/icons/load_export_configuration.gif";s:4:"9f46";s:18:"res/icons/move.gif";s:4:"16fd";s:17:"res/icons/new.gif";s:4:"cdb3";s:21:"res/icons/newicon.gif";s:4:"6aba";s:21:"res/icons/newmail.gif";s:4:"ffa9";s:25:"res/icons/newmail_off.gif";s:4:"a2c1";s:19:"res/icons/print.gif";s:4:"c144";s:22:"res/icons/required.gif";s:4:"29f1";s:24:"res/icons/rightarrow.gif";s:4:"6f9a";s:39:"res/icons/save_export_configuration.gif";s:4:"1431";s:29:"res/icons/saveandclosedok.gif";s:4:"2c9f";s:21:"res/icons/savedok.gif";s:4:"933e";s:20:"res/icons/search.gif";s:4:"d07c";s:20:"res/icons/submit.gif";s:4:"4852";s:25:"res/icons/submitadmin.gif";s:4:"0604";s:20:"res/icons/toggle.gif";s:4:"664c";s:35:"res/icons/toggle_export_display.gif";s:4:"b1d6";s:16:"res/icons/up.gif";s:4:"4928";s:20:"res/images/Thumbs.db";s:4:"0a5e";s:27:"res/images/taMenuBorder.gif";s:4:"a22e";s:25:"res/images/taMenuLeft.gif";s:4:"749f";s:26:"res/images/taMenuRight.gif";s:4:"9802";s:22:"res/images/unknown.gif";s:4:"0d50";}',
);

?>
