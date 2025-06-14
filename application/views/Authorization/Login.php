<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8">
    <title>E - Master Login Page</title>

    <!-- Site favicon -->
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="16x16">
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="32x32">
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="64x64">

    <!-- Mobile Specific Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/vendors/styles/core.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/vendors/styles/icon-font.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/vendors/styles/style.css') ?>">

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-119386393-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'UA-119386393-1');
    </script>

    <style>
        body {
            background-image: url('<?php echo base_url('assets/Logo/background.jpg') ?>');
            /* Ensure this image exists */
            background-size: cover;
            background-position: center;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
        }

        .notification.show {
            display: block;
            animation: fadeInOut 4s ease-in-out;
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
                transform: translateX(100%);
            }

            10% {
                opacity: 1;
                transform: translateX(0);
            }

            90% {
                opacity: 1;
                transform: translateX(0);
            }

            100% {
                opacity: 0;
                transform: translateX(100%);
            }
        }
    </style>
</head>

<body class="login-page">
    <div class="login-wrap d-flex align-items-center justify-content-center min-vh-100">
        <div class="container">

            <div class="row">
                <div id="successNotification" class="notification" style="background: #62bd56;"></div>
                <div id="errorNotification" class="notification" style="background: #e34242;"></div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="login-box"
                        style="background-color: rgba(255, 255, 255, 0.5); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); border-radius: 10px; padding: 16px;">
                        <div class="login-title text-center">
                            <a href="#">
                                <img src="<?php echo base_url('assets/Logo/precot.png') ?>" alt="Precot Logo"
                                    class="dark-logo" style="height: 60px; width: 140px;">
                            </a>
                        </div>
                        <form action="<?php echo base_url('Auth/Verify') ?>" method="POST" class="loginForm"
                            id="loginForm">
                            <div class="input-group custom mb-3 py-2">
                                <input type="text" class="form-control form-control-lg" name="UserName" id="username"
                                    placeholder="Username" value="<?php echo get_cookie('UserName'); ?>" required>
                                <div class="input-group-append custom">
                                    <span class="input-group-text"><i class="icon-copy dw dw-user1"></i></span>
                                </div>
                            </div>
                            <div class="input-group custom mb-3 py-2">
                                <input type="password" class="form-control form-control-lg" name="Password"
                                    id="password" placeholder="Password" value="<?php echo get_cookie('Password'); ?>"
                                    required>
                                <div class="input-group-append custom">
                                    <span class="input-group-text"><i class="dw dw-eye" id="togglePassword"></i></span>
                                </div>
                            </div>
                            <div class="row pb-30 py-4">
                                <div class="col-6">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="customCheck1"
                                            name="Remember" <?php echo (get_cookie('UserName')) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="customCheck1">Remember</label>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <button type="submit" class="btn btn-success btn-block" style="height: 30px; padding: 4px 12px; font-size: 14px;">Login</button>
                                    </div>
                                </div>
                                <div class="col-12 text-center mt-2">
                                    <span>V 2.0</span>
                                </div>
                            </div>



                    </div>
                    </form>
                </div>
            </div>
        </div>
        <?php if ($this->session->flashdata('logout_message')): ?>
            <script>
                alert("<?= $this->session->flashdata('logout_message'); ?>");
            </script>
        <?php endif; ?>


    </div>

    <!-- JS -->
    <script src="<?php echo base_url('assets/vendors/scripts/core.js') ?>"></script>
    <script src="<?php echo base_url('assets/vendors/scripts/script.min.js') ?>"></script>
    <script src="<?php echo base_url('assets/vendors/scripts/process.js') ?>"></script>
    <script src="<?php echo base_url('assets/vendors/scripts/layout-settings.js') ?>"></script>
    <script src="<?php echo base_url('assets/src/plugins/sweetalert2/sweetalert2.all.js') ?>"></script>
    <script src="<?php echo base_url('assets/src/plugins/sweetalert2/sweet-alert.init.js') ?>"></script>
</body>

<script>
    $(document).ready(function() {

        var base_url = "<?php echo base_url() ?>";


        $("#Save_Data").on("click", function() {


            var UserName = $("#username").val();
            var Password = $("#password").val();

            $.ajax({
                url: base_url + 'Auth/verify', // Your controller's method to handle login
                method: 'POST',
                data: {
                    UserName: UserName,
                    Password: Password
                },
                success: function(response) {
                    var Response_Data = JSON.parse(response); // Parse the JSON response

                    if (Response_Data.status == "error") { // Check if the status is error


                        swal({
                            type: 'warning',
                            title: 'warning',
                            text: Response_Data.message,
                        });
                    } else if (Response_Data.status == "success") {
                        // Redirect to the home page or wherever needed
                        window.location.href = "<?php echo base_url(); ?>Home";
                    }
                },
                error: function(xhr, status, error) {
                    swal({
                        type: 'warning',
                        title: 'warning',
                        text: 'An error occurred while processing your request.',
                    });
                }
            });
        });

        $("#togglePassword").on("click", function() {
            let password = $("#password");
            let type = password.attr("type") === "password" ? "text" : "password";
            password.attr("type", type);
        });

        $("#loginForm").on("submit", function(event) {
            event.preventDefault();

            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.status === "success") {
                        $("#successNotification").text(response.message).fadeIn(400);

                        setTimeout(function() {
                            $("#successNotification").fadeOut(400).delay(4500).fadeOut(400);
                            window.location.href = "<?= base_url('Home') ?>";
                        }, 2000);
                    } else if (response.status === "error") {
                        $("#errorNotification").text(response.message).fadeIn(400).delay(4500)
                            .fadeOut(400);
                    }
                }
            });
        });
    });
</script>

</html>