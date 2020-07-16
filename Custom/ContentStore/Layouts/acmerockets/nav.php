        <nav id="topbar" class="topbar topbar-v1">
          <div class="container">
            <div class="inner">
              <div class="row">
                  <div class="col-md-3 col-sm-3 col-xs-6 col-lg-3">
                  <div class="quick-currency pull-left">
                    <div>
                      <?php include("Custom/ContentStore/Layouts/acmerockets/country.php") ?>
                    </div>
                  </div>
                </div>
				<?php
					if( file_exists( 'images/flag_ribbon.png' ) )
					{
						echo '<div class="col-md-1 col-sm-1 col-xs-1 col-lg-1">';
						echo '<img  src="/images/flag_ribbon.png">';
						echo '</div>';
						echo '<div class="col-md-2 col-sm-4 col-xs-7 col-lg-2">';
					}
					else
						echo '<div class="col-md-3 col-sm-5 col-xs-8 col-lg-3">';
				?>
                  <div class="quick-currency pull-left">
                    <div class="badge">
                      In purchasing you will confirm you are over 18 years old.
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6 col-lg-6">
                  <ul class="list-inline text-right">
                    <li>
                    <?php
                        if( array_key_exists('User', $_SESSION)
                         &&  array_key_exists('us_email', $_SESSION['User'])
                         && strlen($_SESSION['User']['us_email']) )
						{
						?>
						<a href='/Members' title="My Members Account Page"><span>My Account</span></a>
						<a class="btn" data-placement="top" href="/Members/Service/Paid" title="My Orders">
						  <i class="zmdi zmdi-airplane zmdi-hc-lg"></i>
						</a>
						<?php if( array_key_exists('NewMessage', $_SESSION) ) { ?>
						<a class="btn" data-placement="top" href="/Members/Service/Messages#New" title="You have a new message">
						  <i class="zmdi zmdi-email zmdi-hc-lg animated infinite wobble zmdi-hc-fw"></i>
						<?php } else { ?>
						<a class="btn" data-placement="top" href="/Members/Service/Messages" title="My Messages">
						  <i class="zmdi zmdi-email zmdi-hc-lg"></i>
						<?php } ?>
						</a>
						<?php
						}
                        else
                            echo "<a href='Members'><span>Login</span></a>";
                      ?>
                    </li>
                    <li>
                      <form action="/Search?Stats=Yes" method="post" name="form1" id="form1" class="form-group" >
                          <input name="AST_SEARCH_KEYWORDS" type="text" class="form-control" value="search" 
															onfocus="this.value='';" onblur="if (this.value.length==0) this.value='search';" />
                      </form>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </nav>
