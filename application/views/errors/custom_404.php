<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>404 - Page Not Found</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="16x16">
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="32x32">
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="64x64">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 0;
            /* Removed padding */
            background: none;
            /* Removed background */
            border-radius: 0;
            /* Removed border-radius */
            box-shadow: none;
            /* Removed shadow */
            margin: 0 auto;
        }

        .logo-side img {
            width: 160px;
            height: auto;
            margin-bottom: 30px;
        }

        h1 {
            font-size: 64px;
            margin-bottom: 10px;
        }

        p {
            font-size: 20px;
            color: #6c757d;
            margin-bottom: 30px;
        }

        a.button {
            display: inline-block;
            padding: 10px 12px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        a.button:hover {
            background-color: #0056b3;
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 48px;
            }

            .logo-side img {
                width: 120px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="logo-side">
            <img src="<?php echo base_url('assets/Logo/precot.png'); ?>" alt="Logo" />
        </div>

        <h1>404</h1>

        <p>The page you are looking for doesn’t exist or was moved.</p>

        <a href="<?php echo base_url(); ?>" class="button">Return to Homepage</a>
    </div>

</body>

</html>