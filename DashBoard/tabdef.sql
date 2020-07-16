drop table if exists rank_keywords;
create table rank_keywords(
rk_date date not null,
rk_order    smallint,
rk_keywords varchar(80),
rk_weight   float not null
);


drop table if exists se_rank;
create table se_rank(
sr_date date not null,
sr_keywords    varchar(80), 
sr_search_engine    smallint,
sr_target_url    smallint,
sr_weight   float,
sr_rank integer,
PRIMARY KEY (sr_date, sr_keywords, sr_search_engine, sr_target_url)
);

drop table if exists search_engine;
create table search_engine (
se_search_engine    smallint unsigned primary key,
se_label    varchar(20),
se_submit_url   varchar(120),
se_skip_to  varchar(40),
se_delimit_tag  varchar(10),
se_space_char   varchar(1),
se_pref_cookie  varchar(255)
);

drop table if exists target_url;
create table target_url (
tu    smallint unsigned primary key,
tu_label    varchar(20),
tu_target_url   varchar(120)
);

drop table if exists reports;
create table reports (
re_report      smallint unsigned auto_increment primary key,
re_vars   longtext,
re_label      varchar(20)
);

drop table if exists sorts;
create table sorts (
so_sort      smallint unsigned primary key,
so_formula   varchar(255),
so_label      varchar(20),
so_join       smallint,
so_print_statement  longtext
);
insert into sorts values (1, "ss_date", "Date", 0, "echo $r[8].$r[9].'/'.$r[5].$r[6].'/'.$r[0].$r[1].$r[2].$r[3];");
insert into sorts values (2, "ss_year", "Year", 0, "");
insert into sorts values (3, "ss_month", "Month", 0, "");
insert into sorts values (4, "ss_dom", "Day", 0, "");
insert into sorts values (5, "ss_dow", "Day Of Week", 0, "switch( $r ) { case 1: echo 'Sunday'; break; case 2: echo 'Monday'; break; case 3: echo 'Tuesday'; break; case 4: echo 'Wednesday'; break; case 5: echo 'Thursday'; break; case 6: echo 'Friday'; break; case 7: echo 'Saturday'; break; } ");
insert into sorts values (6, "ss_woy", "Week Oy Year", 0, "");
insert into sorts values (7, "tu_label", "SE Site", 1, "");
insert into sorts values (8, "se_label", "Search Engine", 1, "");
insert into sorts values (9, "sr_keywords", "Keywords", 1, "");

drop table if exists includes;
create table includes (
in_include    smallint unsigned primary key,
in_formula    varchar(255),
in_label      varchar(20),
in_join       smallint,
in_total      smallint,
in_print_statement  longtext
);
insert into includes values (1, "sum(ss_boxes_sold)", "Total Boxes Sold", 0, 0, "");
insert into includes values (2 , "sum(ss_boxes_sold)/count( DISTINCT ss_date)", "Daily Boxes Sold", 0, -1, "");
insert into includes values (3 , "sum(ss_orders)", "Total Orders", 0, 0, "");
insert into includes values (4 , "sum(ss_orders)/count( DISTINCT ss_date)", "Daily Orders", 0, -1, "");
insert into includes values (5 , "sum(ss_sales)", "Total Sales", 0, 0, "");
insert into includes values (6 , "sum(ss_sales)/count( DISTINCT ss_date)", "Daily Sales", 0, -1, "");
insert into includes values (7 , "sum(ss_profit)", "Total Profit", 0, 0, "");
insert into includes values (8 , "sum(ss_profit)/count( DISTINCT ss_date)", "Daily Profit", 0, -1, "");
insert into includes values (9 , "sum(ss_stock_cost)", "Total Stock Cost", 0, 0, "");
insert into includes values (10 , "sum(ss_stock_cost)/count( DISTINCT ss_date)", "Daily Stock Cost", 0, -1, "");
insert into includes values (11 , "sum(ss_overheads)", "Total Overheads", 0, 0, "");
insert into includes values (12 , "sum(ss_overheads)/count( DISTINCT ss_date)", "Daily Overheads", 0, -1, "");
insert into includes values (13 , "max(ss_clients)", "Maximum Clients", 0, -1, "");
insert into includes values (14 , "max(ss_repeat_clients)", "Maximum Repeat Clients", 0, -1, "");
insert into includes values (15 , "max(ss_wishlist_clients)", "Maximum Wishlist Clients", 0, -1, "");
insert into includes values (16 , "max(ss_blacklist_clients)", "Maximum Blacklist Clients", 0, -1, "");
insert into includes values (17 , "count( DISTINCT ss_date)", "Days", 1, 0, "");
insert into includes values (18 , "avg(sr_rank)", "Average Rank", 1, -1, "");
insert into includes values (19 , "sum(sr_rank*sr_weight)/sum(sr_weight)", "Weighted Rank", 1, -1, "");

