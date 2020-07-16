CREATE TABLE Assets (
	AssetID int NOT NULL PRIMARY KEY,
	as_name varchar (255) NULL ,
	as_last_modified datetime NULL ,
	as_archive bit NULL ,
	as_serialized text NULL ,
	as_type varchar (25) NULL ,
	as_appear_in_menus bit NULL ,
	as_layout_serialized text NULL ,
	as_dev_asset bit NULL ,
	as_promotion_date datetime NULL ,
	as_reversion_date datetime NULL ,
	as_sort_order int NULL ,
	as_parent_as_id int NULL ,
	as_owner_au_id int NULL,
	as_can_use_default bit NULL,
	as_can_admin_default bit NULL,
	as_child_can_use_default bit NULL,
	as_child_can_admin_default bit NULL
);


CREATE TABLE AssetTypes (
	AstTyID int NOT NULL PRIMARY KEY,
	AstTyDisplay varchar (50) NULL ,
	AstTyName varchar (50) NULL ,
	AstTyLimit int NULL 
);

CREATE TABLE AssetUserGroups (
	aug_ug_id int NOT NULL ,
	AssetLink int NOT NULL ,
	Use_ bit NULL ,
	Administer bit NULL ,
	ApplyToChildren bit NULL,
	PRIMARY KEY (aug_ug_id, AssetLink)
);

CREATE TABLE Configuration (
	cfg_id int NOT NULL PRIMARY KEY,
	cfg_website_name varchar (127) NULL ,
	cfg_email_address varchar (255) NULL ,
	cfg_bcc_address varchar (255) NULL ,
	cfg_keywords text NULL ,
	cfg_description text NULL ,
	CoAdminUsername varchar (25) NULL ,
	CoAdminPassword varchar (25) NULL ,
	cfg_contact_details text NULL ,
	cfg_options text NULL 
);

CREATE TABLE Countrys (
	cn_id int NOT NULL PRIMARY KEY,
	cn_name varchar (128) NULL ,
	cn_two_code varchar (2) NULL ,
	cn_three_code varchar (3) NULL 
);

CREATE TABLE UserGroups (
	ug_id int NOT NULL PRIMARY KEY,
	ug_name varchar (50) NULL ,
	ug_mailing_list bit NULL 
);


CREATE TABLE Users (
	us_id int NOT NULL PRIMARY KEY,
	us_first_name varchar (127) NULL ,
	us_last_name varchar (127) NULL ,
	us_email varchar (255) NULL ,
	us_password varchar (10) NULL ,
	us_details_serialized text NULL ,
	UsUUID varchar (40) NULL 
);


CREATE TABLE UserUserGroups (
	UserLink int NOT NULL ,
	aug_ug_id int NOT NULL,
	PRIMARY KEY (UserLink, aug_ug_id) 
);
