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
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="./">GSRC Lords Bonnensysteem</a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">

                        <li class="divider"></li>
                        <li><a href="{{ url('auth/logout') }}"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>

                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
						<li><a href="{{ url('/') }}"{!! (Request::is('*/') ? 'class="active"' : '') !!} ><i class="fa fa-home fa-fw fa-2x"></i> HOME</a></li>
						<li><a href="{{ url('member') }}"{!! (Request::is('*member') ? 'class="active"' : '') !!} ><i class="fa fa-user fa-fw fa-2x"></i> MEMBERS</a></li>
						<li><a href="{{ url('group') }}" {!! (Request::is('*group') ? 'class="active"' : '') !!}><i class="fa fa-users fa-fw fa-2x"></i>GROUPS</a></li>
						<li><a href="{{ url('product') }}" {!! (Request::is('*product') ? 'class="active"' : '') !!}><i class="fa fa-beer fa-fw fa-2x"> </i>PRODUCTS </a></li>

                        @if(\Sentinel::inRole('admin'))
                            <li>
                                <a href="#" {!! (Request::is('*fiscus*') ? 'class="active"' : '') !!}><i class="fa fa-money fa-fw fa-2x"> </i>FISCUS </a>
                                <ul class="nav nav-seconf-level collapse {!! (Request::is('*fiscus*') ? 'in' : '') !!}">
                                    <li><a href="{{ url('fiscus') }}" {!! (Request::is('*fiscus') ? 'class="active"' : '') !!}>View</a></li>
                                    <li><a href="{{ url('fiscus/create') }}" {!! (Request::is('*fiscus') ? 'class="active"' : '') !!}>New </a></li>
                                    <li><a href="{{ url('fiscus/edit') }}" {!! (Request::is('*fiscus/edit') ? 'class="active"' : '') !!}>Add / Delete / Edit </a></li>
                                </ul>
                            </li>
                            <li><a href="{{ url('invoice') }}" {!! (Request::is('*invoice') ? 'class="active"' : '') !!}><i class="fa fa-eur fa-fw fa-2x"> </i>INVOICE </a>  </li>
                            <li><a href="{{ url('sepa') }}" {!! (Request::is('*sepa') ? 'class="active"' : '') !!}><i class="fa fa-eur fa-fw fa-2x"> </i>SEPA </a>  </li>
                        @endif


					</ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>

        <!-- Page Content -->
        <div id="page-wrapper">
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

