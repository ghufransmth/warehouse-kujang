<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kujang Marinas | Login</title>

    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/font-awesome/css/font-awesome.css')}}" rel="stylesheet">

    <link href="{{ asset('assets/css/animate.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css')}}" rel="stylesheet">
    <style type="text/css">
        .middle-box {
            padding-top: 15% !important;
            max-width: 500px !important;
            width: 500px !important;
        }

        .thebox {
            padding-top: 15% !important;
            /*background-color: #72B5E9;*/
            background-color: #ffffff;
            padding: 15px;
            /*border: solid 1px;*/
            border-radius: 5%;
            /*box-shadow: 3px 3px 3px grey;*/
            opacity: 0.95;
            width: 500px;
        }

        /* body {
            background-image: url("{{ asset('assets/background/body_bg.jpg')}}") !important;
            background-repeat: no-repeat;
            background-size: 50%;
        } */
    </style>

</head>

<body class="white-bg">
    <div class="container animated fadeInDownm-auto">
        <div class="row text-center">
            <div class="col-md-6">
                <img src="{{ asset('assets/background/bg6.jpg')}}" alt="" style="width: 100%">
            </div>
            <div class="col-md-6 ">
                <div class="thebox">
                    <h2 class="m-3 ">Kujang Marinas</h2>
                    <h3 class="">ADMIN PANEL</h3>
                    @if(session('message'))
                    <div class="alert alert-{{session('message')['status']}}">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        {{ session('message')['desc'] }}
                    </div>
                    @endif
                    <form class="m-t" role="form" action="{{route('manage.checklogin')}}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <input type="text" name="akun" class="form-control" placeholder="Username" required="">
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-control" placeholder="Password"
                                required="">
                        </div>
                        <button type="submit" class="btn btn-primary block full-width m-b">Login</button>

                        <!-- <a href="#"><small>Forgot password?</small></a> -->
                    </form>
                    <div class="text-right">
                        <small>
                            <strong style="color: #1ab394">Copyright</strong> Buruh Ketik &copy; 2021
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mainly scripts -->
    <script src="{{ asset('assets/js/jquery-3.1.1.min.js')}}"></script>
    <script src="{{ asset('assets/js/popper.min.js')}}"></script>
    <script src="{{ asset('assets/js/bootstrap.js')}}"></script>

</body>

</html>
