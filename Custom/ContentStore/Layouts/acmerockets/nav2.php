                    <nav id="acme-megamenu" class="navbar navbar-default hidden-xs hidden-sm">
                      <div class="collapse navbar-collapse" id="bs-megamenu">
                        <ul class="nav navbar-nav megamenu">
                            <?php $menu = new Request('Asset.Display',array(
                                        'as_id'    =>    '514',
                                        'Service'    =>    'TopLevelCategoryList',
                                    ));
                                  print $menu->display;
                            ?>
													<li class="parent dropdown aligned-left">
														<a class="dropdown-toggle" data-toggle="dropdown" href="/Shop_System/Service/Engine/Specials/1">
															<span class="menu-title">Sale</span>
														</a>
                            <div class="dropdown-menu level1" style="width:300px">
                              <div class="dropdown-menu-inner">
                                <div class="row">
                                  <div class="mega-col col-xs-12 col-sm-12 col-md-12">
                                    <div class="mega-col-inner">
                                      <div class="acme-widget">
																				<div class="acme-widget">
                                          <div class="widget-categories">
                                            <h6 class="widget-heading">Sales</h6>
                                            <div class="widget-inner">
                                              <ul class="list-arrow">
																								<li>
																									<a href="/Shop_System/Service/Engine/Specials/1">
																										<span class="title">All on sale</span>
																									</a>
                                                </li>																								<li>
																									<a href="/Shop_System/Service/Engine/Gateway/32">
																										<span class="title">Bitcoin only, on sale</span>
																									</a>
                                                </li>
																								<li>
																									<a href="/Shop_System/Service/Engine/Specials/1/pr_ve_id/1">
																										<span class="title">Humidors and Accessories on sale</span>
																									</a>
                                                </li>
                                              </ul>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </li>
													<li class=""> 
														<a href="Acme_Rockets/Faq_And_Shipping_Info"><span class="menu-title">FAQ & Shipping</span></a>
                          </li>

                          <li class=""> 
														<a href="http://blog.acmerockets.com"><span class="menu-title">Blog</span></a>
                          </li>

                          <li class=""> <a href="/Members/Service/Issue">
														<span class="menu-title">Contact</span></a>
													</li>
                        </ul>
                      </div>
                    </nav>

