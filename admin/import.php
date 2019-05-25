<?php
/**
 * @component Pay per Download component
 * @author Ratmil Torres
 * @copyright (C) Ratmil Torres
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

defined('_JEXEC') or die;

class XML_Importer
{
	var $table_prefix;
	var $errorCount = 0;
	var $errorMsg = "";
	var $hasDefaultData = false;
	var $dataFound = false;
	var $validXML = false;
	var $headerName = "";
	var $onValidFunction = null;

	function endElement($parser, $name)
	{
	}

	function startElement($parser, $name, $attrs)
	{
		$name = str_replace("PAYPERDOWNLOAD_", "PAYPERDOWNLOADPLUS_", $name);
		$db = JFactory::getDBO();
		if($name == strtoupper($this->headerName))
		{
			$this->validXML = true;
			if($this->onValidFunction)
				call_user_func($this->onValidFunction);
		}
		else
		if(substr($name, 0, strlen($this->table_prefix)) == $this->table_prefix)
		{
			if(count($attrs) > 0)
			{
				$tableName = "#__".strtolower($name);
				if($this->hasDefaultData)
				{
					if(!$this->dataFound)
					{
						$db->setQuery("DELETE FROM ".$tableName);
						$db->query();
						$this->dataFound = true;
					}
				}
				$fields = $db->getTableColumns($tableName);
				$sql = "INSERT INTO $tableName (";
				$values = "";
				if($fields)
				{
					foreach($attrs as $key => $value)
					{
						$key = strtolower($key);
						if(array_key_exists($key, $fields))
						{
							$sql .= $key . ", ";
							$values .= "'". $db->escape(str_replace("<br/>", "\n", $value)). "' ,";
						}
					}
				}
				$sql = substr($sql, 0, strlen($sql) - 2);
				$sql .= ")";
				$values = substr($values, 0, strlen($values) - 2);
				$sql .= " VALUES(" . $values. ")";
				$db->setQuery($sql);
				$result = $db->query();
				if(!$result)
				{
					$this->errorCount++;
					$this->errorMsg .= $db->stderr()."<br/>";
				}
			}
		}
		else if(substr($name, 0, 5) == "ROWS_")
		{
			if(array_key_exists("HAS_DEFAULT_DATA", $attrs) && strtoupper($attrs["HAS_DEFAULT_DATA"]) == "YES")
			{
				$this->hasDefaultData = true;
				$this->dataFound = false;
			}
			else
				$this->hasDefaultData = false;
		}
		else if($name == "CYCLIC_REFERENCE")
		{
			$table_name = "#__".strtolower($attrs["TABLE"]);
			$table_name = str_replace("payperdownload_", "payperdownloadplus_", $table_name);
			$key = $attrs["KEY"];
			$reference = $attrs["REFERENCE"];
			$key_value = $db->escape($attrs["KEY_VALUE"]);
			$reference_value = $db->escape($attrs["REFERENCE_VALUE"]);
			$query = "UPDATE $table_name SET $reference='$reference_value' WHERE $key = '$key_value'";
			$db->setQuery( $query );
			$result = $db->query();
			if(!$result)
			{
				$this->errorCount++;
				$this->errorMsg .= $db->stderr()."<br/>";
			}
		}
		else if($name == "NULL_VALUES")
		{
			$table_name = str_replace("payperdownload_", "payperdownloadplus_", $attrs["TABLE"]);
			$query = "UPDATE #__" . $table_name . " SET ";
			foreach($attrs as $key => $value)
			{
				if(substr($key, 0,  2) == "__")
				{
					$query .= substr($key, 2) . " = NULL,";
				}
			}
			if(substr($query, strlen($query) - 1) == ",")
				$query = substr($query, 0, strlen($query) - 1);
			$query .= " WHERE " . $attrs["KEY"] . " = '" . $db->escape($attrs["KEY_VALUE"]) . "'";
			$db->setQuery( $query );
			$result = $db->query();
			if(!$result)
			{
				$this->errorCount++;
				$this->errorMsg .= $db->stderr()."<br/>";
			}
		}
	}

	function importFromXml($filePath, $table_prefix)
	{
		$this->validXML = false;
		$this->table_prefix = strtoupper ($table_prefix);
		$xml_parser = xml_parser_create();
		xml_set_object($xml_parser, $this);
		xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		if (!($fp = @fopen($filePath, "r"))) {
		   return false;
		}

		while ($data = @fread($fp, 4096)) {
			if (!xml_parse($xml_parser, $data, feof($fp))) {
				echo (sprintf("XML error: %s at line %d",
				   xml_error_string(xml_get_error_code($xml_parser)),
				   xml_get_current_line_number($xml_parser)));
				return false;
			}
		}
		xml_parser_free($xml_parser);
		return $this->errorCount == 0;
	}
}


?>