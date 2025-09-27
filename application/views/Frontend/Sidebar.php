<div class="left-side-bar">
    <div class="brand-logo" style="display: flex; justify-content: center;">
        <a href="#">
            <img src="<?php echo base_url('assets/Logo/precot.png') ?>" alt="" class="light-logo"
                style="height: 45px; width: 110px;">
        </a>
        <div class="close-sidebar" data-toggle="left-sidebar-close">
            <i class="ion-close-round"></i>
        </div>
    </div>
    <div class="menu-block customscroll">
        <div class="sidebar-menu">
            <ul id="accordion-menu">
                <?php
                $Session = $this->session->userdata('sess_array');
                if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

                    $CompanyCode = $Session['Ccode'];
                    $LocationCode = $Session['Lcode'];
                    $UserRole = $Session['Designation'];

                    $Sql = "SELECT * FROM Web_Privacy_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND UserRole = '$UserRole' AND Status = 'Yes' AND Screen  != '' AND  Screen  != '0'";
                    $Query = $this->db->query($Sql);

                    // print_r($Sql);exit;

                    if ($Query->num_rows() > 0) {
                        $results = $Query->result();

                        $menu_structure = [];
                        foreach ($results as $row) {
                            $menu = trim($row->Menu);
                            $sub_menu = trim($row->Sub_Menu);
                            $screen = $row->Screen;

                            if (!isset($menu_structure[$menu])) {
                                $menu_structure[$menu] = [];
                            }

                            $menu_structure[$menu][] = [
                                'name' => $sub_menu,
                                'screen' => $screen,
                                'menu' => $menu
                            ];
                        }

                        // Define the order of the main menus
                        $menu_order = [
                            'Home',
                            'Privacy',
                            'Master',
                            'Employee',
                            'Work Allocation',
                            'Employee OT',
                            'Leave Management',
                            'SAP Upload',
                            'Report'
                        ];

                        foreach ($menu_order as $menu) {
                            if (!isset($menu_structure[$menu])) {
                                continue; // Skip menus not available for this user
                            }

                            $icon = '';
                            switch ($menu) {
                                case 'Home':
                                    $icon = 'dw dw-house-1';
                                    break;
                                case 'Privacy':
                                    $icon = 'dw dw-shield';
                                    break;
                                case 'Master':
                                    $icon = 'dw dw-settings';
                                    break;
                                case 'Employee':
                                    $icon = 'fa fa-user';
                                    break;
                                case 'Leave Management':
                                    $icon = 'fa fa-bed';
                                    break;
                                case 'Report':
                                    $icon = 'fa fa-chart-bar';
                                    break;
                                case 'Work Allocation':
                                    $icon = 'fa fa-briefcase';
                                    break;
                                case 'SAP Upload':
                                    $icon = 'fa fa-upload';
                                    break;
                                default:
                                    $icon = 'dw dw-list';
                                    break;
                            }

                            if ($menu === 'Home') {
                                echo '<li class="dropdown">
                        <a href="' . base_url('Home') . '" class="dropdown-toggle">
                            <span class="micon ' . $icon . '"></span><span class="mtext">Home</span>
                        </a>
                      </li>';
                            } elseif ($menu === 'Leave Management') {
                                echo '<li class="dropdown">
                        <a href="' . base_url('Management/Employee') . '" class="dropdown-toggle">
                            <span class="micon ' . $icon . '"></span><span class="mtext">Leave Management</span>
                        </a>
                      </li>';
                            } else {
                                echo '<li class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle">
                            <span class="micon ' . $icon . '"></span><span class="mtext">' . $menu . '</span>
                        </a>
                        <ul class="submenu">';

                                foreach ($menu_structure[$menu] as $sub) {
                                    if ($sub['screen'] == 1) {
                                        $route = '#';
                                        $sub_menu_name = $sub['name'];

                                        $routes = [
                                            'User Permission' => 'Privacy',
                                            'Add Menu' => 'Privacy/Add_Menu',
                                            'Add Sub Menu' => 'Privacy/Add_SubMenu',

                                            'Employee Requirement' => 'Master/Standard_Actual',
                                            'Position Grade Mapping' => 'Master/Employee_Position_Map',
                                            'Machine Master' => 'Master/Machine_Master',
                                            'Unit E-Master Status' => 'Admin/Unit_Details',

                                            'Employee Live Punching List' => 'Employee/Punching_List',
                                            'Employee Attendance Report' => 'Employee/Employee_Attendance',
                                            'Employee Manual Attendance' => 'Employee/Attendance_Entry',
                                            'Employee Punching Details' => 'Employee/Details_Punching_List',
                                            'Employee I-Report' => 'Incentive/Position_Report',

                                            'Employee Work Allocation' => 'Work',
                                            'Late Work Allocation' => 'Work/Late_Extra',
                                            'Employee Partial Closing' => 'Work/Partial_Close',
                                            'Employee Shift Closing' => 'Shift_Closing',
                                            

                                            'Employee Status' => 'Grade_Master',
                                            'Employee Attendace Grade' => 'Grade_Master/Attendance_Sheet',

                                            'Work Allocation Report' => 'Reports/Work_Allocation',
                                            'Late -  Extra Work Allocation Report' => 'Reports/Late_Report',
                                            'Shift Closing Report' => 'Reports/Shift_Closing_Reports',
                                            'NoWork Employee Report' => 'Reports/No_Work_Employee',
                                            'OT Employee List' => 'Reports/OT_Employee',

                                            'Daily Machine Details Upload' => 'SAP/Machine_Work_Details',

                                            'Extra Work Allocation' => 'OT/Allocation',
                                            'Extra Hours Closing' => 'OT/Partial_Closing',
                                            'Employee OT Details' => 'OT/Details',
                                            'Consolidation OT-Extra Hours Report' => 'OT/OT_Extra_Hours'
                                        ];

                                        if (isset($routes[$sub_menu_name])) {
                                            $route = base_url($routes[$sub_menu_name]);
                                        }

                                        echo '<li><a href="' . $route . '">' . $sub_menu_name . '</a></li>';
                                    }
                                }

                                echo '</ul></li>';
                            }
                        }
                    }
                }
                ?>

        </div>
    </div>
