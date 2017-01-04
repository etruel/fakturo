
	<h1 class="_title_dash">Dashboard</h1>
	<!--html5 desing-->
	<div>
		
	<section class="_menu_items_metro">
		<ul>
			<a href="#">
			<li class="color1">
				<div class="_menu_dashicon">
					<span class="dashicons dashicons-format-aside"></span>
				</div>
				<div class="_descripcion_items_metro">
					<p>Items1</p>
				</div>
			</li>
			</a>
			<li class="color2">
				<div class="_menu_dashicon">
					<span class="dashicons dashicons-format-image"></span>
				</div>
				<div class="_descripcion_items_metro">
					<p>Items2</p>
				</div>
			</li>
			<li class="color3">
				<div class="_menu_dashicon">
					<span class="dashicons dashicons-admin-appearance"></span>
				</div>
				<div class="_descripcion_items_metro">
					<p>Items3</p>
				</div>
			</li>
			<li class="color4">
				<div class="_menu_dashicon">
					<span class="dashicons dashicons-admin-settings"></span>
				</div>
				<div class="_descripcion_items_metro">
					<p>Items3</p>
				</div>
			</li>
			<li class="color5">
				<div class="_menu_dashicon">
					<span class="dashicons dashicons-image-filter"></span>
				</div>
				<div class="_descripcion_items_metro">
					<p>Items3</p>
				</div>
			</li>
			<li class="colordefault">
				<div class="_menu_dashicon">
					<span class="dashicons dashicons-admin-settings"></span>
				</div>
				<div class="_descripcion_items_metro">
					<p>Items3</p>
				</div>
			</li>
			<li>
				<div class="_menu_dashicon">
					<span class="dashicons dashicons-admin-settings"></span>
				</div>
				<div class="_descripcion_items_metro">
					<p>Items6</p>
				</div>
			</li>
		</ul>
	</section>


	<!--section widgets front-end-->
	<section class="element_widget_dashboard">
		<!--left-->
		<div class="left_dash">
			<!--seccion1-->
			<div class="seccion1">
				<h3 class="border:2px solid red !important;">Latest Post</h3>
				<ul>
					<li>
						<span class="dashicons dashicons-admin-settings"></span>
						<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
						tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
						quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
						consequat...</p>
						<div style="clear: both;"></div>
					</li>
					<li>
						<span class="dashicons dashicons-admin-settings"></span>
						<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
						tempor incididunt ut labore et dolore magna aliqua.</p>
						<div style="clear: both;"></div>
					</li>
					<li>
						<span class="dashicons dashicons-admin-settings"></span>
						<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
						tempor incididunt ut labore et dolore magna aliqua.</p>
						<div style="clear: both;"></div>

					</li>
					<li>
						<span class="dashicons dashicons-admin-settings"></span>
						<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
						tempor incididunt ut labore et dolore magna aliqua.</p>
						<div style="clear: both;"></div>
						
					</li>
					<li>
						<span class="dashicons dashicons-admin-settings"></span>
						<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
						tempor incididunt ut labore et dolore magna aliqua.</p>
						<div style="clear: both;"></div>
						
					</li>
					<li>
						<span class="dashicons dashicons-admin-settings"></span>
						<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
						tempor incididunt ut labore et dolore magna aliqua.</p>
						<div style="clear: both;"></div>
						
					</li>
					<li>
						<span class="dashicons dashicons-admin-settings"></span>
						<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
						tempor incididunt ut labore et dolore magna aliqua.</p>
						<div style="clear: both;"></div>
						
					</li>
				</ul>
				<div style="clear: both;"></div>
			</div>
			<div style="clear: both;"></div>
		</div>




		<!--right-->
		<div class="right_dash">
			<div class="seccion1" class="white">
				<h3>Fakturo - Sales Summary</h3>
				<table  width="100%" cellspacing="0" style="border:1px solid #CBD4DB; padding:10px;">
					<tr>
						<td>
							<p>Current Mothn</p>
							<ol>
								<li><span class="sp_left">Earning</span>  <span class="sp_right"><?php echo $money_format_current_month; ?></span><br style="clear: both;"></li>
								<li><span class="sp_left">Sales</span> <span class="sp_right"><?php echo $count_sales_current_month; ?></span></li>
							</ol>
						</td>
						<td>
							<p>Today</p>
							<ol>
								<li><span class="sp_left">Earning</span> <span class="sp_right"><?php echo $money_format_today; ?></span><br style="clear: both;"></li>
								<li><span class="sp_left">Sales</span> <span class="sp_right"><?php echo $count_sales_today; ?></span></li>
							</ol>
						</td>
					</tr>
					<tr>
						<td>
							<p>Last Mothn</p>
							<ol>
								<li><span class="sp_left">Earning</span> <span class="sp_right"><?php echo $money_format_last_month; ?></span><br style="clear: both;"></li>
								<li><span class="sp_left">Sales</span> <span class="sp_right"><?php echo $count_sales_last_month; ?></span></li>
							</ol>
						</td>
						<td>
							<p>Total</p>
							<ol>
								<li><span class="sp_left">Earning</span> <span class="sp_right"><?php echo $money_format_total; ?></span><br style="clear: both;"></li>
								<li><span class="sp_left">Sales</span> <span class="sp_right"><?php echo $count_sales_total; ?></span></li>
							</ol>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div style="clear: both !important;"></div>
	</section>
	</div>


