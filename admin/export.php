<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
?>
<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

class XML_Exporter
{
	var $fp;
	var $root;
	var $table_prefix;
	var $to_file = true;
	
	function open($filePath)
	{
		if (!($this->fp = @fopen($filePath, "w")))
		{
			echo "error opening";
			return false;
		}
		else
			return true;
	}
	
	function writeTo($str)
	{
		if($this->to_file)
			@fwrite($this->fp, $str);
		else
			echo $str;
	}
	
	function close()
	{
		return @fclose($this->fp);
	}
	
	function write_root_open($root)
	{
		$this->writeTo("<$root>\r\n");
		$this->root = $root;
	}
	
	function write_root_close()
	{
		$root = $this->root;
		$this->writeTo("</$root>\r\n");
	}

	function export_table($table, $table_key, $optionals = "", $cyclic_reference = "")
	{
		$this->writeTo("<rows_$table $optionals >\r\n");
		$table_name = $this->table_prefix . $table;
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM #__" .  $table_name;
		$db->setQuery( $query );
		$total = $db->loadResult();
		$count = 0;
		$query = "SELECT * FROM #__" . $table_name;
		while($count <= $total)
		{
			$db->setQuery( $query, $count, 100);
			$rows = $db->loadAssocList();
			if($rows)
			{
				foreach($rows as $row)
				{
					$null_values = "";
					$this->writeTo("<".$table_name." ");
					foreach($row as $key => $value)
					{
						if(strcasecmp($key, $cyclic_reference) != 0 && $value != "" && $value != null)
							$this->writeTo(" $key = \"".$this->encodeXml($value)."\"");		
						else if($value == null)
							$null_values .= " __".$key."=\"\"";
					}
					$this->writeTo(" />\r\n ");
					if($null_values != "")
						$this->writeTo("<null_values table=\"$table\" key=\"$table_key\" key_value=\"" . 
							$this->encodeXml($row[$table_key]) . "\" $null_values />\r\n");
				}
			}
			$count += 100;
		}
		$this->writeTo("</rows_$table>\r\n");
		if($cyclic_reference != "")
		{
			$count = 0;
			$query = "SELECT $cyclic_reference, $table_key FROM #__" . $table_name;
			$this->writeTo("<cyclic_references>\r\n");
			while($count <= $total)
			{
				$db->setQuery( $query, $count, 100);
				$rows = $db->loadAssocList();
				foreach($rows as $row)
				{
					$key = $row[$table_key];
					$value = $row[$cyclic_reference];
					if($value != null && $value != "" && $value != 0)
					{
						$this->writeTo("<cyclic_reference table=\"$table_name\"");
						$this->writeTo(" key=\"".$this->encodeXml($table_key)."\"");
						$this->writeTo(" reference=\"".$this->encodeXml($cyclic_reference)."\"");
						$this->writeTo(" key_value=\"".$this->encodeXml($key)."\"");
						$this->writeTo(" reference_value=\"".$this->encodeXml($value)."\"");
						$this->writeTo(" />\r\n ");
					}
				}
				$count += 100;
			}
			$this->writeTo("</cyclic_references>\r\n");
		}
	}

	function encodeXml($val) 
	{
		$val = str_replace("\n", '<br/>', $val);
		$val = str_replace('&', '&amp;', $val);
		$val = str_replace("'", '&apos;', $val);
		$val = str_replace('"', '&quot;', $val);
		$val = str_replace('<', '&lt;', $val);
		$val = str_replace('>', '&gt;', $val);
		return $val;
	}
	
	function setTablePrefix($table_prefix)
	{
		$this->table_prefix = $table_prefix;
	}
}

?>