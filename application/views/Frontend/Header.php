<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title><?php echo $Favicon; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="16x16">
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="32x32">
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/Logo/Precot-App-Logo.png') ?>" sizes="64x64">

    <!-- Responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Icons & Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/styles/core.css') ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/styles/icon-font.min.css') ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/src/plugins/datatables/css/dataTables.bootstrap4.min.css') ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/src/plugins/datatables/css/responsive.bootstrap4.min.css') ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/styles/style.css') ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/styles/preloader.css') ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/src/plugins/switchery/switchery.min.css') ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/src/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') ?>">

    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <!-- Base URL for JS -->
    <script>
        var baseurl = '<?php echo base_url() ?>';
    </script>

    <!-- Optional custom styles -->
    <style>
        .header {
            background-color: #2c3e50;
            color: whitesmoke;
            padding: 10px 20px;
        }

        .header h4 {
            margin: 0;
            font-weight: 600;
        }

        .dropdown-menu a {
            color: #000;
        }

        .user-icon img {
            border-radius: 50%;
            height: 40px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header d-flex justify-content-between align-items-center">
        <!-- Left: Menu Icon -->
        <div>
            <div class="menu-icon dw dw-menu"></div>
        </div>

        <!-- Center: Title -->
        <div class="flex-grow-1 text-center">
            <!-- <h4 class="mb-0" style="color: white; font-family: EB Garamond ">WORK ALLOCATION MANAGEMENT</h4> -->
            <h5 class="mb-0" style="color: white; font-family: EB Garamond ">Work Allocation Management</h5>

        </div>


        <!-- Right: User Section -->
        <div class="d-flex align-items-center">
            <?php
            $Session = $this->session->userdata('sess_array');
            if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {
                $UserRole = $Session['Designation'];
                $LocationCode = $Session['Lcode'];
            ?>

                <?php if ($UserRole == 'Manager' || $UserRole == 'Super Admin') { ?>
                    <div class="mr-2">
                        <select class="custom-select2 form-control" name="Lcode" id="Lcode" style="min-width: 180px;">
                            <option value="">Choose Location</option>
                            <option value="PRECOT - CO" <?= ($LocationCode == 'PRECOT - CO') ? 'selected' : ''; ?>>PRECOT - CO</option>
                            <option value="PRECOT - A" <?= ($LocationCode == 'PRECOT - A') ? 'selected' : ''; ?>>PRECOT - A</option>
                            <option value="PRECOT - B" <?= ($LocationCode == 'PRECOT - B') ? 'selected' : ''; ?>>PRECOT - B</option>
                            <option value="PRECOT - C" <?= ($LocationCode == 'PRECOT - C') ? 'selected' : ''; ?>>PRECOT - C</option>
                            <option value="PRECOT - D" <?= ($LocationCode == 'PRECOT - D') ? 'selected' : ''; ?>>PRECOT - D</option>
                            <option value="PRECOT - M" <?= ($LocationCode == 'PRECOT - M') ? 'selected' : ''; ?>>PRECOT - M</option>
                            <option value="PRECOT - K" <?= ($LocationCode == 'PRECOT - K') ? 'selected' : ''; ?>>PRECOT - K</option>
                        </select>
                        <input type="hidden" id="Ccode" value="PRECOT">
                    </div>
                    <button type="button" class="btn btn-warning btn-sm mr-3" id="Update_Location" style="padding: 10px;">Update</button>
                <?php } ?>
            <?php } ?>


            <!-- User Dropdown -->
            <div class="user-info-dropdown">
                <div class="dropdown user-icon">
                    <a href="#" role="button" id="userDropdown">
                        <img src="<?php echo base_url('assets/Logo/Precot-Avatar.jpg'); ?>" alt="User Avatar" style="margin-buttom: 90px;">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list" id="dropdownMenu">

                        <a class="dropdown-item">
                            <i class="dw dw-user1"></i>
                            <?php echo isset($Session['UserName']) ? $Session['UserName'] : "Unknown User"; ?>
                        </a>
                        <a class="dropdown-item" href="<?php echo base_url('Auth/logout'); ?>">
                            <i class="dw dw-logout"></i> Log Out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dropdown Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dropdownToggle = document.getElementById("userDropdown");
            const dropdownMenu = document.getElementById("dropdownMenu");

            dropdownToggle.addEventListener("click", function(e) {
                e.preventDefault();
                dropdownMenu.classList.toggle("show");
            });

            document.addEventListener("click", function(e) {
                if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove("show");
                }
            });
        });

    </script>

    

    <!-- JS Scripts -->
    <script src="<?php echo base_url('assets/vendors/scripts/core.js') ?>"></script>
    <script src="<?php echo base_url('assets/Script/IT.js') ?>"></script>