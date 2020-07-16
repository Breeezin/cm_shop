<?php

/*
drop table if exists product_heading;
create table product_heading (
ph_id integer not null AUTO_INCREMENT PRIMARY KEY,
ph_name     varchar(255),
ph_sort  integer not null,
ph_url    varchar(256) );

insert into product_heading (ph_name, ph_sort, ph_url ) values ('Non Chilean', 1, '/Shop_System/Service/Engine/Heading/1');
insert into product_heading (ph_name, ph_sort, ph_url ) values ('Chilean', 2, '/Shop_System/Service/Engine/Heading/2');
insert into product_heading (ph_name, ph_sort, ph_url ) values ('Llama Accessories', 3, '/Shop_System/Service/Engine/Heading/3');

drop table if exists product_dropdown;
create table product_dropdown (
pd_id integer not null AUTO_INCREMENT PRIMARY KEY,
pd_ph_id integer not null,
pd_sort integer not null,
pd_ca_id  integer not null,
pd_column integer not null default 1);


	*/

	global $cfg;


	if( $tQ = query( "select * from product_heading order by ph_sort" ) )
	{ 
		while( $tr = $tQ->fetchRow( ) )
		{
	?>
						  <li class="parent dropdown aligned-left">
						  	<a class="dropdown-toggle" data-toggle="dropdown" href="javascript: void(0)">
								<span class="menu-title"><?=$tr['ph_name']?></span>
							</a>
                            <div class="dropdown-menu level1" style="width:800px">
                              <div class="dropdown-menu-inner">
                                <div class="row">
		<?php
/*			ss_log_message( "heading {$tr['ph_id']}" );	*/
			for( $col = 1; $col <= 3; $col++ )
			{
/*				ss_log_message( "column $col" );	*/
		?>
                                  <div class="mega-col col-xs-12 col-sm-12 col-md-4">
                                    <div class="mega-col-inner">
                                      <div class="acme-widget">
										<div class="acme-widget">
                                          <div class="widget-categories">
                                            <div class="widget-inner">
                                              <ul class="list-arrow">
<?php
				if( $dQ = query( "select * from product_dropdown join shopsystem_categories on pd_ca_id = ca_id where pd_ph_id = ".$tr['ph_id']." and pd_column = $col order by pd_sort" ) )
				{
					while( $dr = $dQ->fetchRow() )
					{
/*						ss_log_message( "Category ".$dr['pd_ca_id']." ".$dr['ca_name'] );	*/
?> 												<li>
													<a href="/Shop_System/Service/Engine/OrderBy/Avail.Price/pr_ca_id/<?=$dr['pd_ca_id']?>">
														<span class="title"><?=$dr['ca_name']?></span>
													</a>
                                                </li>
<?php 				}
				}
?>
                                              </ul>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
		<?php
			}
		?>
                                </div>
                              </div>
                            </div>
                          </li>
		<?php
		}
	}

?>
