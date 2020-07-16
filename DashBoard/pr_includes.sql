delete from pr_includes;

INSERT INTO `pr_includes` VALUES (1,'sum(op_quantity)','Total Boxes Sold',0,0,''),
(2,'sum(op_price_paid * op_quantity)','Total Income',0,0,''),
(3,'sum(op_price_paid * op_quantity)/sum(op_quantity)','Average Price',0,0,''),
(4,'sum(op_included_freight * op_quantity)','Included Freight',0,0,''),
(5,'sum(op_extra_freight * op_quantity)','Extra Freight',0,0,''),
(6,'sum(op_quantity*op_supplier_price)','Total Supplier Cost',0,0,''),
(7,'sum(op_profit*op_quantity)','Profit',0,0,'');
