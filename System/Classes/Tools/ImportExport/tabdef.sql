drop table if exists daily_skim;
create table daily_skim (
	ds_id	integer not NULL AUTO_INCREMENT,
	ds_company	integer,
	ds_timestamp			timestamp,
	ds_amount		float,
	PRIMARY KEY (ds_id));

drop table if exists invoice_configuration;
create table invoice_configuration(
ic_symbol    varchar(10), 
ic_description    varchar(80), 
ic_company	integer,
ic_value    float,
PRIMARY KEY (ic_symbol)
);

insert into invoice_configuration values ('FIXED', 'Fixed Cost', -1, 1000);
insert into invoice_configuration values ('HIGHCO', 'Profit Percent for Highco Tech', 2, 0.425);
insert into invoice_configuration values ('TOTARA', 'Profit Percent for Totara Corp', 1, 0.425);
