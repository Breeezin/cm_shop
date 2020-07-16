drop table if exists fonts;
create table fonts ( font_id  integer, font_name char(40) );

drop table if exists colours;
create table colours ( colour_id  integer, colour_name char(40), colour_r smallint, colour_g smallint, colour_b smallint );

drop table if exists alignment;
create table alignment ( align_id  char(1), align_name char(40) );

drop table if exists pdf_printout_attributes;
create table pdf_printout_attributes (
	label_no  			integer,
	addr1_x				integer,
	addr1_y				integer,
	addr1_font_id		integer,
	addr1_font_attr		char(5),
	addr1_font_size		integer,
	addr1_font_align	char(1),
	addr1_font_colour	integer,
	addr2_font_id		integer,
	addr2_font_attr		char(5),
	addr2_font_size		integer,
	addr2_font_align	char(1),
	addr2_font_colour	integer,
	addr3_font_id		integer,
	addr3_font_attr		char(5),
	addr3_font_size		integer,
	addr3_font_align	char(1),
	addr3_font_colour	integer,
	addr4_font_id		integer,
	addr4_font_attr		char(5),
	addr4_font_size		integer,
	addr4_font_align	char(1),
	addr4_font_colour	integer,
	addr5_font_id		integer,
	addr5_font_attr		char(5),
	addr5_font_size		integer,
	addr5_font_align	char(1),
	addr5_font_colour	integer,
	addr6_font_id		integer,
	addr6_font_attr		char(5),
	addr6_font_size		integer,
	addr6_font_align	char(1),
	addr6_font_colour	integer );

insert into fonts values (0, "Arial" );
insert into fonts values (1, "Courier" );
insert into fonts values (2, "Times" );

insert into colours values (0, "Black", 0, 0, 0 );
insert into colours values (1, "Red", 255, 0, 0 );
insert into colours values (2, "Green", 0, 255, 0 );
insert into colours values (3, "Blue", 0, 0, 255 );

insert into alignment values ("L", "Left" );
insert into alignment values ("C", "Centre" );
insert into alignment values ("R", "Right" );


insert into pdf_printout_attributes values (1,
	10, 5, 1, "BI", 16, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "R", 0,
			2, "", 8, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "L", 0 );

insert into pdf_printout_attributes values (2,
	120, 5, 0, "B", 16, "C", 2,
			1, "", 8, "L", 0,
			1, "", 8, "R", 0,
			1, "", 8, "C", 0,
			1, "", 8, "L", 0,
			1, "", 8, "L", 0 );

insert into pdf_printout_attributes values (3,
	10, 45, 2, "B", 16, "C", 3,
			0, "", 8, "L", 0,
			0, "", 8, "R", 0,
			0, "", 8, "C", 0,
			0, "", 8, "L", 0,
			0, "", 8, "L", 0 );

insert into pdf_printout_attributes values (4,
	120, 45, 1, "B", 16, "C", 1,
			2, "", 8, "L", 0,
			2, "", 8, "R", 0,
			2, "", 8, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "L", 0 );

insert into pdf_printout_attributes values (5,
	10, 85, 1, "BI", 16, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "R", 0,
			2, "", 8, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "L", 0 );

insert into pdf_printout_attributes values (6,
	120, 85, 2, "B", 16, "C", 2,
			0, "", 8, "L", 0,
			0, "", 8, "R", 0,
			0, "", 8, "C", 0,
			0, "", 8, "L", 0,
			0, "", 8, "L", 0 );

insert into pdf_printout_attributes values (7,
	10, 125, 1, "B", 16, "C", 3,
			2, "", 8, "L", 0,
			2, "", 8, "R", 0,
			2, "", 8, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "L", 0 );

insert into pdf_printout_attributes values (8,
	120, 125, 2, "B", 16, "C", 2,
			2, "", 8, "L", 0,
			2, "", 8, "R", 0,
			2, "", 8, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "L", 0 );

insert into pdf_printout_attributes values (9,
	10, 165, 1, "BI", 16, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "R", 0,
			2, "", 8, "L", 0,
			2, "", 8, "C", 0,
			2, "", 8, "L", 0 );

insert into pdf_printout_attributes values (10,
	120, 165, 2, "B", 16, "C", 2,
			0, "", 8, "R", 0,
			0, "", 8, "L", 0,
			0, "", 8, "C", 0,
			0, "", 8, "L", 0,
			0, "", 8, "L", 0 );

insert into pdf_printout_attributes values (11,
	10, 205, 1, "B", 16, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "R", 0,
			2, "", 8, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "L", 0 );

insert into pdf_printout_attributes values (12,
	120, 205, 2, "B", 16, "C", 2,
			2, "", 8, "L", 0,
			2, "", 8, "R", 0,
			2, "", 8, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "L", 0 );

insert into pdf_printout_attributes values (13,
	10, 245, 1, "BI", 16, "C", 1,
			2, "", 8, "R", 0,
			2, "", 8, "L", 0,
			2, "", 8, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "L", 0 );

insert into pdf_printout_attributes values (14,
	120, 245, 2, "B", 16, "C", 2,
			2, "", 8, "L", 0,
			2, "", 8, "R", 0,
			2, "", 8, "C", 0,
			2, "", 8, "L", 0,
			2, "", 8, "L", 0 );
