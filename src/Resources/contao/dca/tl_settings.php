<?php

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{lizenzverwaltung_legend:hide},lizenzverwaltung_mailsignatur,lizenzverwaltung_absender,lims_host,lims_link,lims_username,lims_password';

/**
 * fields
 */

// Absendername und E-Mail die bei Mails verwendet wird
$GLOBALS['TL_DCA']['tl_settings']['fields']['lizenzverwaltung_absender'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_settings']['lizenzverwaltung_absender'],
	'inputType'     => 'text',
	'eval'          => array('tl_class'=>'w50','preserveTags'=>true)
);

// Mailtemplate
$GLOBALS['TL_DCA']['tl_settings']['fields']['lizenzverwaltung_mailsignatur'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_settings']['lizenzverwaltung_mailsignatur'],
	'inputType'     => 'textarea',
	'eval'          => array('tl_class'=>'long', 'rte' => 'tinyMCE', 'cols' => 80,'rows' => 10)
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['lims_host'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_settings']['lims_host'],
	'inputType'     => 'text',
	'eval'          => array('tl_class'=>'w50 clr')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['lims_username'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_settings']['lims_username'],
	'inputType'     => 'text',
	'eval'          => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['lims_password'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_settings']['lims_password'],
	'inputType'     => 'text',
	'eval'          => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['lims_link'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_settings']['lims_link'],
	'inputType'     => 'text',
	'eval'          => array('tl_class'=>'w50')
);
