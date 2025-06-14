<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">


<head>
	<meta charset="utf-8">
	<title>404 - Page Not Found</title>
	<!-- Favicon -->
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="16x16">
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="32x32">
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="64x64">
	<style>
		body {
			font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
			background-color: #f8f9fa;
			color: #343a40;
			text-align: center;
			padding: 50px;
		}

		.logo {
			width: 150px;
			margin-bottom: 30px;
		}

		.error-container {
			max-width: 600px;
			margin: auto;
			padding: 30px;
			background-color: #fff;
			border: 1px solid #dee2e6;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
		}

		h1 {
			font-size: 48px;
			margin-bottom: 10px;
		}

		p {
			font-size: 18px;
			color: #6c757d;
		}

		a {
			display: inline-block;
			margin-top: 20px;
			padding: 12px 24px;
			background-color: #007bff;
			color: #fff;
			text-decoration: none;
			border-radius: 6px;
		}

		a:hover {
			background-color: #0056b3;
		}
	</style>
</head>

<body>

	<div class="error-container">
		<!-- ✅ Your Logo -->
		<img src="<?php echo base_url('assets/Logo/precot.png'); ?>" alt="Logo" class="logo">

		<h1><?php echo $heading; ?></h1>
		<p><?php echo $message; ?></p>

		<a href="<?php echo base_url(); ?>">Go to Homepage</a>
	</div>

</body>

</html>