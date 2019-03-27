<!DOCTYPE html>
<html lang="en" class="loading" ng-app="spa">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Apex admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Apex admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="PIXINVENT">
    <title>SIAP SAJA</title>
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('template/Apex6/app-assets/img/ico/apple-icon-60.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('template/Apex6/app-assets/img/ico/apple-icon-76.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('template/Apex6/app-assets/img/ico/apple-icon-120.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('template/Apex6/app-assets/img/ico/apple-icon-152.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('template/Apex6/app-assets/img/ico/favicon.ico') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('template/Apex6/app-assets/img/ico/favicon-32.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link href="{{ asset('template/Apex6/new assets/css/fonts.css') }}" rel="stylesheet">
    <!-- BEGIN VENDOR CSS-->
    <!-- font icons-->
    <link rel="stylesheet" type="text/css" href="{{ asset('template/Apex6/app-assets/fonts/feather/style.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('template/Apex6/app-assets/fonts/simple-line-icons/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('template/Apex6/app-assets/fonts/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('template/Apex6/app-assets/vendors/css/perfect-scrollbar.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('template/Apex6/app-assets/vendors/css/prism.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('template/Apex6/app-assets/vendors/css/tables/datatable/datatables.min.css') }}">
	<link href="{{ asset('plugins/chosen/chosen.min.css') }}" rel="stylesheet" >
	<link href="{{ asset('plugins/alertify/themes/alertify.core.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/alertify/themes/alertify.default.css') }}" rel="stylesheet" >
	<link href="{{ asset('plugins/jquery-file-upload/css/jquery.fileupload.css') }}" rel="stylesheet" />
    <!-- END VENDOR CSS-->
    <!-- BEGIN APEX CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('template/Apex6/app-assets/css/app.css') }}">
    <!-- END APEX CSS-->
    <!-- BEGIN Page Level CSS-->
    <!-- END Page Level CSS-->
	<style>
		.chosen-container {
			width: 100% !important;
		}
		form .form-body{
			overflow: visible !important;
		}
		td.details-control {
			background: url('template/img/details_open.png') no-repeat center center;
			cursor: pointer;
		}
		tr.shown td.details-control {
			background: url('template/img/details_close.png') no-repeat center center;
		}
	</style>
  </head>
  <body data-col="2-columns" class=" 2-columns ">
    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <div class="wrapper">

      <!-- main menu-->
      <!--.main-menu(class="#{menuColor} #{menuOpenType}", class=(menuShadow == true ? 'menu-shadow' : ''))-->
      <div data-active-color="white" data-background-color="man-of-steel" data-image="{{ asset('template/Apex6/app-assets/img/sidebar-bg/01.jpg') }}" class="app-sidebar">
        <!-- main menu header-->
        <!-- Sidebar Header starts-->
        <div class="sidebar-header">
          <div class="logo clearfix"><a href="index.html" class="logo-text float-left">
          <div class="logo-img"><img src="{{ asset('template/Apex6/app-assets/img/logo.png') }}"/></div><span class="text align-middle">APEX</span></a><a id="sidebarToggle" href="javascript:;" class="nav-toggle d-none d-sm-none d-md-none d-lg-block"><i data-toggle="expanded" class="ft-toggle-right toggle-icon"></i></a><a id="sidebarClose" href="javascript:;" class="nav-close d-block d-md-block d-lg-none d-xl-none"><i class="ft-x"></i></a></div>
        </div>
        <!-- Sidebar Header Ends-->
        <!-- / main menu header-->
        <!-- main menu content-->
        <div class="sidebar-content">
          <div class="nav-container">
            <ul id="main-menu-navigation" data-menu="menu-navigation" class="navigation navigation-main">
			{!! $menu !!}
            </ul>
          </div>
        </div>
        <!-- main menu content-->
        <div class="sidebar-background"></div>
        <!-- main menu footer-->
        <!-- include includes/menu-footer-->
        <!-- main menu footer-->
      </div>
      <!-- / main menu-->


      <!-- Navbar (Header) Starts-->
      <nav class="navbar navbar-expand-lg navbar-light bg-faded header-navbar">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" data-toggle="collapse" class="navbar-toggle d-lg-none float-left"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button><span class="d-lg-none navbar-right navbar-collapse-toggle"><a aria-controls="navbarSupportedContent" href="javascript:;" class="open-navbar-container black"><i class="ft-more-vertical"></i></a></span>
            <form role="search" class="navbar-form navbar-right mt-1">
              <div class="position-relative has-icon-right">
                <input type="text" placeholder="Search" class="form-control round"/>
                <div class="form-control-position"><i class="ft-search"></i></div>
              </div>
            </form>
          </div>
          <div class="navbar-container">
            <div id="navbarSupportedContent" class="collapse navbar-collapse">
              <ul class="navbar-nav">
                <li class="dropdown nav-item">
					<a id="dropdownBasic3" href="#" data-toggle="dropdown" class="nav-link position-relative dropdown-toggle">
						<i class="ft-user font-medium-3 blue-grey darken-4"></i>
						<p class="d-none">User Settings</p>
					</a>
                  <div ngbdropdownmenu="" aria-labelledby="dropdownBasic3" class="dropdown-menu text-left dropdown-menu-right">
					<a ui-sref="profile" class="dropdown-item py-1"><i class="ft-edit mr-2"></i><span>Edit Profile</span></a>
                    <div class="dropdown-divider"></div>
					<a href="auth/logout" class="dropdown-item"><i class="ft-power mr-2"></i><span>Logout</span></a>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </nav>
      <!-- Navbar (Header) Ends-->

      <div class="main-panel">
        <div class="main-content">
          <div class="content-wrapper" ui-view><!--Statistics cards Starts-->
			
		  </div>
        </div>

        <footer class="footer footer-static footer-light">
          <p class="clearfix text-muted text-sm-center px-2"><span>Copyright  &copy; 2019 <a href="https://themeforest.net/user/pixinvent/portfolio?ref=pixinvent" id="pixinventLink" target="_blank" class="text-bold-800 primary darken-2">Tim X Developer </a>, All rights reserved. </span></p>
        </footer>

      </div>
    </div>
    
    <!-- Theme customizer Starts-->
    <div class="customizer border-left-blue-grey border-left-lighten-4 d-none d-sm-none d-md-block"><a class="customizer-close"><i class="ft-x font-medium-3"></i></a><a id="rtl-icon" href="../../fixed-navbar/html-demo-6" target="_blank" class="customizer-toggle bg-dark "><span class="font-medium-1 white align-middle">RTL</span></a><a id="customizer-toggle-icon" class="customizer-toggle bg-danger"><i class="ft-settings font-medium-4 fa fa-spin white align-middle"></i></a>
      <div data-ps-id="df6a5ce4-a175-9172-4402-dabd98fc9c0a" class="customizer-content p-3 ps-container ps-theme-dark">
        <h4 class="text-uppercase mb-0 text-bold-400">Theme Customizer</h4>
        <p>Customize & Preview in Real Time</p>
        <hr>
        <!-- Sidebar Options Starts-->
        <h6 class="text-center text-bold-500 mb-3 text-uppercase">Sidebar Color Options</h6>
        <div class="cz-bg-color">
          <div class="row p-1">
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="pomegranate" class="gradient-pomegranate d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="king-yna" class="gradient-king-yna d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="ibiza-sunset" class="gradient-ibiza-sunset d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="flickr" class="gradient-flickr d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="purple-bliss" class="gradient-purple-bliss d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="man-of-steel" class="gradient-man-of-steel d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="purple-love" class="gradient-purple-love d-block rounded-circle"></span></div>
          </div>
          <div class="row p-1">
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="black" class="bg-black d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="white" class="bg-grey d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="primary" class="bg-primary d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="success" class="bg-success d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="warning" class="bg-warning d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="info" class="bg-info d-block rounded-circle"></span></div>
            <div class="col"><span style="width:20px; height:20px;" data-bg-color="danger" class="bg-danger d-block rounded-circle"></span></div>
          </div>
        </div>
        <!-- Sidebar Options Ends-->
        <hr>
        <!-- Sidebar BG Image Starts-->
        <h6 class="text-center text-bold-500 mb-3 text-uppercase">Sidebar Bg Image</h6>
        <div class="cz-bg-image row">
          <div class="col mb-3"><img src="{{ asset('template/Apex6/app-assets/img/sidebar-bg/01.jpg') }}" width="90" class="rounded"></div>
          <div class="col mb-3"><img src="{{ asset('template/Apex6/app-assets/img/sidebar-bg/02.jpg') }}" width="90" class="rounded"></div>
          <div class="col mb-3"><img src="{{ asset('template/Apex6/app-assets/img/sidebar-bg/03.jpg') }}" width="90" class="rounded"></div>
          <div class="col mb-3"><img src="{{ asset('template/Apex6/app-assets/img/sidebar-bg/04.jpg') }}" width="90" class="rounded"></div>
          <div class="col mb-3"><img src="{{ asset('template/Apex6/app-assets/img/sidebar-bg/05.jpg') }}" width="90" class="rounded"></div>
          <div class="col mb-3"><img src="{{ asset('template/Apex6/app-assets/img/sidebar-bg/06.jpg') }}" width="90" class="rounded"></div>
        </div>
        <!-- Sidebar BG Image Ends-->
        <hr>
        <!-- Sidebar BG Image Toggle Starts-->
        <div class="togglebutton">
          <div class="switch"><span>Sidebar Bg Image</span>
            <div class="float-right">
              <div class="custom-control custom-checkbox mb-2 mr-sm-2 mb-sm-0">
                <input id="sidebar-bg-img" type="checkbox" checked="" class="custom-control-input cz-bg-image-display">
                <label for="sidebar-bg-img" class="custom-control-label"></label>
              </div>
            </div>
          </div>
        </div>
        <!-- Sidebar BG Image Toggle Ends-->
        <hr>
        <!-- Compact Menu Starts-->
        <div class="togglebutton">
          <div class="switch"><span>Compact Menu</span>
            <div class="float-right">
              <div class="custom-control custom-checkbox mb-2 mr-sm-2 mb-sm-0">
                <input id="cz-compact-menu" type="checkbox" class="custom-control-input cz-compact-menu">
                <label for="cz-compact-menu" class="custom-control-label"></label>
              </div>
            </div>
          </div>
        </div>
        <!-- Compact Menu Ends-->
        <hr>
        <!-- Sidebar Width Starts-->
        <div>
          <label for="cz-sidebar-width">Sidebar Width</label>
          <select id="cz-sidebar-width" class="custom-select cz-sidebar-width float-right">
            <option value="small">Small</option>
            <option value="medium" selected="">Medium</option>
            <option value="large">Large</option>
          </select>
        </div>
        <!-- Sidebar Width Ends-->
      </div>
    </div>
    <!-- Theme customizer Ends-->
    <!-- BEGIN VENDOR JS-->
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/core/jquery-3.2.1.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('template/Apex6/src/js/jquery-ui.min.js') }}"></script>
	
	<!-- load angular -->
    <script src="{{ asset('template/angular/angular.min.js') }}"></script>
    <script src="{{ asset('template/angular/angular-ui-router.min.js') }}"></script>
    <script src="{{ asset('template/angular/ngStorage.js') }}"></script>
    <script src="{{ asset('template/angular/loading-bar.js') }}"></script>
	
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/core/popper.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/core/bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/perfect-scrollbar.jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/prism.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/jquery.matchHeight-min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/screenfull.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/pace/pace.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('template/Apex6/app-assets/vendors/js/datatable/datatables.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('plugins/chosen/chosen.jquery.min.js') }}"></script>
	<script src="{{ asset('plugins/alertify/lib/alertify.min.js') }}"></script>
	<script src="{{ asset('plugins/jquery-file-upload/js/jquery.fileupload.js') }}"></script>
	<script src="{{ asset('plugins/jQueryMaskPlugin/src/jquery.mask.js') }}"></script>
    <!-- BEGIN VENDOR JS-->
    <!-- BEGIN PAGE VENDOR JS-->
    <!-- END PAGE VENDOR JS-->
    <!-- BEGIN APEX JS-->
    <script src="{{ asset('template/Apex6/app-assets/js/app-sidebar.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/js/notification-sidebar.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/js/customizer.js') }}" type="text/javascript"></script>
    <!-- END APEX JS-->
	
	<script>
		{!! $angular !!}
	</script>
	
	<script>
        jQuery(document).ready(function(){
			
			jQuery("body").off("keypress",'.val_char').on("keypress",'.val_char',function (e) {
                var charcode = e.which;
                if (
                    (charcode === 8) || // Backspace
                    (charcode === 13) || // Enter
                    (charcode === 127) || // Delete
                    (charcode === 32) || // Space
                    (charcode === 0) || // arrow
                    //(charcode === 188) || // Koma
                    //(charcode === 190) || // Titik
                    //(charcode === 173) || // _
                    //(charcode === 9) || // Horizontal Tab
                    //(charcode === 11) || // Vertical Tab
                    //(charcode >= 37 && charcode <= 40) || // arrow
                    //(charcode >= 48 && charcode <= 57) ||// 0 - 9
                    (charcode >= 65 && charcode <= 90) || // a - z
                    (charcode >= 97 && charcode <= 122) // A - Z
                    ){ 
                    console.log(charcode)
                }
                else {
                    e.preventDefault()
                    return
                }
            }); 

            jQuery("body").off("keypress",'.val_name').on("keypress",'.val_name',function (e) {
                var charcode = e.which;
                if (
                    (charcode === 8) || // Backspace
                    (charcode === 13) || // Enter
                    (charcode === 127) || // Delete
                    (charcode === 32) || // Space
                    (charcode === 0) || // arrow
                    (charcode == 188) || // Koma
                    (charcode == 190) || // Titik
                    //(charcode === 173) || // _
                    //(charcode === 9) || // Horizontal Tab
                    //(charcode === 11) || // Vertical Tab
                    //(charcode >= 37 && charcode <= 40) || // arrow
                    //(charcode >= 48 && charcode <= 57) ||// 0 - 9
                    (charcode >= 65 && charcode <= 90) || // a - z
                    (charcode >= 97 && charcode <= 122) // A - Z
                    ){ 
                    console.log(charcode)
                }
                else {
                    e.preventDefault()
                    return
                }
            }); 

            //hanya alpabet
            jQuery("body").off("keypress",'.val_num').on("keypress",'.val_num',function (e) {
                var charcode = e.which;
                if (
                    (charcode === 8) || // Backspace
                    (charcode === 13) || // Enter
                    (charcode === 127) || // Delete
                    (charcode === 0) || // arrow
                    (charcode >= 48 && charcode <= 57)// 0 - 9
                    ){ 
                    console.log(charcode)
                }
                else {
                    e.preventDefault()
                    return
                }
            });

        });
    </script>
  </body>
</html>