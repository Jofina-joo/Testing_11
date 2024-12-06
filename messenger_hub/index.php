<?php
/*
This page is used to authendicate the user.
Every valid user can login here to access their
role based services.

Version : 1.0
Author : Arun Rama Balan.G (YJ0005)
Date : 06-Jul-2023
*/

$subdomain = join('.', explode('.', $_SERVER['HTTP_HOST'], -2));
$has_subdomain = 0;
if ($subdomain == 'yourpostman' or $subdomain == 'whatsapp') {
  $has_subdomain = 1;
}
// echo "--".$subdomain."--".$has_subdomain."--";

session_start(); // To start session
error_reporting(0); // The error reporting function

// Include configuration.php
include_once ('api/configuration.php');

// To find what is the previous page link and redirect to that link
$newPageName = substr($_SERVER["HTTP_REFERER"], strrpos($_SERVER["HTTP_REFERER"], "/") + 1);
if ($_SERVER['HTTP_REFERER'] == '' or $newPageName == 'index' or $newPageName == 'logout' or $newPageName == 'dashboard') {
  $server_http_referer = $site_url . "dashboard";
} elseif ($_SERVER['HTTP_REFERER'] == $site_url or $newPageName == 'index.php') {
  $server_http_referer = $site_url . "dashboard";
} else {
  $server_http_referer = $_SERVER['HTTP_REFERER'];
}

// If Session available user try to access this page, then it will redirect to Logout page
if ($_SESSION['yjtsms_user_id'] != "") { ?>
  <script>window.location = "logout";</script>
  <?php exit();
}
site_log_generate("Index Page : Unknown User : '" . $_SESSION['yjtsms_user_id'] . "' access this page on " . $current_date); // Log file
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Login -
    <?= $site_title ?>
  </title>
  <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="assets/modules/bootstrap-social/bootstrap-social.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <style>
    .progress {
      height: 0.3rem !important;
    }

    .progress-bar {
      background-color: green;
    }

    .theme-loader {
      display: block;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 100;
      width: 100%;
      height: 100%;
      background-color: rgba(192, 192, 192, 0.5);
      background-image: url("assets/img/loader.gif");
      background-repeat: no-repeat;
      background-position: center;

    }

    .login-brand {
      margin-bottom: 65px;
    }

    .text-bold {
      font-weight: bold;
    }
  </style>

