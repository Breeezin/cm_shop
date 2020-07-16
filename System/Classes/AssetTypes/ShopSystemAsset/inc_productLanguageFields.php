<?php

    /*    This file is referenced absolutely in ProductsAdministration and 
        category products administration.. So if you copy the ShopSystem asset,
        those files will need to be updated with the new path for this file..    */

            //ss_DumpVar($pr_id);
        if ($prd_id === null) {
            
            if (array_key_exists('prd_id',$_REQUEST)) {
                $prd_id = $_REQUEST['prd_id'];
            } else {
                if (is_array($this->ATTRIBUTES)) {
                    if (array_key_exists('prd_id',$this->ATTRIBUTES)) {
                        $prd_id = $this->ATTRIBUTES['prd_id'];                
                    }
                }
            }
        }
        
        $this->addField(new TextField (array(
            'name'            =>    'prd_pr_name',
            'displayName'    =>    'Name',
            'note'            =>    null,
            'required'        =>    true,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '40',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
        )));

        $this->addField(new TextField (array(
            'name'            =>    'prd_window_title',
            'displayName'    =>    'Window Title',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '60',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
        )));

        $this->addField(new HTMLMemoField2 (array(
            'name'            =>    'prd_short',
            'displayName'    =>    'Short Description',
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
        $this->addField(new HTMLMemoField2 (array(
            'name'            =>    'prd_long',
            'displayName'    =>    'Long Description',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'default'        =>    null,
            'size'    =>    '50',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
            'width'    =>    'document.body.clientWidth*0.80',
            'height'    =>    400,
        )));

        $this->addField(new MemoField (array(
            'name'            =>    'prd_keywords',
            'displayName'    =>    'Keywords',
            'note'            =>    'The keywords entered here will be used when a customer uses the product search, but will not be displayed on the website.',
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'default'        =>    null,
            'size'    =>    '1024',    'maxLength'    =>    '1024',
            'rows'    =>    '5',    'cols'        =>    '1024',
        )));

        $this->addField(new MemoField (array(
            'name'            =>    'prd_metadata_description',
            'displayName'    =>    'Meta Description',
            'note'            =>    'Meta Description shown for this product on this website.',
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'default'        =>    null,
            'size'    =>    '1024',    'maxLength'    =>    '1024',
            'rows'    =>    '5',    'cols'        =>    '1024',
        )));

?>
