<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// no direct access
defined ( '_JEXEC' ) or die;

class ImageVisualDataBind extends VisualDataBind
{
	var $imagePath;
	var $showImage;
	var $minWidth;
	var $minHeight;
	var $maxWidth;
	var $maxHeight;
	var $savePreview;
	var $acceptVideos;

	function __construct($dataField, $displayName, $imagePath = 'images')
	{
		parent::__construct($dataField, $displayName);
		$this->imagePath = $imagePath;
		$this->showInGrid = false;
		$this->showImage = false;
		$this->minWidth = $this->minHeight = 0;
		$this->maxWidth = $this->maxHeight = 1000000;
		$this->useForTextSearch = false;
		$this->savePreview = false;
		$this->acceptVideos = false;
	}

	function setImageLimits($minWidth, $minHeight, $maxWidth, $maxHeight)
	{
		$this->minWidth = $minWidth;
		$this->minHeight = $minHeight;
		$this->maxWidth = $maxWidth;
		$this->maxHeight = $maxHeight;
	}

	function renderNew()
	{
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		$html .= "<input type=\"file\" name=\"$dataField" . "_file\" id=\"$dataField" . "_file\" />";
		$html .= "&nbsp;" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_MAX_SIZE")) . ":&nbsp;&nbsp;" . htmlspecialchars($this->maxWidth) . " X " . htmlspecialchars($this->maxHeight);
		$html .= "</td></tr>";
		return $html;
	}