<body style="background: url(assets/img/background_img.jpg); background-repeat: no-repeat; background-size: cover;">
  <div class="theme-loader" style="display:none;"> </div>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <!-- <div class="row"> -->
        <div class="col-sm-12 offset-sm-0 col-md-6 offset-md-3">
          <div class="login-brand">
            <? if ($has_subdomain == 1) {
              echo "Whatsapp Bulk Messenger";
            } else { ?><img src="assets/img/cm-logo.png"
                alt="logo" style="width: 100%"> <!-- Logo --><? } ?>
          </div>
          <!-- Signin -->
          <div class="card card-success" id="tab_signin" style="border-top: 0px solid #f27878 !important;">
            <div class="card-body">
              <div class="col-sm-12">
                <form class="md-float-material form-material" action="#" name="frm_login" id='frm_login' method="post">
                  <div>
                    <div class="row m-b-20">
                      <div class="col-md-12">
                        <h3 class="text-center"><i class="icofont icofont-sign-in"></i> Sign In</h3>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-4 text-bold">
                        Login ID <label style="color:#FF0000">*</label>
                      </div>
                      <div class="col-8">
                        <input type="text" name="txt_username" id="txt_username" class="form-control" value=""
                          maxlength="20" tabindex="1" autofocus="" required="" data-toggle="tooltip"
                          pattern="[a-zA-Z0-9 -_]+" data-placement="top" title="" data-original-title="Login ID"
                          placeholder="Login ID" onkeypress="return clsAlphaNoOnly(event)">
                        <!-- Login ID -->
                      </div>
                    </div>

                    <div class="row mt-2">
                      <div class="col-4 text-bold">
                        Login Password <label style="color:#FF0000">*</label>
                      </div>
                      <div class="col-8">
                        <div class="input-group" title="Visible Password">
                          <input type="password" name="txt_password" id='txt_password' class="form-control" value=""
                            maxlength="100" tabindex="2" required="" data-toggle="tooltip" data-placement="top" title=""
                            data-original-title="Login Password" placeholder="Login Password">
                          <div class="input-group-prepend">
                            <div class="input-group-text" onclick="password_visible()"
                              id="id_signup_display_visiblitity"><i class="fas fa-eye-slash"></i>
                            </div>
                          </div>
                          <!-- Password -->
                        </div>
                      </div>
                    </div>


                    <div class="row m-t-30">
                      <div class="col-md-4"></div>
                    </div>

                    <div class="row  mt-4">
                      <!-- Error Display -->
                      <div class="col-md-12 text-center">
                        <span class="error_display" id='id_error_display_signin'></span>
                      </div>
                      <!-- Error Display -->
                      <div class="col-md-4"></div>
                      <div class="col-md-4">
                        <input type="hidden" class="form-control" name='call_function' id='call_function'
                          value='signin' /> <!-- Process Name -->
                        <input type="hidden" class="form-control" name='hid_sendurl' id='hid_sendurl'
                          value='<?= $server_http_referer ?>' /> <!-- Redirect Link -->
                        <input type="submit" name="submit" id="submit" tabindex="3" value="Sign in"
                          class="btn btn-success btn-md btn-block waves-effect waves-light text-center m-b-20">
                        <!-- Submit Button -->
                      </div>
                      <div class="col-md-4"></div>
                    </div>

                    <!-- <div class="row m-t-1">
                      <div class=" text-left"><a class="nav-link text-bold" data-toggle="tab" href="#tab_signup"
                          onclick="func_open_tab_signin()" role="tab"> New Users : Sign up</a></div>
                    </div> -->

                    <div class="row m-t-1">
                      <div class=" text-left col-md-6"><a class="nav-link text-bold" data-toggle="tab"
                          href="#tab_signup" onclick="func_open_tab_signin()" role="tab"> New Users : Sign up</a></div>
                      <div class=" text-right col-md-6"><a class="nav-link text-bold" data-toggle="tab"
                          href="#tab_forgot_pass" onclick="func_open_tab_forgot()" role="tab"> Forgot Password</a></div>
                    </div>


                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- Signin -->


          <!-- Signup -->
          <div class="offset-sm-0" id="tab_signup" style="display:none;">
            <div class="card card-success col-sm-12 " style="border-top: 0px solid #f27878 !important;">

              <div class="card-body tab_signup">
                <form class="needs-validation" novalidate action="#" name="frm_signup" id='frm_signup'
                  enctype="multipart/form-data" method="post">
                  <div>
                    <div class="row m-b-20">
                      <div class="col-md-12">
                        <h3 class="text-center"><i class="icofont icofont-sign-in"></i> Sign Up</h3>
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-4 label text-bold">
                        User Name<label style="color:#FF0000">*</label>
                      </div>
                      <div class="col-8">
                        <input type="text" name="clientname_txt" id="clientname_txt" class="form-control" value=""
                          maxlength="30" tabindex="1" autofocus="" required="" data-toggle="tooltip"
                          data-placement="top" title="" data-original-title="User Name" placeholder="User Name"
                          pattern="[a-zA-Z0-9 -_]+" onkeypress="return clsAlphaNoOnly(event)" onpaste="return false;">
                      </div>
                    </div>

                    <div class="row mt-2">
                      <div class="col-4 label text-bold">
                        User Mobile<label style="color:#FF0000">*</label>
                      </div>
                      <div class="col-8">
                        <input type="text" name="mobile_no_txt" id="mobile_no_txt" class="form-control" value=""
                          maxlength="10" tabindex="4" autofocus="" required="" data-toggle="tooltip"
                          data-placement="top" title="" data-original-title="User Mobile" placeholder="User Mobile"
                          onkeypress="return (event.charCode !=8 && event.charCode ==0 ||  (event.charCode >= 48 && event.charCode <= 57))">
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-4 label text-bold">
                        User Email ID<label style="color:#FF0000">*</label>
                      </div>
                      <div class="col-8">
                        <input type="text" name="email_id_contact" id="email_id_contact" class="form-control" value=""
                          maxlength="50" tabindex="5" autofocus="" required="" data-toggle="tooltip"
                          data-placement="top" title="" data-original-title="User Email ID" placeholder="User Email ID">
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-4 label text-bold">
                        Login ID<label style="color:#FF0000">*</label>
                      </div>
                      <div class="col-8">
                        <input type="text" name="login_id_txt" id="login_id_txt" class="form-control" value=""
                          maxlength="20" tabindex="1" autofocus="" required="" data-toggle="tooltip"
                          data-placement="top" title="" data-original-title="Login ID" placeholder="Login ID"
                          pattern="[a-zA-Z0-9 -_]+" onkeypress="return clsAlphaNoOnly(event)" onpaste="return false;">
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-4 label text-bold">
                        Login Password<label style="color:#FF0000">*</label>
                      </div>
                      <div class="col-8">
                        <div class="input-group" title="Visible Signup Password">
                          <input type="password" name="txt_user_password" id="txt_user_password" class="form-control"
                            maxlength="100" value="" tabindex="10" required="" data-toggle="tooltip"
                            data-placement="top" title=""
                            data-original-title="Login Password -  [Atleast 8 characters and Must Contains Numeric, Capital Letters and Special characters]"
                            placeholder="Login Password -  [Atleast 8 characters and Must Contains Numeric, Capital Letters and Special characters]"
                            onblur="return checkPasswordStrength()">
                          <div class="input-group-prepend">
                            <div class="input-group-text" onclick="password_visible1()" id="display_visiblitity"><i
                                class="fas fa-eye-slash"></i>
                            </div>
                          </div>
                        </div>


                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-4 label text-bold">
                        Confirm Password<label style="color:#FF0000">*</label>
                      </div>
                      <div class="col-8">
                        <div class="input-group" title="Visible conform Password">
                          <input type="password" name="txt_confirm_password" id="txt_confirm_password"
                            class="form-control" maxlength="100" value="" tabindex="11" required=""
                            data-toggle="tooltip" data-placement="top" title="" data-original-title="Confirm Password"
                            placeholder="Confirm Password">
                          <div class="input-group-prepend">
                            <div class="input-group-text" onclick="password_visible2()" id="display_visiblitity_1"><i
                                class="fas fa-eye-slash"></i>
                            </div>
                          </div>

                        </div>
                      </div>
                      <!-- <br>   -->
                      <div class="row m-t-10 text-left" style="margin-top: 10px;">
                        <div class="col-md-12">
                          <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0"
                              aria-valuemax="100" style="width: 0%; background: rgb(255, 0, 0);" data-toggle="tooltip"
                              data-placement="top" title="" data-original-title="Password Strength Meter"
                              placeholder="Password Strength Meter">
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row mt-2">
                        <div class="col-12">
                          <input type="checkbox" name="chk_terms" id="chk_terms" value="" tabindex="29">
                          <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                          <span class="text-inverse" style="color:#FF0000 !important">I read and accept <a
                              href="javascript:void(0)" style="color:#FF0000 !important" data-toggle="tooltip"
                              data-placement="top" title="" data-original-title="Terms & Conditions."
                              class="alert-ajax btn-outline-info">Terms &amp; Conditions.</a></span>
                        </div>
                      </div>
                    </div>

                    <div class="row m-t-30">
                      <div class="col-md-12" style="text-align:center;">
                        <span class="error_display text-center" id='id_error_display_onboarding'></span>&nbsp;
                      </div>
                      <div class="col-md-4"></div>
                    </div>

                    <div class="row  m-t-30">
                      <div class="col-md-12" style="text-align:center">

                        <input type="submit" name="submit_signup" id="submit_signup"
                          style="width:150px;margin-left:auto;margin-right:auto" tabindex="30" value="Sign Up Now"
                          class="btn btn-success btn-md btn-block waves-effect waves-light text-center ">
                      </div>
                      <div class="col-md-4"></div>
                    </div>
                    <!-- <div class="row m-t-1">
                      <div class="text-left"><a class="nav-link text-bold" data-toggle="tab" href="#tab_signin"
                          onclick="func_open_tab_signup()" role="tab">Sign In</a></div>
                    </div> -->
                    <div class="row m-t-1">
                      <div class="text-left col-md-6"><a class="nav-link text-bold" data-toggle="tab" href="#tab_signin"
                          onclick="func_open_tab_signup()" role="tab">Sign In</a></div>
                      <div class=" text-right col-md-6"><a class="nav-link text-bold" data-toggle="tab"
                          href="#tab_forgot_pass" onclick="func_open_tab_forgot()" role="tab"> Forgot Password</a></div>
                    </div>

                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- Signup -->


          <!-- Forgot Password -->
          <div class="offset-sm-0" id="tab_forgot_pass" style="display:none;">
            <div class="card card-success col-sm-12 " style="border-top: 0px solid #f27878 !important;">

              <div class="card-body tab_signup">
                <form class="needs-validation" novalidate action="#" name="frm_forgotpass" id='frm_forgotpass'
                  enctype="multipart/form-data" method="post">
                  <div>
                    <div class="row m-b-20">
                      <div class="col-md-12">
                        <h3 class="text-center"><i class="icofont icofont-sign-in"></i> Forgot Password</h3>
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-4 label text-bold">
                        User Email ID <label style="color:#FF0000">*</label>
                      </div>
                      <div class="col-8">
                        <input type="text" name="email_id_reset" id="email_id_reset" class="form-control" value=""
                          maxlength="50" tabindex="5" autofocus="" required="" data-toggle="tooltip"
                          data-placement="top" title="" data-original-title="User Email ID" placeholder="User Email ID">
                      </div>
                    </div>

                    <div class="row m-t-30">
                      <div class="col-md-12" style="text-align:center;">
                        <span class="error_display text-center" id='id_error_forgotpass'></span>&nbsp;
                      </div>
                      <div class="col-md-4"></div>
                    </div>

                    <div class="row  m-t-30">
                      <div class="col-md-12" style="text-align:center">

                        <input type="submit" name="submit_forgot" id="submit_forgot"
                          style="width:150px;margin-left:auto;margin-right:auto" tabindex="30" value="Reset Password"
                          class="btn btn-success btn-md btn-block waves-effect waves-light text-center ">
                      </div>
                      <div class="col-md-4"></div>
                    </div>
                    <div class="row m-t-1">
                      <div class="text-left col-md-6"><a class="nav-link text-bold" data-toggle="tab" href="#tab_signin"
                          onclick="func_open_tab_signup()" role="tab">Sign In</a></div>
                      <div class=" text-right col-md-6"><a class="nav-link text-bold" data-toggle="tab"
                          href="#tab_signup" onclick="func_open_tab_signin()" role="tab"> New Users : Sign up</a></div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>


          <!-- Forgot Password -->

        </div>
    </section>
  </div>

  <!-- Footer Panel -->
  <div class="simple-footer text-white" style="margin-top: 200px;">
    Copyright &copy;
    <?= $has_subdomain == 1 ? "Whatsapp Bulk Messenger" : $site_title ?> -
    <?= date("Y") ?>
  </div>
  </div>
  </div>

  <!-- Modal content-->
  <div class="modal fade" id="default-Modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Terms & Conditions</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="id_modal_display">
          <h5>Welcome</h5>
          <p>Waiting for load Data..</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success waves-effect " data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- General JS Scripts -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/tooltip.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>

  <!-- JS Libraies -->
  <!-- Page Specific JS File -->
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    // To Submit the Form
    document.body.addEventListener("click", function (evt) {
      //note evt.target can be a nested element, not the body element, resulting in misfires
      $("#id_error_display_signin").html("");
    });

    $("option").each(function () {
      var $this = $(this);
      $this.text($this.text().charAt(0).toUpperCase() + $this.text().slice(1));
    });

    $(".alert-ajax").click(function () {
      $("#id_modal_display").load("uploads/imports/terms.htm", function () {
        $('#default-Modal').modal({ show: true });
      });
    });

    // function func_open_tab_signin() {
    //   $('#tab_signup').css("display", "block");
    //   $('#tab_signin').css("display", "none");
    //   // login clear
    //   $('#txt_user_password').css('border-color', '');
    //   $('#txt_username').val("");
    //   $('#txt_password').val("");
    // }
    // function func_open_tab_signup() {
    //   $('#tab_signin').css("display", "block");
    //   $('#tab_signup').css("display", "none");
    //   // signup clear 
    //   $('#clientname_txt').val('');
    //   $('#login_id_txt').val('');
    //   $('#mobile_no_txt').val('');
    //   $('#email_id_contact').val('');
    //   $('#txt_user_password').val('');
    //   $('#txt_confirm_password').val('');
    // }

    function func_open_tab_signin() {
      $('#tab_signup').css("display", "block");
      $('#tab_signin').css("display", "none");
      $('#tab_forgot_pass').css("display", "none");

      // login clear
      $('#txt_user_password').css('border-color', '');
      $('#txt_username').val("");
      $('#txt_password').val("");
    }

    function func_open_tab_signup() {
      $('#tab_signin').css("display", "block");
      $('#tab_signup').css("display", "none");
      $('#tab_forgot_pass').css("display", "none");
      // signup clear 
      $('#clientname_txt').val('');
      $('#login_id_txt').val('');
      $('#mobile_no_txt').val('');
      $('#email_id_contact').val('');
      $('#txt_user_password').val('');
      $('#txt_confirm_password').val('');
    }


    function func_open_tab_forgot() {

      $('#tab_forgot_pass').css("display", "block");
      $('#tab_signin').css("display", "none");
      $('#tab_signup').css("display", "none");
      // signup clear 
      $('#clientname_txt').val('');
      $('#login_id_txt').val('');
      $('#mobile_no_txt').val('');
      $('#email_id_contact').val('');
      $('#txt_user_password').val('');
      $('#txt_confirm_password').val('');
    }

    function password_visible1() {
      var x = document.getElementById("txt_user_password");
      if (x.type === "password") {
        x.type = "text";
        $('#display_visiblitity').html('<i class="fas fa-eye"></i>');
      } else {
        x.type = "password";
        $('#display_visiblitity').html('<i class="fas fa-eye-slash"></i>');
      }
    }

    function password_visible2() {
      var x = document.getElementById("txt_confirm_password");
      if (x.type === "password") {
        x.type = "text";
        $('#display_visiblitity_1').html('<i class="fas fa-eye"></i>');
      } else {
        x.type = "password";
        $('#display_visiblitity_1').html('<i class="fas fa-eye-slash"></i>');
      }
    }

    function password_visible() {
      var x = document.getElementById("txt_password");
      if (x.type === "password") {
        x.type = "text";
        $('#id_signup_display_visiblitity').html('<i class="fas fa-eye"></i>');
      } else {
        x.type = "password";
        $('#id_signup_display_visiblitity').html('<i class="fas fa-eye-slash"></i>');
      }
    }


    $("#submit").click(function (e) {
      $("#id_error_display_signin").html("");
      var uname = $('#txt_username').val();
      var password = $('#txt_password').val();
      var flag = true;
      /********validate all our form fields***********/
      /* Name field validation  */
      if (uname == "") {
        $('#txt_username').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      }
      /* password field validation  */
      if (password == "") {
        $('#txt_password').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      } else {
      }
      /********Validation end here ****/

      /* If all are ok then we send ajax request to process_connect.php *******/
      if (flag) {
        var data_serialize = $("#frm_login").serialize();
        $.ajax({
          type: 'post',
          url: "ajax/call_functions.php",
          dataType: 'json',
          data: data_serialize,
          beforeSend: function () { // Before Send to Ajax
            $('#submit').attr('disabled', true);
            $('.theme-loader').css("display", "block");
            $('.theme-loader').show();
          },
          complete: function () { // After complete the Ajax
            $('#submit').attr('disabled', false);
            $('.theme-loader').css("display", "none");
            $('.theme-loader').hide();
          },
          success: function (response) {
            if (response.status == 0 || response.status == '0') { // Failure Response
              $('#txt_password').val('');
              $('#submit').attr('disabled', false);
              $("#id_error_display_signin").html(response.msg);
              if (response.msg === null || response.msg === '') {
                $("#id_error_display_signin").html('Service not running, Kindly check the service!!');
              }
            }
            else if (response.status == 1) { // Success Response
              $('#submit').attr('disabled', false);
              var hid_sendurl = $("#hid_sendurl").val();
              console.log(hid_sendurl)
              window.location = hid_sendurl; // Redirect the URL
            }
          },
          error: function (response, status, error) {
            console.log(error)
            console.log(response)
            $('#txt_password').val('');
            $('#submit').attr('disabled', false);
            $("#id_error_display_signin").html(response.msg);
          }
        });
      }
    });

    var percentage = 0;

    function check(n, m) {
      var strn_disp = "Very Weak Password";
      if (n < 6) {
        percentage = 0;
        $(".progress-bar").css("background", "#FF0000");
        strn_disp = "Very Weak Password";
      } else if (n < 7) {
        percentage = 20;
        $(".progress-bar").css("background", "#758fce");
        strn_disp = "Weak Password";
      } else if (n < 8) {
        percentage = 40;
        $(".progress-bar").css("background", "#ff9800");
        strn_disp = "Medium Password";
      } else if (n < 10) {
        percentage = 60;
        $(".progress-bar").css("background", "#A5FF33");
        strn_disp = "Strong Password";
      } else {
        percentage = 80;
        $(".progress-bar").css("background", "#129632");
        strn_disp = "Very Strong Password";
      }

      //Lowercase Words only
      if ((m.match(/[a-z]/) != null)) {
        percentage += 5;
      }

      //Uppercase Words only
      if ((m.match(/[A-Z]/) != null)) {
        percentage += 5;
      }

      //Digits only
      if ((m.match(/0|1|2|3|4|5|6|7|8|9/) != null)) {
        percentage += 5;
      }

      //Special characters
      if ((m.match(/\W/) != null) && (m.match(/\D/) != null)) {
        percentage += 5;
      }

      // Update the width of the progress bar
      $(".progress-bar").css("width", percentage + "%");
      $("#strength_display").html(strn_disp);
    }

    // Update progress bar as per the input
    $(document).ready(function () {
      // Whenever the key is pressed, apply condition checks.
      $("#txt_user_password").keyup(function () {
        var m = $(this).val();
        var n = m.length;

        // Function for checking
        check(n, m);
      });
    });


    function checkPasswordStrength() {
      var txt_user_password = $('#txt_user_password').val();
      var number = /([0-9])/;
      var alphabets = /([a-zA-Z])/;
      var special_characters = /([~,!,@,#,$,%,^,&,*,-,_,+,=,?,>,<])/;
      var txt_user_password = $('#txt_user_password').val();
      $('#txt_user_password').css('border-color', '');
      if (txt_user_password != '') {
        if (txt_user_password.length < 8) {
          console.log("Weak (should be at least 8 characters.)");
          $('#txt_user_password').css('border-color', 'red');
          return false;
        } else {
          if ($('#txt_user_password').val().match(number) && $('#txt_user_password').val().match(alphabets) && $(
            '#txt_user_password').val().match(special_characters)) {
            console.log("Strong");
            $('#txt_user_password').css('border-color', '#a0a0a0');
            return true;
          } else {
            console.log("Medium (should include alphabets, numbers and special characters.)");
            $('#txt_user_password').css('border-color', 'red');
            return false;
          }
        }
      }
    }

    // Sign up submit Button function Start
    $(document).on("submit", "form#frm_signup", function (e) {
      $("#id_error_display_signup").html("");
      e.preventDefault();
      //get input field values 
      var clientname_txt = $('#clientname_txt').val();
      var login_id_txt = $('#login_id_txt').val();
      var mobile_no_txt = $('#mobile_no_txt').val();
      var email_id_contact = $('#email_id_contact').val();
      var password = $('#txt_user_password').val();
      var confirm_password = $('#txt_confirm_password').val();
      var flag = true;

      /********validate all our form fields***********/
      if (clientname_txt == "") {
        $('#clientname_txt').css('border-color', 'red');
        flag = false;
      }
      if (login_id_txt == "") {
        $('#login_id_txt').css('border-color', 'red');
        flag = false;
      }
      if (email_id_contact == "") {
        $('#email_id_contact').css('border-color', 'red');
        flag = false;
      }

      if (mobile_no_txt == "") {
        $('#mobile_no_txt').css('border-color', 'red');
        flag = false;
      }
      var mobile_no_txt = document.getElementById('mobile_no_txt').value;
      if (mobile_no_txt.length != 10) {
        $("#id_error_display_onboarding").html("Please enter a valid mobile number");
        flag = false;
      }
      if (!(mobile_no_txt.charAt(0) == "9" || mobile_no_txt.charAt(0) == "8" || mobile_no_txt.charAt(0) == "6" || mobile_no_txt.charAt(0) == "7")) {
        $("#id_error_display_onboarding").html("Please enter a valid mobile number");
        document.getElementById('mobile_no_txt').focus();
        flag = false;
      }
      /************************************/

      var email_id_contact = $('#email_id_contact').val();
      /* Email field validation  */
      var filter = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i
      if (filter.test(email_id_contact)) {
        // flag = true;
      } else {
        $('#email_id_contact').css('border-color', 'red');
        $("#id_error_display_onboarding").html("Email is invalid");
        document.getElementById('email_id_contact').focus();
        flag = false;
        e.preventDefault();
      }
      /* password field validation  */
      if (password == "") {
        $('#txt_user_password').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      } else {
        if (checkPasswordStrength() == false) {
          flag = false;
          e.preventDefault();
        }
      }
      /* confirm_password field validation  */
      if (confirm_password == "") {
        $('#txt_confirm_password').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      }
      /* password, confirm_password field validation  */
      if (confirm_password != "" && password != "" && confirm_password != password) {
        $('#txt_confirm_password').css('border-color', 'red');
        //alert();
        $("#id_error_display_onboarding").html("Confirm Password mismatch with Password");
        flag = false;
        e.preventDefault();
      }

      if ($("#chk_terms").prop('checked') == true) {
      } else {
        $("#id_error_display_onboarding").html("Please select the terms & conditions");
        flag = false;
        e.preventDefault();
      }

      /********Validation end here ****/

      /* If all are ok then we send ajax request to call_functions.php *******/
      if (flag) {
        $('#txt_confirm_password').css({ 'border-color': '' });
        var data_serialize = $("#frm_signup").serialize();
        var fd = new FormData(this);

        $.ajax({
          type: 'post',
          url: "ajax/call_functions.php?call_function=onboarding_signup",
          dataType: 'json',
          data: fd,
          contentType: false,
          processData: false,
          beforeSend: function () { // Before Send to Ajax
            $('#submit').attr('disabled', true);
            $('.theme-loader').css("display", "block");
            $('.theme-loader').show();
            $("#id_error_display_onboarding").html("");

          },
          complete: function () { // After complete the Ajax
            $('.theme-loader').css("display", "none");
            $('#submit').attr('disabled', false);
            $('.theme-loader').hide();
          },
          success: function (response) { // Success
            if (response.status == 0 || response.status == '0') { // Failure Response
              $('#submit').attr('disabled', false);
              $("#id_error_display_onboarding").html(response.msg);
              if (response.msg === null || response.msg === '') {
                $("#id_error_display_onboarding").html('Service not running, Kindly check the service!!');
              }
            }
            else if (response.status == 1) { // Success Response
              $('#submit').attr('disabled', false);
              $("#id_error_display_onboarding").html(response.msg);
              $('#clientname_txt').val('');
              $('#login_id_txt').val('');
              $('#email_id_contact').val('');
              $('#mobile_no_txt').val('');
              $('#txt_user_password').val('');
              $('#txt_confirm_password').val('');
              setInterval(function () {
                window.location = 'index';
              }, 2000);
            } else if (response.status == 2) {
              //alert(response.msg);
              $('#submit').attr('disabled', false);
              $("#id_error_display_onboarding").html(response.msg);
            }
          },
          error: function (response, status, error) { // If any error occurs
            $('#txt_password').val('');
            $('#submit').attr('disabled', false);
            $("#id_error_display_onboarding").html(response.msg);
          }
        });
      }
    });
    // Sign in submit Button function End


    function clsAlphaNoOnly(e) { // Accept only alpha numerics, no special characters 
      var key = e.keyCode;
      if ((key >= 65 && key <= 90) || (key >= 97 && key <= 122) || (key >= 48 && key <= 57) || (key == 32) || (key == 95)) {
        return true;
      }
      return false;
    }

    // TEMplate Name - Space
    $(function () {
      $('#clientname_txt').on('keypress', function (e) {
        if (e.which == 32) {
          console.log('Space Detected');
          return false;
        }
      });
    });
    $(function () {
      $('#login_id_txt').on('keypress', function (e) {
        if (e.which == 32) {
          console.log('Space Detected');
          return false;
        }
      });
    });
    $(function () {
      $('#txt_username').on('keypress', function (e) {
        if (e.which == 32) {
          console.log('Space Detected');
          return false;
        }
      });
    });

    // 

    
    $(document).on("submit", "form#frm_forgotpass", function (e) {
      $("#id_error_forgotpass").html("");
      e.preventDefault();
      var email_id_reset = $('#email_id_reset').val();
      var flag = true;
      /********validate all our form fields***********/
      /* Name field validation  */
      if (email_id_reset == "") {
        $('#email_id_reset').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      }

      /* Email field validation  */
      var filter = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i
      if (filter.test(email_id_reset)) {
        // flag = true;
      } else {
        $('#email_id_reset').css('border-color', 'red');
        $("#id_error_forgotpass").html("Email is invalid");
        document.getElementById('email_id_reset').focus();
        flag = false;
        e.preventDefault();
      }

      /********Validation end here ****/

      /* If all are ok then we send ajax request to process_connect.php *******/
      if (flag) {
        var data_serialize = $("#frm_forgotpass").serialize();
        $.ajax({
          type: 'post',
          url: "ajax/call_functions.php?call_function=resetpwd",
          dataType: 'json',
          data: data_serialize,
          beforeSend: function () { // Before Send to Ajax
            $('#submit_forgot').attr('disabled', true);
            $('.theme-loader').css("display", "block");
            $('.theme-loader').show();
          },
          complete: function () { // After complete the Ajax
            $('#submit_forgot').attr('disabled', false);
            $('.theme-loader').css("display", "none");
            $('.theme-loader').hide();
          },
          success: function (response) {
            if (response.status == 0 || response.status == '0') { // Failure Response
              $('#email_id_reset').val('');
              $('#submit_forgot').attr('disabled', false);
              $("#id_error_forgotpass").html(response.msg);
              if (response.msg === null || response.msg === '') {
                $("#id_error_forgotpass").html('Service not running, Kindly check the service!!');
              }
            }
            else if (response.status == 1) { // Success Response
              $('#submit_forgot').attr('disabled', false);
              var hid_sendurl = $("#hid_sendurl").val();
              console.log(hid_sendurl)
              window.location = hid_sendurl; // Redirect the URL
            }
          },
          error: function (response, status, error) {
            console.log(error)
            console.log(response)
            $('#email_id_reset').val('');
            $('#submit_forgot').attr('disabled', false);
            $("#id_error_forgotpass").html(response.msg);
          }
        });
      }
    });


    
  </script>
</body>

</html>

