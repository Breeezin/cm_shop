drop table if exists OnlineShop_ServiceInvoice;
create table OnlineShop_ServiceInvoice (siv_id int(11) not null primary key auto_increment, sic_id int(11) not null, siv_created_date date not null, siv_paid_date date, siv_external_reference varchar(30), siv_notes varchar(255),
siv_1_created_date date not null, siv_1_text varchar(30), siv_1_hours	float, siv_1_amount float, siv_1_tax float,
siv_2_created_date date, siv_2_text varchar(30), siv_2_hours	float, siv_2_amount float, siv_2_tax float,
siv_3_created_date date, siv_3_text varchar(30), siv_3_hours	float, siv_3_amount float, siv_3_tax float,
siv_4_created_date date, siv_4_text varchar(30), siv_4_hours	float, siv_4_amount float, siv_4_tax float,
siv_5_created_date date, siv_5_text varchar(30), siv_5_hours	float, siv_5_amount float, siv_5_tax float,
siv_6_created_date date, siv_6_text varchar(30), siv_6_hours	float, siv_6_amount float, siv_6_tax float
);

drop table if exists OnlineShop_ServiceInvoiceItems;

drop table if exists OnlineShop_ServiceCompany;
create table OnlineShop_ServiceCompany (sic_id int(11) not null primary key, sic_name varchar(255), sic_template_suffix varchar(50), sic_email_address varchar(50));
insert into OnlineShop_ServiceCompany values (0, 'Lyonnel Consulting', '_lc', 'im@admin.com');
insert into OnlineShop_ServiceCompany values (1, 'Totara Corporation', '_tc', 'gort@admin.com');
insert into OnlineShop_ServiceCompany values (2, 'Highco Technology Limited', '_ht', 'biteme@admin.com');
