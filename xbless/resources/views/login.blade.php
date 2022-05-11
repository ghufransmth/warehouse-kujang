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

        .bg_body {
            background-image: url("{{ asset('assets/background/0201.jpg')}}") !important;
            background-repeat: no-repeat;
            background-size: 100%;
        }
    </style>

</head>

<body class="bg_body bg-white">
    <div class="loginColumns animated fadeInDown">
        <!--<div class="row bg-white" style="margin-left:-250px  ">-->
        <div class="row bg-white" style="">
            <div class="col-md-6 my-5 bg-white" style="background: ">
                <div class="d-flex justify-content-between">
                    <div class="">
                        <img src="{{ asset('assets/logo/kujang.png')}}" class="m-auto" alt="" width="100" height="50">
                    </div>
                    <div class="text-right">
                        <h3 class="m-auto">Kujang Marinas Utama</h3>
                        <h4 class="m-auto py-2">MOBILITY</h4>
                    </div>
                </div>
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
                        <input type="text" name="akun" class="form-control rounded" style="border-radius: 50%"
                            placeholder="Username" required="">
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control rounded" placeholder="Password"
                            required="">
                    </div>
                    <button type="submit" class="btn btn-success btn-rounded block full-width m-b">Login</button>

                    <!-- <a href="#"><small>Forgot password?</small></a> -->
                </form>
                <div class="text-right">
                    <small>
                        <strong style="color: #1ab394">Copyright</strong> KMU &copy; 2022
                    </small>
                </div>
            </div>
            <!--<div class="col-md-6" style="margin-top: -100px">-->
            <div class="col-md-6" style="">
                <img src="{{ asset('assets/background/396.jpg')}}" alt="" style="width: 100%">
            </div>
        </div>
    </div>

    <!-- Mainly scripts -->
    <script src="{{ asset('assets/js/jquery-3.1.1.min.js')}}"></script>
    <script src="{{ asset('assets/js/popper.min.js')}}"></script>
    <script src="{{ asset('assets/js/bootstrap.js')}}"></script>

</body>

</html>
