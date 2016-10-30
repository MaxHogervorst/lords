<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin 2 - Bootstrap Admin Theme</title>

    <!-- Bootstrap Core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../css/plugins/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css">

</head>

<body>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Please Sign In</h3>
                </div>
                <div class="panel-body">
                    <form role="form" method="post" action="{{ url('auth/authenticate') }}" id="loginform">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" placeholder="Username" name="username" type="text" autofocus>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="password" type="password" value="">
                            </div>
                            <!-- Change this to a button or input when using this as a form -->
                            <button class="btn btn-lg btn-success btn-block">Login</button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layout.notifications')


<!-- jQuery -->
<script src="../JS/jquery-2.0.3.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="../JS/bootstrap.min.js"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="../JS/metisMenu.min.js"></script>

<!-- Custom Theme JavaScript -->
<script src="../JS/sb-admin-2.js"></script>
{{ Html::script('JS/jquery.form.min.js') }}

{{ Html::script('JS/pnotify.custom.min.js') }}
{{ Html ::script('JS/functions.js') }}

<script>
    $(document).ready(function(){

        if(localStorage.getItem('success'))
        {

            new PNotify({
                title: 'success',
                text: localStorage.getItem('message'),
                type: 'success',
                addclass: 'notification-'
            });
            localStorage.removeItem('success');
            localStorage.removeItem('message');
        }
    });
</script>

</body>

</html>