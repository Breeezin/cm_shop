drop table if exists stock_reports;
create table stock_reports (
re_report      smallint unsigned auto_increment primary key,
re_vars   longtext,
re_label      varchar(20)
);

drop table if exists stock_sorts;
create table stock_sorts (
so_sort      smallint unsigned primary key,
so_formula   varchar(255),
so_label      varchar(20),
so_join       smallint,
so_print_statement  longtext
);
insert into stock_sorts values (7, "PrExternal", "Vendor ID", 1, "");
insert into stock_sorts values (10, "pr_id", "Product ID", 1, "");
insert into stock_sorts values (20, "pr_name", "Product Name", 1, "");
insert into stock_sorts values (30, "pr_combo", "Combo Product", 1, "");
insert into stock_sorts values (40, "PrExternal", "Product Vendor", 1, "");
insert into stock_sorts values (50, "pr_ca_id", "Category ID", 1, "");
insert into stock_sorts values (60, "ca_name", "Category Name", 1, "");
insert into stock_sorts values (80, "pro_stock_code", "Stock Code", 1, "");
insert into stock_sorts values (90, "pro_source_currency", "Source Currency", 1, "");
insert into stock_sorts values (100, "pr_sort_order", "Sort Order", 1, "");
insert into stock_sorts values (110, "pr_offline", "Offline", 1, "");
insert into stock_sorts values (130, "pr_shipping_method_us", "Shipping Method", 1, "");
insert into stock_sorts values (140, "pr_shipping_usps", "USPS Method", 1, "");
insert into stock_sorts values (150, "PrShippingFedex", "Fedex Method", 1, "");
insert into stock_sorts values (160, "ct_name", "Llama Type", 1, "");

drop table if exists stock_includes;
create table stock_includes (
in_include    smallint unsigned primary key,
in_formula    varchar(255),
in_label      varchar(20),
in_join       smallint,
in_total      smallint,
in_print_statement  longtext
);
insert into stock_includes values (5 , "sum(pro_stock_available)", "Total Stock", 0, 0, "");
insert into stock_includes values (10 , "sum(PrCigarLength)/count(PrCigarLength)", "Av Llama Length", 0, 0, "");
insert into stock_includes values (15 , "sum(PrCigarThickness)/count(PrCigarThickness)", "Av Llama Thickness", 0, 0, "");
insert into stock_includes values (20 , "sum(pro_supplier_price)", "Total Cost Supplier", 0, 0, "");
insert into stock_includes values (30 , "sum(pro_wholesaler_price)", "Total Cost Wholesale", 0, 0, "");
insert into stock_includes values (40 , "sum(pro_price)", "Total Retail Price", 0, 0, "");
insert into stock_includes values (50 , "sum(pro_special_price)", "Total Special Price", 0, 0, "");
insert into stock_includes values (60, "sum(pr_shipping_d1)", "Total Shipping 1", 0, 0, "");
insert into stock_includes values (70, "sum(pr_shipping_d2)", "Total Shipping 2", 0, 0, "");
insert into stock_includes values (80, "sum(pr_shipping_d3)", "Total Shipping 3", 0, 0, "");
insert into stock_includes values (90, "sum(pro_weight)", "Total Weight", 0, 0, "");
insert into stock_includes values (100, "sum(pr_customer_rating)", "Total Customer Rating", 0, 0, "");
