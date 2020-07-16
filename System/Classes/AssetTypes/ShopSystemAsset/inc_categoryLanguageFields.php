<?php

    /*    This file is referenced absolutely in ProductsAdministration and 
        category products administration.. So if you copy the ShopSystem asset,
        those files will need to be updated with the new path for this file..    */

        //ss_DumpVar($pr_id);

		// hijack this, use it for another table
		$this->tablePrimaryKey = 'cad_id';

		if( !array_key_exists( 'cad_id', $this->ATTRIBUTES ) )
		{
			$wrow = getRow( "Select * from shopsystem_category_descriptions WHERE cad_language = ".$this->ATTRIBUTES['cad_language']." and cad_ca_id = ".$this->ATTRIBUTES['ca_id'] );
			if( !$wrow || !is_numeric( $wrow['cad_id'] ) )
			{
				query( "insert into shopsystem_category_descriptions (cad_language, cad_ca_id) values (".$this->ATTRIBUTES['cad_language'].", ".$this->ATTRIBUTES['ca_id'].")" );
				$wrow = getRow( "Select * from shopsystem_category_descriptions WHERE cad_language = ".$this->ATTRIBUTES['cad_language']." and cad_ca_id = ".$this->ATTRIBUTES['ca_id'] );
			}
			$cad_id = $wrow['cad_id'];
			$this->ATTRIBUTES['cad_id'] = $cad_id;
		}
		else
		{
			$cad_id = $this->ATTRIBUTES['cad_id'];
		}

		$this->primaryKey = $cad_id;

		$tableDisplayFields = array('cad_name','cad_window_title','cad_description');
		$tableDisplayFieldTitles = array('Category Name','Window Title','Description');
		$this->__construct(array(
				'prefix'                    =>    'ShopSystem_CategoryDescriptions',
				'singular'                    =>    'Category',
				'plural'                    =>    'Categories',
				'tableName'                    =>    'shopsystem_category_descriptions',
				'tablePrimaryKey'            =>    'cad_id',
				'tableDisplayFields'        =>    $tableDisplayFields,
				'tableDisplayFieldTitles'    =>    $tableDisplayFieldTitles,
				));

		$this->fields = array();

		$this->addField(new HiddenField (array(
    			'name'			=>	'cad_id',
                'defaultValue'  =>  $cad_id,
    		)));

		$this->addField(new HiddenField (array(
    			'name'			=>	'cad_language',
                'defaultValue'  =>  $this->ATTRIBUTES['cad_language'],
    		)));

        $this->addField(new TextField (array(
            'name'            =>    'cad_name',
            'displayName'    =>    'Category Name',
            'note'            =>    null,
            'required'        =>    true,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '40',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
        )));

        $this->addField(new TextField (array(
            'name'            =>    'cad_window_title',
            'displayName'    =>    'Window Title',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '60',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
        )));

        $this->addField(new MemoField (array(
            'name'            =>    'cad_metadata_keywords',
            'displayName'    =>    'Metadata Keywords',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '1024',    'maxLength'    =>    '1024',
            'rows'    =>    '6',    'cols'        =>    '255',
        )));

        $this->addField(new MemoField (array(
            'name'            =>    'cad_metadata_description',
            'displayName'    =>    'Metadata Description',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '1024',    'maxLength'    =>    '1024',
            'rows'    =>    '6',    'cols'        =>    '255',
        )));

        $this->addField(new HTMLMemoField2 (array(
            'name'            =>    'cad_description',
            'displayName'    =>    'Description',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'default'        =>    null,
            'size'    =>    '50',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
            'width'    =>    'document.body.clientWidth*0.80',
            'height'    =>    200,
        )));

?>
