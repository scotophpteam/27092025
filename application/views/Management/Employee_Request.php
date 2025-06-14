<div class="main-container">

    <style>
        .order-card {
            color: #fff;
        }

        .bg-c-blue {
            background: linear-gradient(45deg, #4099ff, #73b4ff);
        }

        .bg-c-green {
            background: linear-gradient(45deg, #2ed8b6, #59e0c5);
        }

        .bg-c-yellow {
            background: linear-gradient(45deg, #FFB64D, #ffcb80);
        }

        .bg-c-pink {
            background: linear-gradient(45deg, #FF5370, #ff869a);
        }

        .card {
            border-radius: 5px;
            box-shadow: 0 2px 6px rgba(4, 26, 55, 0.15);
            border: none;
            margin-bottom: 20px;
            transition: all 0.3s ease-in-out;
        }

        .card .card-block {
            padding: 20px;
            position: relative;
        }

        .order-card i {
            font-size: 32px;
            opacity: 0.8;
        }

        .f-left {
            float: left;
        }

        .f-right {
            float: right;
        }

        .card a {
            color: #fff;
            font-weight: bold;
            font-size: 13px;
            text-decoration: none;
        }
    </style>

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-100px">

            <div class="pd-5 card-box mb-30">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">

                        <!-- Card 1 -->
                        <div class="col-md-4 col-xl-3">
                            <div class="card bg-c-blue order-card clickable-card" data-href="Management/Leave_Apply" style="cursor: pointer;">
                                <div class="card-block">
                                    <i class="fas fa-calendar-plus f-right"></i>
                                    <div class="clear-both"></div>
                                    <span>Leave Request</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="col-md-4 col-xl-3">
                            <div class="card bg-c-green order-card clickable-card" data-href="Management/Leave_Apply_List" style="cursor: pointer;">
                                <div class="card-block">
                                    <i class="fas fa-list f-right"></i>
                                    <div class="clear-both"></div>
                                    <span>Leave Request List</span>
                                </div>
                            </div>
                        </div>




                        <!-- Add more cards below by copying a block -->

                    </div>
                </form>

                <!-- Preloader -->
                <div class="min-height-200px">
                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." width="60">
                    </div>
                </div>

            </div>

            <!-- External JS -->
            <script src="<?php echo base_url('assets/Script/Management.js') ?>"></script>
        </div>

        <script>
            $(document).ready(function() {
                $('.clickable-card').on('click', function() {
                    var href = $(this).data('href');
                    if (href) {
                        window.location.href = base_url + href;
                    }
                });
            });

            // Make sure base_url is defined
            var base_url = "<?php echo base_url(); ?>";
        </script>