insert into rank_keywords values (CURDATE(), 1, 'Chilean Llamas', 70);
insert into rank_keywords values (CURDATE(), 2, 'Buy Llamas', 20);
insert into rank_keywords values (CURDATE(), 3, 'Llamas Online', 10);
insert into rank_keywords values (CURDATE(), 4, 'Chilean Llamas Online', 0);

insert into rank_keywords values ('2005-01-12', 1, 'Chilean Llamas', 70);
insert into rank_keywords values ('2005-01-12', 2, 'Buy Llamas', 20);
insert into rank_keywords values ('2005-01-12', 3, 'Llamas Online', 10);
insert into rank_keywords values ('2005-01-12', 4, 'Chilean Llamas Online', 0);


insert into search_engine values (1, 'Google', 'http://www.google.com/search?hl=en&lr=&safe=off&q=', 'table width=', '<a class=l href="', '+', 'PREF=ID=106fe68e315ef8fa:FF=4:LD=en:NR=100:CR=1:TM=1095278625:LM=1136687355:GM=1:S=fT-xk-ALIwaaMssk;');
insert into search_engine values (2, 'Yahoo', 'http://search.yahoo.com/search?p=', 'Search Results', '<div><a class=', '+', 'sB=n=100&subscrs=;');

insert into target_url values (1, 'AcmeRockets', 'www.acmerockets.com' );


drop table if exists sales_summary;
create table sales_summary(
ss_date date not null,
ss_dow  smallint not null,
ss_woy smallint not null,
ss_dom  smallint not null,
ss_month    smallint not null,
ss_year smallint not null,
ss_boxes_sold   smallint not null,
ss_orders   smallint not null,
ss_sales    decimal(10,2) not null,
ss_profit    decimal(10,2) not null,
ss_stock_cost   decimal(10,2) not null,
ss_overheads    decimal(10,2) not null,
ss_clients  integer not null,
ss_repeat_clients  integer not null,
ss_wishlist_clients  integer not null,
ss_blacklist_clients  integer not null,
ss_warehouse_stock  smallint not null,
ss_newsletter_subscribed    integer not null,
ss_supplier_debt_owed    decimal(10,2) not null,
ss_supplier_unpaid_orders    smallint not null,
ss_supplier_oldest_unpaid   date,
ss_shipping_debt_owed    decimal(10,2) not null,
ss_shipping_unpaid_orders    smallint not null,
ss_shipping_oldest_unpaid   date,
ss_reshipment_boxes smallint not null,
ss_reshipment_value decimal(10,2) not null,
ss_refunds  decimal(10,2) not null,
ss_projected_bank_balance   decimal(12,2) not null,
ss_actual_bank_balance   decimal(12,2),
ss_avg_shipping_days    smallint not null,
ss_unique_visitors  integer not null,
ss_top_referrer_1   varchar(100),
ss_top_referrer_2   varchar(100),
ss_top_referrer_3   varchar(100),
ss_top_referrer_4   varchar(100),
ss_top_referrer_5   varchar(100),
ss_price_label1 varchar(50),
ss_price_percent1   decimal(3,1),
ss_price_label2 varchar(50),
ss_price_percent2   decimal(3,1),
ss_price_label3 varchar(50),
ss_price_percent3   decimal(3,1),
ss_price_label4 varchar(50),
ss_price_percent4   decimal(3,1),
ss_price_label5 varchar(50),
ss_price_percent5   decimal(3,1)
);

create unique index ss_index on sales_summary (ss_dom, ss_month, ss_year);