	function renderEdit(&$row)
	{
	    $option = JFactory::getApplication()->input->get('option');
		if($this->acceptVideos)
		{
			$scriptPath = "administrator/components/$option/flowplayer/";
			JHTML::script( $scriptPath . 'flowplayer.min.js');
			JHTML::stylesheet( $scriptPath . 'style.css' );
		}
		$html = "<tr>" . $this->renderFieldLabel() . "<td>";
		$dataField = $this->dataField;
		if($this->allowBlank)
		{
			$html .= "<input type=\"checkbox\" name=\"$dataField" . "_isnull\"/>&nbsp;" .
				JText::_("PAYPERDOWNLOADPLUS_HTML_REMOVE_IMAGE")
				. "<br/>";
		}
		$html .= "<input type=\"file\" name=\"$dataField" . "_file\" id=\"$dataField" . "_file\" />";
		$html .= "<input type=\"hidden\" name=\"$dataField\" id=\"$dataField\" value=\"" . htmlspecialchars($row->{$this->dataField}) . "\" />";
		$html .= "&nbsp;" . htmlspecialchars(JText::_("PAYPERDOWNLOADPLUS_MAX_SIZE")) . ":&nbsp;&nbsp;" . htmlspecialchars($this->maxWidth) . " X " . htmlspecialchars($this->maxHeight);
		$url = JURI::root();
		if($this->showImage && $row->{$this->dataField} != null)
		{
			if(!$this->acceptVideos)
			{
				$imageSrc = str_replace("\\", "/", $row->{$this->dataField});
				$html .= "<br/><img src=\"" . htmlspecialchars($url . $imageSrc) . "\" />";
			}
			else
			{
				$imageSrc = str_replace("\\", "/", $row->{$this->dataField});
				$html .= "<a
						 href=\"" .  htmlspecialchars($url . $imageSrc) . "\"
						 style=\"display:block;width:520px;height:330px\"
						 id=\"player\">
					</a> ";
				$html .= "<script>
						flowplayer(\"player\", \"" . $url . "/administrator/components/$option/flowplayer/flowplayer.swf\");
						</script>";
			}
		}
		$html .= "</td></tr>";
		return $html;
	}

	function getFileName($name)
	{
		$file_name = $name;
		$name = str_replace(' ','_',$name);
		$ext_name = "";
		$dotpos = strrpos($name, ".");
		if($dotpos !== false)
		{
			$file_name = substr($name, 0, $dotpos);
			$ext_name = substr($name, $dotpos);
		}
		$tmp_dest = $this->imagePath . '/' . $file_name . "_" . time() . "_";
		for($i = 0; $i < 16; $i++)
			$tmp_dest .= chr(rand(97, 97 + 25));
		$tmp_dest .= $ext_name;
		return $tmp_dest;
	}

	function saveImagePreview($sourceFile)
	{
		if(!function_exists("imagecreatefromjpeg"))
			return false;
		$image_size = getimagesize($sourceFile);
		$format = $image_size[2];
		$srcWidth = $image_size[0];
		$srcHeight = $image_size[1];
		$dstWidth = 100;
		$dstHeight = 75;
		$srcImage = null;
		switch($format)
		{
		case 1:
			$srcImage = imagecreatefromgif($sourceFile);
			break;
		case 2:
			$srcImage = imagecreatefromjpeg($sourceFile);
			break;
		case 3:
			$srcImage = imagecreatefrompng($sourceFile);
			break;
		default:
			return false;
		}
		$dstImage = imagecreate($dstWidth, $dstHeight);
		if($dstImage)
		{
			imagecopyresized($dstImage, $srcImage, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
			$filePath = $sourceFile."_small.jpg";
			$quality = 70;
			imagejpeg($dstImage, $filePath, $quality);
			return true;
		}
		return false;
	}

	function onBeforeStore(&$row)
	{
		if($this->ignoreToBind)
			return true;
		$mainframe = JFactory::getApplication();

		$jinput = JFactory::getApplication()->input;

		$is_null = $jinput->get($this->dataField . "_isnull");
		if($this->allowBlank && $is_null == "on")
		{
			if($row->{$this->dataField})
			{
				//Eliminar archivo anterior
				//$previous_image_path = str_replace("/", DIRECTORY_SEPARATOR, $row->{$this->dataField});
				JFile::delete(JPATH_ROOT . '/' . $previous_image_path);
			}
			$row->{$this->dataField} = null;
			return true;
		}
		//$userfile = JRequest::getVar($this->dataField . "_file", null, 'files', 'array' );
		$userfile = $jinput->files->get($this->dataField . "_file");

		if ( !is_array($userfile) || $userfile['error'] || $userfile['size'] < 1 )
		{
			if($row->{$this->dataField} != null)
				return true;
			if($this->allowBlank)
			{
				$row->{$this->dataField} = null;
				return true;
			}
			$row->setError(JText::_("PAYPERDOWNLOADPLUS_HTML_INVALID_IMAGE"));
			return false;
		}
		$tmp_dest 	= $this->getFileName($userfile['name']);
		$tmp_src	= $userfile['tmp_name'];

		if(!$this->acceptVideos)
		{
			$image_size = getimagesize($tmp_src);
			//JPG, GIF, PNG only
			if($image_size == null || ($image_size[2] != 1 && $image_size[2] != 2 && $image_size[2] != 3))
			{
				$row->setError(JText::_("PAYPERDOWNLOADPLUS_HTML_INVALID_IMAGE"));
				return false;
			}

			if($image_size[0] < $this->minWidth || $image_size[0] > $this->maxWidth ||
				$image_size[1] < $this->minHeight || $image_size[1] > $this->maxHeight)
			{
				$row->setError(JText::_("PAYPERDOWNLOADPLUS_HTML_INVALID_IMAGE_SIZE"));
				return false;
			}
		}

		jimport('joomla.filesystem.file');


		// Move uploaded file
		if(JFile::upload($tmp_src, JPATH_ROOT . '/' . $tmp_dest))
		{
			if( !$this->acceptVideos && $this->savePreview )
				$this->saveImagePreview( JPATH_ROOT . '/' . $tmp_dest );
			if($row->{$this->dataField})
			{
				//Eliminar archivo anterior
				//$previous_image_path = str_replace("/", DIRECTORY_SEPARATOR, $row->{$this->dataField});
				JFile::delete(JPATH_ROOT . '/' . $previous_image_path);
			}
			$row->{$this->dataField} = str_replace("\\", "/", $tmp_dest);
			return true;
		}
		else
		{
			$row->setError(JText::_("PAYPERDOWNLOADPLUS_HTML_ERROR_SAVING_IMAGE"));
			return false;
		}
	}

	function onBeforeDelete($row, $id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT " . $row->getKeyName() . ", " . $this->dataField . " as image_path FROM " . $row->getTableName() . "
			WHERE " . $row->getKeyName() . " = '" . $db->escape($id) . "'");
		$object = $db->loadObject();
		if(isset($object) && $object != null)
			$this->image_path = $object->image_path;
		else
			$this->image_path = null;
		return true;
	}

	function onAfterDelete($row, $id)
	{

		jimport('joomla.filesystem.file');
		if($this->image_path)
			JFile::delete(JPATH_ROOT . '/' . $this->image_path);
		return true;
	}

	function check(&$row)
	{
		return true;
	}

	function renderValidateJavascript()
	{
		return "";
	}

	function renderGridCell(&$row, $rowNumber, $columnNumber, $columnCount)
	{
		if(!$this->acceptVideos && $this->savePreview)
		{
			$url = JURI::root();
			$imageSrc = str_replace("\\", "/", $url . $row->{$this->dataField});
			echo "<img src=\"" . $imageSrc . "_small.jpg\" />" ;
		}
	}
}

?>