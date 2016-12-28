
	<h1 class="_title_dash">Dashboard</h1>
	<!--html5 desing-->
	<div>
	<section class="_menu_items_metro">
		<ul>
			<li class="color1">
				<div class="_menu_dashicon">
					<span class="dashicons dashicons-format-aside"></span>
				</div>
				<div class="_descripcion_items_metro">
					<p>Items1</p>
				</div>
			</li>
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

				</ul>
			</div>

		</div>

		<!--right-->
		<div class="right_dash"></div>
	</section>
	</div>
	<div style="clear: both;"></div>



	<!--styles-->
	<!--desing que colocaremos en un ECHO desps-->
	<!--design dashboard fakturo-->
	<style type="text/css">
		.color1{background-color: #27A9E3 !important;}
		.color2{background-color: #28B779 !important;}
		.color3{background-color:#FFB848 !important; }
		.color4{background-color:#DA542E !important; }
		.color5{background-color:#2255A4 !important; }
		._title_dash{margin-left: 20px;}
		._menu_items_metro:after{clear: both;}
		._menu_items_metro ul:after{clear: both;}
		._menu_items_metro ul li{
			position: relative;
			width: 150px; 
			height: 150px;
			display: inline-block;
			background-color: #ccc;
			margin-left: 10px;
		}
		._menu_dashicon{
			 position: absolute; /* or absolute */
  			 top: 50%;
  			 left: 50%;
  			 transform: translate(-50%, -50%);
  			 width: 50px;
  			 height: 50px;
		}
		._menu_dashicon span{
			font-size: 50px;
			color: white;
		}
		._descripcion_items_metro{
			position: absolute;
			bottom: 0;
			width: 100%;
			text-align: center;
		}
		._descripcion_items_metro p{
			color: white; 
			font-size: 16px;
		}

		/*section widgets Frontend*/
		.element_widget_dashboard:after{clear: both;}
		.left_dash{
			float: left;
			width: 50%;
			height: 200px;
			margin-left: 10px;
		}
		.seccion1 h3{
			padding: 10px 10px;
			background-color: #D9D9D9 !important;
			border:1px solid #BEBEBE !important;
			color: #435156;
			font-weight: bold;
		}
		
		.seccion1 ul{
			background-color: white;
			border:1px solid #CBD4DB;
			margin-top: -20px !important;
		}
		
		.seccion1 ul li{
			border-bottom: 1px solid #CBD4DB;
			margin-top: 0px !important;
			padding: 10px;
		}
		.seccion1 ul li:last-child{
			border-bottom: none;
		}
		.seccion1 ul li span{
			font-size: 20px;
			padding: 5px;
			background-color: #DBD3CA;
			color: #515D60;
			float: left;
			width: 5%;
			margin-top: 10px;
			
		}
		.seccion1 ul li p{
			padding: 0px 10px;
			float: left;
			width: 85%;
			padding-left: 10px;
			margin-top: 5px;
		}
		.seccion1 ul li p:after{
			clear: both !important;
		}
	</style>