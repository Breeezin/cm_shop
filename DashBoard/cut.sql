drop table if exists includes;
create table includes (
in_include    smallint unsigned primary key,
in_formula    varchar(255),
in_label      varchar(20),
in_join       smallint
);
insert into includes values (1, "sum(ss_boxes_sold)", "Total Boxes Sold", 0);
insert into includes values (2 , "sum(ss_boxes_sold)/count( DISTINCT ss_date)", "Daily Boxes Sold", 0);
insert into includes values (3 , "sum(ss_orders)", "Total Orders", 0);
insert into includes values (4 , "sum(ss_orders)/count( DISTINCT ss_date)", "Daily Orders", 0);
insert into includes values (5 , "sum(ss_sales)", "Total Sales", 0);
insert into includes values (6 , "sum(ss_sales)/count( DISTINCT ss_date)", "Daily Sales", 0);
insert into includes values (7 , "sum(ss_profit)", "Total Profit", 0);
insert into includes values (8 , "sum(ss_profit)/count( DISTINCT ss_date)", "Daily Profit", 0);
insert into includes values (9 , "sum(ss_stock_cost)", "Total Stock Cost", 0);
insert into includes values (10 , "sum(ss_stock_cost)/count( DISTINCT ss_date)", "Daily Stock Cost", 0);
insert into includes values (11 , "sum(ss_overheads)", "Total Overheads", 0);
insert into includes values (12 , "sum(ss_overheads)/count( DISTINCT ss_date)", "Daily Overheads", 0);
insert into includes values (13 , "max(ss_clients)", "Maximum Clients", 0);
insert into includes values (14 , "max(ss_repeat_clients)", "Maximum Repeat Clients", 0);
insert into includes values (15 , "max(ss_wishlist_clients)", "Maximum Wishlist Clients", 0);
insert into includes values (16 , "max(ss_blacklist_clients)", "Maximum Blacklist Clients", 0);
insert into includes values (17 , "count( DISTINCT ss_date)", "Days", 1);
insert into includes values (18 , "avg(sr_rank)", "Average Rank", 1);
insert into includes values (19 , "sum(sr_rank*sr_weight)/count(*)", "Weighted Rank", 1);

