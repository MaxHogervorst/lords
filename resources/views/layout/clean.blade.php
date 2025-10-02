<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>GSRC Lords Bonnensysteem</title>

    <!-- Bootstrap Core CSS -->
<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

<!-- MetisMenu CSS -->
<link href="{{ asset('css/plugins/metisMenu/metisMenu.min.css') }}" rel="stylesheet">

<!-- Custom CSS -->
<link href="{{ asset('css/sb-admin-2.css') }}" rel="stylesheet">


<!-- Custom Fonts -->
<link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">

<link href="{{ asset('css/site.css') }}" rel="stylesheet">

<link href="{{ asset('css/selectize.bootstrap3.css') }}" rel="stylesheet">


<link href="{{ asset('css/datepicker.css') }}" rel="stylesheet">
<link href="{{ asset('css/pnotify.custom.min.css') }}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-wizzard.css') }}" rel="stylesheet">

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

<div id="wrapper">

    <!-- Navigation -->

    <!-- Page Content -->

    <div class='row'> &nbsp; </div>
    <div class='row'>
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    @if(!App\Models\InvoiceGroup::getCurrentMonth())
                        <div class="alert alert-danger" role="alert"> No month selected, please contact the board</div>
                        @if(!Request::is('*invoice'))
                            {{die()}}
                        @endif
                    @else
                        <div class="alert alert-info">Current Month:  {{ App\Models\InvoiceGroup::getCurrentMonth()->name }}</div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>


@yield('modal');
@include('layout.notifications')
<!-- /#wrapper -->

<!-- jQuery Version 1.11.0 -->
<script src="{{ asset('JS/jquery-2.0.3.min.js') }}"></script>
<script src="{{ asset('JS/jquery-ui-1.9.2.custom.min.js') }}"></script>

<!-- Bootstrap Core JavaScript -->
<script src="{{ asset('JS/bootstrap.min.js') }}"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="{{ asset('JS/metisMenu.min.js') }}"></script>

<!-- Custom Theme JavaScript -->
<script src="{{ asset('JS/sb-admin-2.js') }}"></script>

<script src="{{ asset('JS/selectize.min.js') }}"></script>

<script src="{{ asset('JS/bootstrap-datepicker.js') }}"></script>


<script src="{{ asset('JS/jquery.form.min.js') }}"></script>
<script src="{{ asset('JS/pnotify.custom.min.js') }}"></script>
<script src="{{ asset('JS/jquery.bootstrap.wizard.min.js') }}"></script>

<script src="{{ asset('JS/functions.js') }}"></script>

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

@yield('script')

</body>

</html>

