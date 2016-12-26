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
{!! Html::style('css/bootstrap.min.css') !!}

<!-- MetisMenu CSS -->
{!! Html::style('css/plugins/metisMenu/metisMenu.min.css') !!}

<!-- Custom CSS -->
{!! Html::style('css/sb-admin-2.css') !!}


<!-- Custom Fonts -->
{!! Html::style('css/font-awesome.min.css') !!}

{!! Html::style('css/site.css') !!}

{!! Html::style('css/selectize.bootstrap3.css') !!}


{!! Html::style('css/datepicker.css') !!}
{!! Html::style('css/pnotify.custom.min.css') !!}

{!! Html::style('css/bootstrap-wizzard.css') !!}

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
{!! Html::script('JS/jquery-2.0.3.min.js') !!}
{!! Html::script('JS/jquery-ui-1.9.2.custom.min.js')!!}

<!-- Bootstrap Core JavaScript -->
{!! Html::script('JS/bootstrap.min.js') !!}

<!-- Metis Menu Plugin JavaScript -->
{!! Html::script('JS/metisMenu.min.js') !!}

<!-- Custom Theme JavaScript -->
{!! Html::script('JS/sb-admin-2.js') !!}

{!! Html::script('JS/selectize.min.js') !!}

{!! Html::script('JS/bootstrap-datepicker.js') !!}


{!! Html::script('JS/jquery.form.min.js') !!}
{!! Html::script('JS/pnotify.custom.min.js') !!}
{!! Html::script('JS/jquery.bootstrap.wizard.min.js') !!}

{!! Html::script('JS/functions.js') !!}

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

