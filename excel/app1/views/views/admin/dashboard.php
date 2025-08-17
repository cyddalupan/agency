<script type="text/javascript">
function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}
</script>
<div ng-controller="dashboard">
    <!-- Page Breadcrumb -->
    <div class="page-breadcrumbs">
        <ul class="breadcrumb">
            <li  class="active">
                <i class="fa fa-home"></i> Home 
            </li>
        </ul>
    </div>
    <!-- /Page Breadcrumb -->

    <!-- Page Header -->
    <div class="page-header position-relative">
        <div class="header-title">
            <h1>Dashboard</h1>
        </div>
        <!--Header Buttons-->
        <div class="header-buttons">
            <a class="sidebar-toggler" href="#">
                <i class="fa fa-arrows-h"></i>
            </a>
            <a class="refresh" id="refresh-toggler" href="#">
                <i class="fa fa-refresh"></i>
            </a>
            <a class="fullscreen" id="fullscreen-toggler" href="#">
                <i class="fa fa-arrows-alt"></i>
            </a>
        </div>
        <!--Header Buttons End-->
    </div>
    <!-- /Page Header -->

    <!-- Page Body -->
    <div class="page-body page-<?php echo $app->getTemplate(); ?>">
        <?php $app->renderAlerts(); ?> 

        <div class="horizontal-space"></div>
        <div class="row">
            <div class="col-xs-12 col-md-12 col-lg-12">
                <div class="widget">
                    <div class="widget-header ">
                        <span class="widget-caption">Statistic</span>
                        <div class="widget-buttons">
                            <a href="#" data-toggle="maximize">
                                <i class="fa fa-expand"></i>
                            </a>
                            <a href="#" data-toggle="collapse">
                                <i class="fa fa-minus"></i>
                            </a>
                            <a href="#" data-toggle="dispose">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="text-center text-info dashboardstats-loading dloading">
                        <h1><i class="fa fa-circle-o-notch fa-spin"></i></h1>
                    </div>
                    <iframe id="dashboardstats" onload='javascript:resizeIframe(this);' frameborder="0" scrolling="no" style="width:100%"></iframe>   
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"> 
                <div class="widget">
                    <div class="widget-header bordered-bottom bordered-themesecondary">
                        <i class="widget-icon fa fa-tags themesecondary"></i>
                        <span class="widget-caption themesecondary">Updates Board</span>
                    </div><!--Widget Header-->
                    <div class="text-center text-info updatesboard-loading uloading">
                        <h1><i class="fa fa-circle-o-notch fa-spin"></i></h1>
                    </div>
                    <iframe id="updatesboard" onload='javascript:resizeIframe(this);' frameborder="0" scrolling="no" style="width:100%"></iframe>
                </div>
            </div>
        </div><!-- .row --> 

    </div>
    <!-- /Page Body -->
</div>