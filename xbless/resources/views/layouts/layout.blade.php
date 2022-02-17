<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CV KUJANG MARINAS UTAMA - @yield('title') </title>

    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/font-awesome/css/font-awesome.css')}}" rel="stylesheet">

    <!-- Toastr style -->
    <link href="{{ asset('assets/css/plugins/toastr/toastr.min.css')}}" rel="stylesheet">

    <!-- Gritter -->
    <link href="{{ asset('assets/js/plugins/gritter/jquery.gritter.css')}}" rel="stylesheet">

    <link href="{{ asset('assets/css/animate.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css')}}" rel="stylesheet">

    <link href="{{ asset('assets/css/plugins/datapicker/datepicker3.css')}}" rel="stylesheet">

    <link href="{{ asset('assets/css/plugins/dataTables/datatables.min.css')}}" rel="stylesheet">

    <link href="{{ asset('assets/css/plugins/iCheck/custom.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/plugins/select2/select2.min.css')}}" rel="stylesheet">

    <link href="{{ asset('assets/css/plugins/touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet">
    <!-- <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/logo/fav.png')}}"> -->

    <!-- Sweet Alert -->
    <link href="{{ asset('assets/css/plugins/sweetalert/sweetalert.css')}}" rel="stylesheet">

    <link href="{{ asset('assets/css/plugins/daterangepicker/daterangepicker-bs3.css')}}" rel="stylesheet">
    <style>
        .swal2-container{
            z-index: 99999 !important;
        }
        </style>
    @yield('css')

</head>
<body>


    <!-- Modal loading search-->
    <div class="modal fade" id="loaderModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="margin-top: 15%;">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body">
                    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
                <lottie-player src="https://assets3.lottiefiles.com/datafiles/bEYvzB8QfV3EM9a/data.json"  background="transparent"  speed="1"  style="width: 300px; height: 300px; margin:auto;"  loop  autoplay></lottie-player>
                </div>
            </div>
        </div>
    </div>

    <!-- Wrapper-->
    <div id="wrapper">

        <!-- Navigation -->
        @include('layouts.navigation')

        <!-- Page wraper -->
        <div id="page-wrapper" class="gray-bg">

            <!-- Page wrapper -->
            @include('layouts.topnavbar')

            <!-- Main view  -->
            @yield('content')

            <!-- Footer -->
            @include('layouts.footer')

        </div>
        <!-- End page wrapper-->

    </div>
    <!-- End wrapper-->


<!-- Mainly scripts -->
<script src="{{ asset('assets/js/jquery-3.1.1.min.js')}}"></script>
<script src="{{ asset('assets/menuactive.js') }}"></script>
<script src="{{ asset('assets/js/jquery.ambiance.js') }}"></script>
<script src="{{ asset('assets/js/popper.min.js')}}"></script>
<script src="{{ asset('assets/js/bootstrap.js')}}"></script>
<script src="{{ asset('assets/js/plugins/metisMenu/jquery.metisMenu.js')}}"></script>
<script src="{{ asset('assets/js/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>

<!-- Datatables -->
<script src="{{ asset('assets/js/plugins/dataTables/datatables.min.js')}}"></script>
<script src="{{ asset('assets/js/plugins/dataTables/dataTables.bootstrap4.min.js')}}"></script>

<!-- Peity -->
<script src="{{ asset('assets/js/plugins/peity/jquery.peity.min.js')}}"></script>
<script src="{{ asset('assets/js/demo/peity-demo.js')}}"></script>

<!-- Custom and plugin javascript -->
<script src="{{ asset('assets/js/inspinia.js')}}"></script>
<script src="{{ asset('assets/js/plugins/pace/pace.min.js')}}"></script>

<!-- jQuery UI -->
<script src="{{ asset('assets/js/plugins/jquery-ui/jquery-ui.min.js')}}"></script>

<!-- GITTER -->
<script src="{{ asset('assets/js/plugins/gritter/jquery.gritter.min.js')}}"></script>

<!-- Sparkline -->
<script src="{{ asset('assets/js/plugins/sparkline/jquery.sparkline.min.js')}}"></script>

<!-- Sparkline demo data  -->
<script src="{{ asset('assets/js/demo/sparkline-demo.js')}}"></script>

<!-- Data picker -->
<script src="{{ asset('assets/js/plugins/datapicker/bootstrap-datepicker.js')}}"></script>

<!-- Toastr -->
<script src="{{ asset('assets/js/plugins/toastr/toastr.min.js')}}"></script>


<!-- iCheck -->
<script src="{{ asset('assets/js/plugins/iCheck/icheck.min.js')}}"></script>
<script src="{{ asset('assets/js/plugins/touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
<!-- Date range use moment.js same as full calendar plugin -->
<script src="{{ asset('assets/js/plugins/fullcalendar/moment.min.js')}}"></script>

<!-- Date range picker -->
<script src="{{ asset('assets/js/plugins/daterangepicker/daterangepicker.js')}}"></script>

<!-- Select2 -->
<script src="{{ asset('assets/js/plugins/select2/select2.full.min.js')}}"></script>

<!-- Jquery Validate -->
<script src="{{ asset('assets/js/plugins/validate/jquery.validate.min.js')}}"></script>

<!-- Sweet alert -->
<script src="{{ asset('assets/js/plugins/sweetalert/sweetalert.js')}}"></script>


<script src="{{ asset('assets/js/plugins/touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
@stack('scripts')
<script>
    var CURRENT_URL = window.location.href.split('#')[0].split('?')[0],
    $SIDEBAR_MENU = $('#sidebar-menu');

    // Sidebar
    $(document).ready(function() {
        // TODO: This is some kind of easy fix, maybe we can improve this
        // check active menu
        var segments = CURRENT_URL.split( '/' );
        // console.log(segments[3]);
        var iniurl = window.location.origin;
        var tamp = ''+iniurl;

        for(var i=0; i<segments.length; i++){
            if(i>=3){
                tamp += '/'+segments[i];
            }


        }
        // console.log(tamp);
        // var potongurl= iniurl+'/'+segments[3]+'/'+segments[4]+'/'+segments[5]+'/'+segments[6];
        var potongurl= tamp;
        // console.log(potongurl);
        $SIDEBAR_MENU.find('ul a[href="' + potongurl + '"]').parents('li').addClass('active');
        // $SIDEBAR_MENU.find('ul a[href="' + potongurl + '"]').parents('ul').addClass('in');
        // console.log($SIDEBAR_MENU);
        $SIDEBAR_MENU.find('a').filter(function () {
            return this.href == potongurl;
        }).addClass('active').parents('li').slideDown(function() {
        });
    });
    $(function () {
        toastr.options = {
                "closeButton": true,
                "debug": false,
                "progressBar": true,
                "preventDuplicates": false,
                "positionClass": "toast-top-right",
                "onclick": null,
                "showDuration": "400",
                "hideDuration": "1000",
                "timeOut": "7000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
        };
    });
</script>
</body>
</html>
