drop table if exists pr_reports;
create table pr_reports (
re_report      smallint unsigned auto_increment primary key,
re_vars   longtext,
re_label      varchar(20)
);

drop table if exists pr_sorts;
create table pr_sorts (
so_sort      smallint unsigned primary key,
so_formula   varchar(255),
so_label      varchar(20),
so_join       smallint,
so_print_statement  longtext
);
insert into pr_sorts values (1, "DATE( or_recorded )", "RecordedDate", 0, "echo $r[8].$r[9].'/'.$r[5].$r[6].'/'.$r[0].$r[1].$r[2].$r[3];");
insert into pr_sorts values (2, "or_purchaser_email", "Purchaser Email", 1, "");
insert into pr_sorts values (3, "or_tr_id", "Order Number", 1, "");
insert into pr_sorts values (4, "or_us_id", "UserID", 1, "");
insert into pr_sorts values (5, "pr_id", "Product Number", 1, "");
insert into pr_sorts values (6, "pr_name", "Product", 1, "");
insert into pr_sorts values (7, "pr_combo", "Combo Product", 1, "");
insert into pr_sorts values (8, "PrExternal", "Product Vendor", 1, "");
insert into pr_sorts values (9, "pr_ca_id", "Category Number", 1, "");
insert into pr_sorts values (10, "pro_price", "Product Price", 1, "");
insert into pr_sorts values (11, "pro_stock_code", "Stock Code", 1, "");
insert into pr_sorts values (12, "YEAR( or_recorded )", "Year", 1, "");
insert into pr_sorts values (13, "MONTH( or_recorded )", "Month", 1, "");
insert into pr_sorts values (14, "DAY( or_recorded )", "Day", 1, "");
insert into pr_sorts values (15, "WEEK( or_recorded )", "Week", 1, "");

drop table if exists pr_includes;
create table pr_includes (
in_include    smallint unsigned primary key,
in_formula    varchar(255),
in_label      varchar(20),
in_join       smallint,
in_total      smallint,
in_print_statement  longtext
);
insert into pr_includes values (1, "sum(orpr_qty)", "Total Boxes Sold", 0, 0, "");
insert into pr_includes values (2 , "sum(orpr_price)", "Income", 0, 0, "");
insert into pr_includes values (3 , "sum(orpr_qty*IF( pro_special_price, pro_special_price, pro_price) )", "Sales", 0, 0, "");
insert into pr_includes values (4 , "sum(orpr_price) - sum(orpr_qty*IF( pro_special_price, pro_special_price, pro_price) )", "Shipping", 0, 0, "");
insert into pr_includes values (5 , "sum(orpr_qty*pro_supplier_price)", "Total Supplier Cost", 0, 0, "");
insert into pr_includes values (6 , "sum(or_profit*orpr_price/tr_total)", "Profit", 0, 0, "");
insert into pr_includes values (7 , "sum(or_profit*orpr_price/tr_total)*0.99", "C. Margin", 0, 0, "");
insert into pr_includes values (8 , "100*sum(or_profit*orpr_price/tr_total)*0.99/sum(orpr_qty*IF( pro_special_price, pro_special_price, pro_price) )", "CM%", 0, 0, "");
