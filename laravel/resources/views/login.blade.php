<!DOCTYPE html>
<html lang="en" class="loading">
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
	<link href="{{ asset('plugins/alertify/themes/alertify.core.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/alertify/themes/alertify.default.css') }}" rel="stylesheet" >
    <!-- END VENDOR CSS-->
    <!-- BEGIN APEX CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('template/Apex6/app-assets/css/app.css') }}">
    <!-- END APEX CSS-->
    <!-- BEGIN Page Level CSS-->
    <!-- END Page Level CSS-->
	
    <script>
        if(self==top){
            document.documentElement.style.visibility='visible';
        }else{
            top.location=self.location;
        }
    </script>
	
  </head>
  <body data-col="1-column" class=" 1-column  blank-page blank-page">
    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <div class="wrapper">
      <div class="main-panel">
        <div class="main-content">
          <div class="content-wrapper"><!--Login Page Starts-->
			<section id="login">
				<div class="container-fluid">
					<div class="row full-height-vh">
						<div class="col-12 d-flex align-items-center justify-content-center">
							<div class="card gradient-indigo-purple text-center width-400">
								<div class="card-img overlap">
									<img alt="element 06" class="mb-1" src="{{ asset('template/Apex6/app-assets/img/portrait/avatars/avatar-08.png') }}" width="190">
								</div>
								<div class="card-body">
									<div class="card-block">
										<h2 class="white">Login</h2>
										<form id="form-ruh" name="form-ruh" onsubmit="return false">
											<input type="hidden" name="_token" value="{{ csrf_token() }}" />
											<div class="form-group">
												<div class="col-md-12">
													<input type="text" class="form-control" name="username" id="username" placeholder="Username" required >
												</div>
											</div>

											<div class="form-group">
												<div class="col-md-12">
													<input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
												</div>
											</div>
											
											<div class="form-group">
												<div class="col-md-12">
													<select class="form-control" name="tahun" id="tahun" required>
													{!!$tahun!!}
													</select>
												</div>
											</div>

											<div class="form-group">
												<div class="col-md-12">
													<button id="submit" type="submit" class="btn btn-warning btn-block btn-raised">Submit</button>
												</div>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!--Login Page Ends-->
          </div>
        </div>
      </div>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->

    <!-- BEGIN VENDOR JS-->
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/core/jquery-3.2.1.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/core/popper.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/core/bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/perfect-scrollbar.jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/prism.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/jquery.matchHeight-min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/screenfull.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/vendors/js/pace/pace.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('plugins/alertify/lib/alertify.min.js') }}"></script>
    <!-- BEGIN VENDOR JS-->
    <!-- BEGIN PAGE VENDOR JS-->
    <!-- END PAGE VENDOR JS-->
    <!-- BEGIN APEX JS-->
    <script src="{{ asset('template/Apex6/app-assets/js/app-sidebar.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/js/notification-sidebar.js') }}" type="text/javascript"></script>
    <script src="{{ asset('template/Apex6/app-assets/js/customizer.js') }}" type="text/javascript"></script>
    <!-- END APEX JS-->
    <!-- BEGIN PAGE LEVEL JS-->
    <!-- END PAGE LEVEL JS-->
	<script type="text/javascript">
            
        jQuery(document).ready(function(){
        
            function doBounce(element, times, distance, speed) {
                for(i = 0; i < times; i++) {
                    element.animate({marginTop: '-='+distance},speed)
                        .animate({marginTop: '+='+distance},speed);
                }        
            }
            
            setTimeout(function(){
                jQuery("#username").focus();
            },1000);
            
            //login         
            jQuery('#submit').click(function(){
            
                //bouncing for awhile...
                doBounce(jQuery('#form-box'), 3, '10px', 100);
            
                jQuery(this).prop('disabled',true);
                jQuery(this).html('<span class="loading">Loading.....</span>');
                var lanjut=true;
                if(jQuery('#username').val()==''){
                    lanjut=false;
                }
                if(jQuery('#password').val()==''){
                    lanjut=false;
                }
				if(jQuery('#tahun').val()==''){
                    lanjut=false;
                }
                if(lanjut==true){
                    var url="auth";
                    var data=jQuery('#form-ruh').serialize();
                    jQuery.ajax({
                        url:'auth',
                        data:data,
                        method:'POST',
                        success:function(result){
                            if(result.error==false){
                                alertify.log('Login berhasil.<br>Selamat datang!');
                                jQuery('#submit').html('Submit');
                                jQuery('#submit').prop('disabled', false);
                                window.location.href='./';
                            }
                            else{
                                alertify.log(result.message);
                                jQuery('#submit').html('Submit');
                                jQuery('#submit').prop('disabled', false);
                            }
                        },
                        error:function(result){
                            alertify.log('Sesi telah habis. Silahkan refresh halaman browser Anda.');
                            jQuery('#submit').html('Submit');
                            jQuery('#submit').prop('disabled', false);
                        }
                    });
                }
                else{
                    alertify.log('Kolom username/password tidak dapat dikosongkan!');
                    jQuery('#submit').html('Submit');
                    jQuery('#submit').prop('disabled', false);
                }
            });
        });

    </script>
	
  </body>
</html>