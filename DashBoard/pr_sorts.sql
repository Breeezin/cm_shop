delete from pr_sorts;

INSERT INTO `pr_sorts` VALUES 
(1,'DATE( or_recorded )','RecordedDate',0,'echo $r[8].$r[9].\'/\'.$r[5].$r[6].\'/\'.$r[0].$r[1].$r[2].$r[3];'),
(2,'or_purchaser_email','Purchaser Email',1,''),
(3,'or_tr_id','Order Number',1,''),
(4,'or_us_id','UserID',1,''),
(5,'op_pr_id','Product Number',1,''),
(6,'op_pr_name','Product',1,''),
(7,'op_stock_code','Stock Code',1,''),
(8,'pr_ve_id','Product Vendor',1,''),
(9,'ca_name','Product Category',1,''),
(12,'YEAR( or_recorded )','Year',1,''),
(13,'MONTH( or_recorded )','Month',1,''),
(14,'DAY( or_recorded )','Day',1,''),
(15,'WEEK( or_recorded )','Week',1,''),
(16,'or_site_folder','op_site_folder',1,NULL),
(17,'cn_name','Shipping Country',1,NULL),
(20,'pg_name','Payment Gateway',0,NULL);