</div>

<!-- </div> -->

<!-- js -->
<script src="<?php echo base_url('assets/vendors/scripts/core.js') ?>"></script>
<script src="<?php echo base_url('assets/vendors/scripts/script.min.js') ?>"></script>
<script src="<?php echo base_url('assets/vendors/scripts/process.js') ?>"></script>
<script src="<?php echo base_url('assets/vendors/scripts/layout-settings.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/responsive.bootstrap4.min.js') ?>"></script>
<!-- buttons for Export datatable -->
<script src="<?php echo base_url('assets/src/plugins/datatables/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/buttons.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/buttons.print.min.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/buttons.html5.min.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/buttons.html5.min.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/buttons.flash.min.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/pdfmake.min.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/datatables/js/vfs_fonts.js') ?>"></script>
<script src="<?php echo base_url('assets/src/plugins/apexcharts/apexcharts.min.js') ?>"></script>


<script>
    $(document).ready(function() {

        // $('#Tables').DataTable();

        var currentURL = window.location.href;

        $('.submenu a').each(function() {
            if (this.href === currentURL) {
                $(this).closest('li.dropdown').addClass('active');
                $(this).closest('.submenu').css('display', 'block');
                $(this).closest('ul.submenu').addClass('active-submenu');
                $(this).addClass('active-item');
            }
        });

        $('.dropdown-toggle').off('click').on('click', function(e) {
            e.stopPropagation();
            var parent = $(this).parent('li');

            if (parent.hasClass('active')) {
                parent.removeClass('active').find('.submenu').stop(true, true).slideUp();
                parent.find('ul.submenu').removeClass('active-submenu');
                parent.find('ul.submenu li a').removeClass('active-item');
            } else {
                $('.dropdown').removeClass('active').find('.submenu').slideUp();
                parent.addClass('active').find('.submenu').stop(true, true).slideDown();
            }
        });

        $('.submenu a').on('click', function(e) {
            e.stopPropagation();
            $('.submenu').removeClass('active-submenu');
            $('.submenu a').removeClass('active-item');

            $(this).closest('ul.submenu').addClass('active-submenu');
            $(this).addClass('active-item');
        });
    });



</script>


<style>
    ul.submenu.active-submenu {
        /* background-color: #2596be; Light yellow background for the active submenu */
    }

    .active-item {
        background-color: rgb(165, 38, 185);
        color: white;
    }

    ul.submenu li a:hover {}

    
</style>