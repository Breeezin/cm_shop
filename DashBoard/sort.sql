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
