drop table if exists AccountSummary;
create table AccountSummary (
as_country integer not null,
as_site varchar(64), 
as_year integer not null,
as_month integer not null, 
as_day integer not null,
as_num_orders integer not null default 0,
as_sales double not null default 0,
as_shipping_value double not null default 0, 
as_reship_value double not null default 0, 
as_reship_boxes integer not null default 0, 
as_refund_value double not null default 0, 
as_cm_value double not null default 0,
as_new_blacklist integer not null default 0 );

drop table if exists folders;
create table folders as select distinct or_site_folder from OnlineShop_Orders;
drop table if exists days;
create table days as select distinct DATE( or_recorded ) as day from OnlineShop_Orders;
insert into AccountSummary (as_country, as_site, as_year, as_month, as_day ) select cn_id, folders.or_site_folder, YEAR( day ), MONTH( day), DAY(day) from Countries, folders, days;

create index as_ix1 on AccountSummary (as_country, as_site, as_year, as_month, as_day);

update OnlineShop_Orders set or_country = convert( substr( or_shipping_values, locate( 'ShDe0_50A4', or_shipping_values ) + 18, 3), UNSIGNED) where or_country IS NULL;

create table temp_just_sales select or_country, or_site_folder, YEAR( or_recorded ) as year, MONTH( or_recorded) as month, DAY( or_recorded) as day, count(*) as orders, sum(tr_total) as sales from OnlineShop_Orders join Transactions on tr_id = or_tr_id
WHERE tr_charge_total IS NOT NULL
AND tr_completed = 1
AND tr_status_link < 3
and (or_paid IS NOT NULL or or_paid_not_shipped IS NOT NULL )
and or_reshipment IS NULL
AND or_card_denied IS NULL AND or_cancelled IS NULL
group by or_country, or_site_folder, YEAR( or_recorded ), MONTH( or_recorded), DAY( or_recorded);

update AccountSummary, temp_just_sales
set as_num_orders = orders, 
    as_sales = sales
where as_country = or_country and as_site = or_site_folder and as_year = year and as_month = month and as_day = day;

drop table temp_just_sales;

create table temp_all_sales select or_country, or_site_folder, YEAR( or_recorded ) as year, MONTH( or_recorded) as month, DAY( or_recorded) as day, sum( tr_incl_shipping + tr_excl_shipping ) as shipping, sum(or_profit) as cm, sum(tr_profit) as profit from OnlineShop_Orders join Transactions on tr_id = or_tr_id
WHERE tr_charge_total IS NOT NULL
AND tr_completed = 1
AND tr_status_link < 3
and (or_paid IS NOT NULL or or_paid_not_shipped IS NOT NULL )
AND or_card_denied IS NULL AND or_cancelled IS NULL
group by or_country, or_site_folder, YEAR( or_recorded ), MONTH( or_recorded), DAY( or_recorded);

create index temp_all_sales_ix on temp_all_sales (year, month, day);

update AccountSummary, temp_all_sales
set as_shipping_value = shipping,
    as_cm_value = cm,
	as_profit = profit
where as_country = or_country and as_site = or_site_folder and as_year = year and as_month = month and as_day = day;

drop table temp_all_sales;




create table temp_refunds as
select or_country, or_site_folder, YEAR( rfd_timestamp ) as OrYear, MONTH( rfd_timestamp) as OrMonth, DAY( rfd_timestamp) as OrDay, sum(rfd_amount) as OrRefunds from OnlineShop_Orders
join OnlineShop_Refunds on  rfd_or_id = or_id 
group by or_country, or_site_folder, YEAR( or_recorded ), MONTH( or_recorded), DAY( or_recorded);

create index temp_refunds_ix on temp_refunds (OrYear, OrMonth, OrDay);

update AccountSummary, temp_refunds set as_refund_value = OrRefunds, as_sales = as_sales - OrRefunds where as_country = or_country and as_site = or_site_folder and as_year = OrYear and as_month = OrMonth and as_day = OrDay;

drop table if exists temp_refunds;



create table temp_reships_value as
select or_country, or_site_folder, YEAR( or_recorded ) as OrYear, MONTH( or_recorded) as OrMonth, DAY( or_recorded) as OrDay, -sum(or_profit) as OrReship from OnlineShop_Orders join Transactions on  tr_id = or_tr_id 
where or_reshipment IS NOT NULL
                    AND tr_completed = 1
                    AND tr_status_link < 3
 and tr_total IS NOT NULL
group by or_country, or_site_folder, YEAR( or_recorded ), MONTH( or_recorded), DAY( or_recorded);

create index temp_reships_value_ix on temp_reships_value ((OrYear, OrMonth, OrDay);

update AccountSummary, temp_reships_value set as_reship_value = OrReship where as_country = or_country and as_site = or_site_folder and as_year = OrYear and as_month = OrMonth and as_day = OrDay;

drop table if exists temp_reships_value;



create table temp_reships_boxes as
select or_country, or_site_folder, YEAR( or_recorded ) as OrYear, MONTH( or_recorded) as OrMonth, DAY( or_recorded) as OrDay, sum(Quantity) as OrReshipBoxes
from OnlineShop_Orders 
join Transactions on tr_id = or_tr_id 
left join OnlineShop_AcmeOrderProducts on OrderLink = or_id 
where or_reshipment IS NOT NULL
                    AND tr_completed = 1
                    AND tr_status_link < 3
 and tr_total IS NOT NULL
group by or_country, or_site_folder, YEAR( or_recorded ), MONTH( or_recorded), DAY( or_recorded);

create index temp_reships_boxes_ix on temp_reships_boxes ((OrYear, OrMonth, OrDay);

update AccountSummary, temp_reships_boxes set as_reship_boxes = OrReshipBoxes where as_country = or_country and as_site = or_site_folder and as_year = OrYear and as_month = OrMonth and as_day = OrDay;

drop table if exists temp_reships_boxes;

delete from AccountSummary where as_num_orders = 0
and as_sales = 0
and as_shipping_value = 0
and as_reship_value = 0
and as_reship_boxes = 0
and as_refund_value = 0
and as_cm_value = 0;

alter table OnlineShop_Orders add column or_summarised boolean not null default false;
update OnlineShop_Orders set or_summarised = true where or_recorded  < '20100727';

delete from AccountSummary where as_year = 2010 and as_month = 7 and as_day = 27;

