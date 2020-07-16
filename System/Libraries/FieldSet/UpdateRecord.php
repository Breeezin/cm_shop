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

//		$alterations = "Alterations on {$this->tableName}:{$this->primaryKey} from {$_SERVER['REMOTE_ADDR']} <br/>Logged in as {$_SESSION['User']['us_email']} using {$_SERVER['HTTP_USER_AGENT']} <br/>At ".strftime( '%F %T' )."<br/>";
		$old = getRow( "select * from $this->tableName where $this->tablePrimaryKey = '".escape($this->primaryKey)."'" );
		foreach ($this->fields as $field) {
			if( array_key_exists( $field->name, $old ) )
			{
				if( (strlen($old[$field->name]) || strlen( $this->fields[$field->name]->value ) )
				 && strip_tags($old[$field->name]) != strip_tags($this->fields[$field->name]->value)
				 && "'".$old[$field->name]."'" != $this->fields[$field->name]->value
				 && $this->fields[$field->name]->value != "NULL" )
				{
					
					if( ( $field->name != 'pr_short' ) && ( $field->name != 'pr_long' ) )
						ss_audit( 'update', $shortName, $this->primaryKey, $field->name.' from '.$old[$field->name].' to '.$this->fields[$field->name]->value );
				}
			}
			else
			{
				if( IsSet( $field->linkTableName ) && strlen( $field->linkTableName ) )
				{
					$linkold = getRow( "select * from {$field->linkTableName} where {$field->linkTableOurKey} = '".escape($this->primaryKey)."'" );
					foreach( $field->value[0] as $key=>$val )
						if( array_key_exists( $key, $linkold ) && $linkold[$key] != $val )
						{
//							$alterations .= "{$key} from '{$linkold[$key]}' to '{$val}'<br/>";

							if( $key == 'pro_price' )
								ss_audit( 'update', 'Products', $this->primaryKey, 'Price from '.$linkold[$key].' to '.$val );

							if( $key == 'pro_special_price')
								ss_audit( 'update', 'Products', $this->primaryKey, 'Special Price from '.$linkold[$key].' to '.$val );

							if( $key == 'pro_stock_available')
								ss_audit( 'update', 'Products', $this->primaryKey, 'Stock from '.$linkold[$key].' to '.$val );
						}
				}
			}
		}

/*
		$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Alteration from {$_SESSION['User']['us_email']} on {$this->tableName}",
							'html'	=>	$alterations,
						));
*/

	}

?>
