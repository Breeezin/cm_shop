<?php
	global $sql;

	// Validate the data for each field
	$errors = $this->validate();

	if( !strlen( $this->tableName ) )
	{
		ss_log_message( "Borked update on NoTableName" );
		$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Borked update on NoTableName",
							'text'	=>	'',
						));
		die;
	}

//	ss_log_message( "Updating ".$this->tableName );
//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $errors );

	// Update if no errors validating data
	if (count($errors) == 0)
	{

		// Construct the SQL
		$updateFields = '';
		foreach ($this->fields as $field) {
			$updateSQL = $this->fields[$field->name]->updateSQL();
			$updateFields .= (strlen($updateSQL)?', ':'').$updateSQL;
		}
		
		if ($this->tableTimeStamp !== null) {
			$updateFields .= ', '.$this->tableTimeStamp.' = NOW()';	
		}
		
		startTransaction();

		// Update the fields
		$result = $sql->query("
			UPDATE $this->tableName
			SET $this->tablePrimaryKey = $this->tablePrimaryKey
				$updateFields
			WHERE $this->tablePrimaryKey = '".escape($this->primaryKey)."'
		");

		if( !$result )
		{
			$foo = "UPDATE $this->tableName
			SET $this->tablePrimaryKey = $this->tablePrimaryKey
				$updateFields
			WHERE $this->tablePrimaryKey = '".escape($this->primaryKey)."'";

			ss_log_message( "Borked update on {$this->tableName} : $foo" );
			$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Borked update on {$this->tableName}",
							'text'	=>	$foo,
						));
		}
		
		// Now handle the special fields.. e.g MultiSelectField
		foreach ($this->fields as $field) {
			$this->fields[$field->name]->specialUpdate();
		}
		
		commit();
		
	}
	else
	{
		$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Borked update on {$this->tableName}",
							'text'	=>	print_r($errors, true),
						));
		ss_log_message( "Borked update on {$this->tableName}" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, print_r($errors, true) );
	}
	
	return $errors;
?>
