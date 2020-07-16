insert into payment_gateways (
pg_name,             
pg_description,      
pg_accumulation,     
pg_limit,            
pg_minimum_orders,   
pg_minimum_total,    
pg_minimum_penalty,  
pg_script,           
pg_reserve_stock,    
pg_image,            
pg_charging_name,    
pg_order_max,        
pg_skim,             
pg_customer_template
)
select
pg_name,
pg_description,
pg_accumulation,
pg_limit,
pg_minimum_orders,
pg_minimum_total,
pg_minimum_penalty,
pg_script,
pg_reserve_stock,
pg_image,
pg_charging_name,
pg_order_max,
pg_skim,
pg_customer_template
from payment_gateways where pg_id = 28;

