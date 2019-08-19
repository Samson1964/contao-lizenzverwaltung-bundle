<?php

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{trainerlizenzen_legend:hide},trainerlizenzen_absender,lims_host,lims_link,lims_username,lims_password';

/**
 * fields
 */

// Absendername und E-Mail die bei Mails verwendet wird
$GLOBALS['TL_DCA']['tl_settings']['fields']['trainerlizenzen_absender'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_settings']['trainerlizenzen_absender'],
	'inputType'     => 'text',
	'eval'          => array('tl_class'=>'w50','preserveTags'=>true)
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

?>
