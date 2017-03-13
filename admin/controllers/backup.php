<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined( '_JEXEC' ) or
die( 'Direct Access to this location is not allowed.' );

/**
 * @author		Ratmil 
 * http://www.ratmilwebsolutions.com
*/

require_once(JPATH_COMPONENT.'/controllers/ppd.php');
require_once(JPATH_COMPONENT.'/data/gentable.php');
require_once(JPATH_COMPONENT.'/html/vdatabind.html.php');
require_once(JPATH_COMPONENT.'/html/vdatabindmodel.html.php');
require_once(JPATH_COMPONENT.'/html/backup.html.php');

/************************************************************
Class to manage sectors
*************************************************************/
class BackupForm extends PPDForm
{
	/**
	Class constructor
	*/
	function __construct()
	{
		parent::__construct();
		$this->context = 'com_payperdownload';
	}
	
	function getHtmlObject()
	{
		return new BackupHtmlForm();
	}
	
	function getFileNameWithDate()
	{
		$date = getdate();
		foreach($date as $key => $d)
		{
			if($d < 10)
				$date[$key] = '0' . $d;
		}
		return $date['year'].$date['mon'].$date['mday'].$date['hours'].$date['minutes'].$date['seconds'];
	}
	
	function getTmpFile()
	{
		$config = JFactory::getConfig();
		return $config->get('config.tmp_path') . '/back' . $this->getFileNameWithDate() . "bk";
	}
	
	function compressFile($file)
	{
		jimport('joomla.filesystem.archive');
		$dest = $file . ".gz";
		JArchive::create($dest, $file, 'gz');
		return $dest;
	}
	
	function backup()
	{
		require_once(JPATH_ADMINISTRATOR . "/components/com_payperdownload/export.php");
		
		ob_end_clean();
		header("Cache-Control: public, must-revalidate");
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		header("Pragma: no-cache");
		header("Expires: 0"); 
		header("Content-Description: File Transfer");
		header("Expires: Sat, 30 Dec 1990 07:07:07 GMT");
		header("Content-Type: application/octet-stream ");
		header("Accept-Ranges: bytes"); 
		header('Content-Disposition: attachment; filename="backup' . $this->getFileNameWithDate() . '.bk"');
		header("Content-Transfer-Encoding: binary\n");
		
		$exporter = new XML_Exporter();
		$exporter->to_file = false;
		$exporter->write_root_open("payperdownload");
		$exporter->export_table("payperdownloadplus_licenses", "license_id");
		$exporter->export_table("payperdownloadplus_users_licenses", "user_license_id");
		$exporter->export_table("payperdownloadplus_resource_licenses", "resource_license_id");
		$exporter->export_table("payperdownloadplus_config", "config_id");
		$exporter->export_table("payperdownloadplus_payments", "payment_id");
		$exporter->export_table("payperdownloadplus_download_links", "download_id");
		$exporter->export_table("payperdownloadplus_affiliates_programs", "affiliate_program_id");
		$exporter->export_table("payperdownloadplus_affiliates_users", "affiliate_user_id");
		$exporter->export_table("payperdownloadplus_affiliates_banners", "affiliate_banner_id");
		$exporter->export_table("payperdownloadplus_affiliates_users_refered", "referer_user");
		$exporter->write_root_close();
		exit;
	}
	
	function cleanDB()
	{	
		$db = JFactory::getDBO();
		$tables = array(
				'#__payperdownloadplus_affiliates_programs',
				'#__payperdownloadplus_affiliates_users',
				'#__payperdownloadplus_affiliates_banners',
				'#__payperdownloadplus_affiliates_users_refered',
				'#__payperdownloadplus_payments', 
				'#__payperdownloadplus_resource_licenses',
				'#__payperdownloadplus_users_licenses',
				'#__payperdownloadplus_licenses',
				'#__payperdownloadplus_download_links',
				'#__payperdownloadplus_config',
				);
		foreach($tables as $table)
		{
			$db->setQuery("DELETE FROM $table");
			$db->query();
		}
	}
	
	function decompressFiles($gzFile, &$decompressedFiles)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.archive');
		jimport('joomla.filesystem.folder');
		$config = JFactory::getConfig();
		$extractDir = $config->get('config.tmp_path') . '/extract';
		if(JFolder::exists($extractDir))
			JFolder::delete($extractDir);
		$result = JArchive::extract($gzFile, $extractDir);
		if($result === false)
			return false;
		$files = JFolder::files($extractDir, '.', true, true);
		$decompressedFiles = array();
		foreach($files as $file)
		{
			$ext = JFile::getExt(strtolower($file));
			if($ext == 'bk')
			{
				
				$decompressedFiles[] = $file;
			}
		}
		return true;
	}

	function getFileNameExtension($filename)
	{
		$pos = strrpos($filename, '.');
		if($pos === false)
			return '';
		else
			return substr($filename, $pos + 1);
	}
	
	function restore()
	{
		$importfile = JRequest::getVar('importxml', null, 'files', 'array' );
		if ( $importfile['error'] || $importfile['size'] < 1 )
		{
			$success = false;
			$msg = JText::_("PAYPERDOWNLOADPLUS_IMPORT_INVALID_FILE");
		}
		else
		{
			jimport('joomla.filesystem.file');
			$config = JFactory::getConfig();
			$tmp_dest 	= $config->get('tmp_path')."/".$importfile['name'];
			$tmp_src	= $importfile['tmp_name'];
			$ext = strtoupper($this->getFileNameExtension($tmp_dest));
			$uploaded = ($ext == 'BK') && JFile::upload($tmp_src, $tmp_dest);
			if($uploaded)
			{
				require_once(JPATH_ADMINISTRATOR . "/components/com_payperdownload/import.php");
				$importer = new XML_Importer();
				$importer->headerName = "PAYPERDOWNLOAD";
				$importer->onValidFunction = array($this, "cleanDB");
				$tableheader = "payperdownloadplus_";
				if(JRequest::getVar("oldversion"))
					$tableheader = "payperdownloadplus_";
				if($importer->importFromXml($tmp_dest, $tableheader) && $importer->validXML)
				{
					$success = true;
					$msg = JText::_("PAYPERDOWNLOADPLUS_IMPORT_SUCCESSFUL");
				}
				else
				{
					$success = false;
					$msg = JText::_("PAYPERDOWNLOADPLUS_IMPORT_ERROR");
				}
			}
			else
			{
				$success = false;
				$msg = JText::_("PAYPERDOWNLOADPLUS_IMPORT_ERROR_UPLOADING");
			}
		}
		$mainframe = JFactory::getApplication();
		$option = JRequest::getVar( 'option', '' );
		if($success)
			$mainframe->redirect("index.php?option=$option&adminpage=backup", $msg);
		else
			$mainframe->redirect("index.php?option=$option&adminpage=backup", $msg, "error");
	}
	
	function doTask($task, $option)
	{
		switch($task)
		{
			case "backup" :
				$this->backup();
				break;
			case "restore" :
				$this->restore();
				break;
			default:
				$this->createToolbar($task, $option);
				$this->htmlObject->renderBackup();
				break;
		}
	}
	
	function createToolbar($task, $option)
	{
		JHTML::_('stylesheet', 'administrator/components/'. $option . '/css/backend.css');
		JToolBarHelper::title( JText::_( 'PAYPERDOWNLOADPLUS_BACKUP_TITLE' ), 'backup.png' );
	}
}
?>
