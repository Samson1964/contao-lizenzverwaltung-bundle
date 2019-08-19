<?php

namespace Schachbulle\ContaoLizenzverwaltungBundle\Classes;

if (!defined('TL_ROOT')) die('You cannot access this file directly!');


/**
 * Class dsb_trainerlizenzImport
  */
class TrainerlizenzImport extends \Backend
{

	/**
	 * Return a form to choose a CSV file and import it
	 * @param object
	 * @return string
	 */
        
	public function importTrainer(\DataContainer $dc)
	{
		if (\Input::get('key') != 'import')
		{
			return '';
		}

		$this->import('BackendUser', 'User');
		$class = $this->User->uploader;

		// See #4086
		if (!class_exists($class))
		{
			$class = 'FileUpload';
		}

		$objUploader = new $class();

		// Import CSS
		if (\Input::post('FORM_SUBMIT') == 'tl_table_import')
		{
			$arrUploaded = $objUploader->uploadTo('system/tmp');

			if (empty($arrUploaded))
			{
				\Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields']);
				$this->reload();
			}

			$this->import('Database');

			foreach ($arrUploaded as $strCsvFile)
			{
				$objFile = new \File($strCsvFile, true);

				if ($objFile->extension != 'csv')
				{
					\Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension));
					continue;
				}

				// Get separator
				switch (\Input::post('separator'))
				{
					case 'semicolon':
						$strSeparator = ';';
						break;

					case 'tabulator':
						$strSeparator = "\t";
						break;

					default:
						$strSeparator = ',';
						break;
				}

				$resFile = $objFile->handle;
                                $value_array = array();
                                
				while(($arrRow = @fgetcsv($resFile, null, $strSeparator)) !== false)
				{
                                        $escape_arrRow = array();
                                        foreach($arrRow as $wert)
                                        {
                                            $wert = addslashes($wert);
                                            $escape_arrRow[] = $wert;
                                        }
                                        unset($wert);
                                        
                                        list($name, $vorname, $geburtstag, $strasse, $plz, $ort, $email, $verband, $lizenznummer, $lizenz, $erwerb, $verlaengerung1, $verlaengerung2, $verlaengerung3, $verlaengerung4, $gueltigkeit, $published, $letzteAenderung) = $escape_arrRow;
                                        $value_array[] = "('{$name}', '{$vorname}', '{$geburtstag}', '{$strasse}', '{$plz}', '{$ort}', '{$email}', '{$verband}', '{$lizenznummer}', '{$lizenz}', '{$erwerb}', '{$verlaengerung1}', '{$verlaengerung2}', '{$verlaengerung3}', '{$verlaengerung4}', '{$gueltigkeit}', '{$published}', '{$letzteAenderung}')";                                        
				}
                                
                                $values = join(',', $value_array);
                                $this->Database->prepare("INSERT INTO " . $dc->table . " (name, vorname, geburtstag, strasse, plz, ort, email, verband, lizenznummer, lizenz, erwerb, verlaengerung1, verlaengerung2, verlaengerung3, verlaengerung4, gueltigkeit, published, letzteAenderung)"
                                        . " VALUES " . $values)
						   ->execute(\Input::get('id'));
			}
                                                
//			$objVersions = new \Versions($dc->table, \Input::get('id'));
//			$objVersions->create();
                        
                        
                        
//                        $this->Database->prepare("UPDATE " . $dc->table . " SET tableitems=?")
//						   ->execute(serialize($arrTable), \Input::get('id'));

			\System::setCookie('BE_PAGE_OFFSET', 0, 0);
			$this->redirect(str_replace('&key=table', '', \Environment::get('request')));
		}

		// Return form
		return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=table', '', \Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['MSC']['tw_import'][1].'</h2>
'.\Message::generate().'
<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_table_import" class="tl_form" method="post" enctype="multipart/form-data">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_table_import">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

<div class="tl_tbox">
  <h3><label for="separator">'.$GLOBALS['TL_LANG']['MSC']['separator'][0].'</label></h3>
  <select name="separator" id="separator" class="tl_select" onfocus="Backend.getScrollOffset()">
    <option value="semicolon">'.$GLOBALS['TL_LANG']['MSC']['semicolon'].'</option>
    <option value="comma">'.$GLOBALS['TL_LANG']['MSC']['comma'].'</option>
    <option value="tabulator">'.$GLOBALS['TL_LANG']['MSC']['tabulator'].'</option>
  </select>'.(($GLOBALS['TL_LANG']['MSC']['separator'][1] != '') ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['separator'][1].'</p>' : '').'
  <h3>'.$GLOBALS['TL_LANG']['MSC']['source'][0].'</h3>'.$objUploader->generateMarkup().(isset($GLOBALS['TL_LANG']['MSC']['source'][1]) ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['source'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['tw_import'][0]).'">
</div>

</div>
</form>';
	}
}
?>