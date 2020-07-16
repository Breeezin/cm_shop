<?php
	global $sql;


	$shortName = NULL;
	if( $this->tableName == 'shopsystem_products' )
		$shortName = 'Products';
	if( $this->tableName == 'shopsystem_product_extended_options' )
		$shortName = 'Products';
	if( $this->tableName == 'shopsystem_categories' )
		$shortName = 'Categories';

	if( $shortName )
	{
		if ($this->primaryKey === null)
		{
			if ($this->tablePrimaryMinValue != null) 
				$this->primaryKey = newPrimaryKeyWithMin($this->tableName,$this->tablePrimaryKey, $this->tablePrimaryMinValue);
			else
				$this->primaryKey = newPrimaryKey($this->tableName,$this->tablePrimaryKey);
		}

		foreach ($this->fields as $field)
		{
			$val = ltrim( rtrim( $this->fields[$field->name]->value ) );
			if( strlen( $val ) )
				ss_audit( 'insert', $shortName, $this->primaryKey, $field->name.' is '.$val );
		}

	}

//		$alterations = "Insert on {$this->tableName}:{$this->primaryKey} from {$_SERVER['REMOTE_ADDR']} <br/>Logged in as {$_SESSION['User']['us_email']} using {$_SERVER['HTTP_USER_AGENT']} <br/>At ".strftime( '%F %T' )."<br/>";
/*
			$alterations .= "{$field->name} to '{$this->fields[$field->name]->value}'<br/>";

		$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Insertion from {$_SESSION['User']['us_email']} on {$this->tableName}",
							'html'	=>	$alterations,
						));
*/

?>
