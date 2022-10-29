<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>GSRC Lords Bonnensysteem 2</title>

    <link href="<?php echo e(asset('css/style.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/bootstrap.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/sb-admin-2.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/font-awesome.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/site.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/datepicker.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/bootstrap-wizzard.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/selectize.bootstrap3.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/pnotify.custom.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/plugins/metisMenu/metisMenu.min.css')); ?>" rel="stylesheet">

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
                        <li><a href="<?php echo e(url('auth/logout')); ?>"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>

                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
						<li><a href="<?php echo e(url('/')); ?>"<?php echo (Request::is('*/') ? 'class="active"' : ''); ?> ><i class="fa fa-home fa-fw fa-2x"></i> HOME</a></li>
						<li><a href="<?php echo e(url('member')); ?>"<?php echo (Request::is('*member') ? 'class="active"' : ''); ?> ><i class="fa fa-user fa-fw fa-2x"></i> MEMBERS</a></li>
						<li><a href="<?php echo e(url('group')); ?>" <?php echo (Request::is('*group') ? 'class="active"' : ''); ?>><i class="fa fa-users fa-fw fa-2x"></i>GROUPS</a></li>
						<li><a href="<?php echo e(url('product')); ?>" <?php echo (Request::is('*product') ? 'class="active"' : ''); ?>><i class="fa fa-beer fa-fw fa-2x"> </i>PRODUCTS </a></li>

                        <?php if(\Sentinel::check() && \Sentinel::inRole('admin')): ?>
                            <li>
                                <a href="#" <?php echo (Request::is('*fiscus*') ? 'class="active"' : ''); ?>><i class="fa fa-money fa-fw fa-2x"> </i>FISCUS </a>
                                <ul class="nav nav-seconf-level collapse <?php echo (Request::is('*fiscus*') ? 'in' : ''); ?>">
                                    <li><a href="<?php echo e(url('fiscus')); ?>" <?php echo (Request::is('*fiscus') ? 'class="active"' : ''); ?>>View</a></li>
                                    <li><a href="<?php echo e(url('fiscus/create')); ?>" <?php echo (Request::is('*fiscus') ? 'class="active"' : ''); ?>>New </a></li>
                                    <li><a href="<?php echo e(url('fiscus/edit')); ?>" <?php echo (Request::is('*fiscus/edit') ? 'class="active"' : ''); ?>>Add / Delete / Edit </a></li>
                                </ul>
                            </li>
                            <li><a href="<?php echo e(url('invoice')); ?>" <?php echo (Request::is('*invoice') ? 'class="active"' : ''); ?>><i class="fa fa-eur fa-fw fa-2x"> </i>INVOICE </a>  </li>
                            <li><a href="<?php echo e(url('sepa')); ?>" <?php echo (Request::is('*sepa') ? 'class="active"' : ''); ?>><i class="fa fa-eur fa-fw fa-2x"> </i>SEPA </a>  </li>
                        <?php endif; ?>


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
                        <?php if(!App\Models\InvoiceGroup::getCurrentMonth()): ?>
                            <div class="alert alert-danger" role="alert"> No month selected, please contact the board</div>
                            <?php if(!Request::is('*invoice')): ?>
                                <?php echo e(die()); ?>

                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Current Month:  <?php echo e(App\Models\InvoiceGroup::getCurrentMonth()->name); ?></div>
                        <?php endif; ?>

                        <?php echo $__env->yieldContent('content'); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php echo $__env->yieldContent('modal'); ?>;
    <?php echo $__env->make('layout.notifications', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- /#wrapper -->



<!-- jQuery -->
<script src="../JS/jquery-2.0.3.min.js"></script>
<script src="../JS/jquery-ui-1.9.2.custom.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="../JS/bootstrap.min.js"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="../JS/metisMenu.min.js"></script>

<!-- Custom Theme JavaScript -->
<script src="../JS/sb-admin-2.js"></script>

<script src="<?php echo e(asset('JS/jquery.form.min.js')); ?>"></script>
<script src="<?php echo e(asset('JS/pnotify.custom.min.js')); ?>"></script>
<script src="<?php echo e(asset('JS/functions.js')); ?>"></script>
<script src="<?php echo e(asset('JS/jquery.bootstrap.wizard.min.js')); ?>"></script>
<script src="<?php echo e(asset('JS/bootstrap-datepicker.js')); ?>"></script>
<script src="<?php echo e(asset('JS/selectize.min.js')); ?>"></script>

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

    <?php echo $__env->yieldContent('script'); ?>

</body>

</html>

<?php /**PATH /var/www/html/resources/views/layout/master.blade.php ENDPATH**/ ?>