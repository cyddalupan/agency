    <div class="navbar">
    <div class="navbar-inner">
    <div class="navbar-container"  STYLE="HEIGHT:120PX">
    <!-- Navbar Barnd -->
    <div class="navbar-header pull-left">
    <a href="#" class="navbar-brand">
    <small>
    <img src="<?php echo site_url().$_SESSION["settings"]['icon_link']; ?>" alt="" style="border:0px solid black;width:140px;height:125px"/>
    </small>
    </a>
    </div>
    <!-- /Navbar Barnd -->
    
    <!-- Sidebar Collapse -->
    <div class="sidebar-collapse" id="sidebar-collapse">
    <i class="collapse-icon fa fa-bars"></i>
    </div>
    <!-- /Sidebar Collapse -->
    
    <!-- Account Area and Settings -->
    <div class="navbar-header pull-right">
    <div class="navbar-account">
    <ul class="account-area">
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [3, 4, 6]) ): ?>
    <?php if ( $notification['expired-total'] > 0 ): ?>
    
    <li>
    <a href="" ng-click="redirect('applicants','For Review');skill = 'all'" title="For Review">
    <i class="icon fa fa-folder-open-o"></i>
    <span class="badge badge-darkpink graded" >
    <?php echo $notification['4reviewCount']; ?>
    </span>
    </a>
    </li>
    <li>
    <a class=" dropdown-toggle" data-toggle="dropdown" title="Help" href="#">
    <i class="icon fa fa-warning"></i>
    <span class="badge badge-darkpink graded" >
    <?php echo (int) $notification['expired-total'];  ?>
    </span>
    </a>
    <!--Notification Dropdown-->
    <ul class="pull-right dropdown-menu dropdown-arrow dropdown-notifications">
    <li>
    <a href="<?php echo site_url( 'admin/applicants/expired-reservations' ); ?>">
    <div class="clearfix">
    <div class="notification-icon">
    <i class="fa fa-check-square bg-warning white"></i>
    </div>
    <div class="notification-body">
    <span class="title">Reservation</span>
    </div>
    <?php if ( $notification['expired']['reservation'] > 0 ): ?>
    <div class="notification-extra">
    <span class="badge badge-darkorange graded" >
    <?php echo (int) $notification['expired']['reservation'];  ?>
    </span>
    </div>
    <?php endif; ?>
    </div>
    </a>
    </li>
    <li>
    <a href="<?php echo site_url( 'admin/applicants/expired-medical' ); ?>">
    <div class="clearfix">
    <div class="notification-icon">
    <i class="fa fa-stethoscope bg-warning white"></i>
    </div>
    <div class="notification-body">
    <span class="title">Medical</span>
    </div>
    <?php if ( $notification['expired']['medical'] > 0 ): ?>
    <div class="notification-extra">
    <span class="badge badge-darkorange graded" >
    <?php echo (int) $notification['expired']['medical'];  ?>
    </span>
    </div>
    <?php endif; ?>
    </div>
    </a>
    </li>
    <li>
    <a href="<?php echo site_url( 'admin/applicants/expired-visa' ); ?>">
    <div class="clearfix">
    <div class="notification-icon">
    <i class="fa fa-credit-card bg-warning white"></i>
    </div>
    <div class="notification-body">
    <span class="title">Visa</span>
    </div>
    <?php if ( $notification['expired']['visa'] > 0 ): ?>
    <div class="notification-extra">
    <span class="badge badge-darkorange graded" >
    <?php echo (int) $notification['expired']['visa'];  ?>
    </span>
    </div>
    <?php endif; ?>
    </div>
    </a>
    </li>
    <li>
    <a href="<?php echo site_url( 'admin/applicants/expired-passports' ); ?>">
    <div class="clearfix">
    <div class="notification-icon">
    <i class="fa fa-ticket bg-warning white"></i>
    </div>
    <div class="notification-body">
    <span class="title">Passport</span>
    </div>
    <?php if ( $notification['expired']['passport'] > 0 ): ?>
    <div class="notification-extra">
    <span class="badge badge-darkorange graded" >
    <?php echo (int) $notification['expired']['passport'];  ?>
    </span>
    </div>
    <?php endif; ?>
    </div>
    </a>
    </li>
    </ul>
    <!--/Notification Dropdown-->
    </li>
    <?php endif; ?>
    <?php endif; ?>
    <li>
    <a class="login-area dropdown-toggle" data-toggle="dropdown">
    <div class="avatar" title="View your public profile">
    <img src="<?php echo site_url().$_SESSION["settings"]['icon_link']; ?>" alt="" style="border:0px solid black"/>
    </div>
    <section>
    <h2><span class="profile"><span><?php echo $_SESSION['admin']['user']['user_fullname']; ?></span></span></h2>
    </section>
    </a>
    <!--Login Area Dropdown-->
    <ul class="pull-right dropdown-menu dropdown-arrow dropdown-login-area">
    <li class="username"><a>David Stevenson</a></li>
    <li class="email"><a><?php echo $_SESSION['admin']['user']['user_email']; ?></a></li>
    <!--Avatar Area-->
    <li>
    <div class="avatar-area">
    <img src="<?php echo site_url().$_SESSION["settings"]['icon_link']; ?>" class="avatar" alt="" style="border:0px solid black;width:128px;height:128px"/>
    <span class="caption">Change Photo</span>
    </div>
    </li>
    
    <li>
    <a href="" ng-click="redirect('change_password');">
    <span class="menu-text text-danger">Change Password</span> 
    </a>
    </li>
    
    <!--Avatar Area-->
    <li class="edit">
    <a href="#" class="pull-left">Profile</a>
    <a href="<?php echo site_url( 'admin/popup/exchange_rate' ); ?>" role="button" data-toggle="modal" data-target="#modalReports" class="pull-right">Exchange Rate</a>
    </li>
    <!--Theme Selector Area-->
    <li class="theme-area">
    <ul class="colorpicker" id="skin-changer">
    <li><a class="colorpick-btn" href="#" style="background-color:#5DB2FF;" rel="<?php echo $app->getPath()['styles']; ?>skins/blue.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#2dc3e8;" rel="<?php echo $app->getPath()['styles']; ?>skins/azure.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#03B3B2;" rel="<?php echo $app->getPath()['styles']; ?>skins/teal.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#53a93f;" rel="<?php echo $app->getPath()['styles']; ?>skins/green.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#FF8F32;" rel="<?php echo $app->getPath()['styles']; ?>skins/orange.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#cc324b;" rel="<?php echo $app->getPath()['styles']; ?>skins/pink.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#AC193D;" rel="<?php echo $app->getPath()['styles']; ?>skins/darkred.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#8C0095;" rel="<?php echo $app->getPath()['styles']; ?>skins/purple.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#0072C6;" rel="<?php echo $app->getPath()['styles']; ?>skins/darkblue.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#585858;" rel="<?php echo $app->getPath()['styles']; ?>skins/gray.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#474544;" rel="<?php echo $app->getPath()['styles']; ?>skins/black.min.css"></a></li>
    <li><a class="colorpick-btn" href="#" style="background-color:#001940;" rel="<?php echo $app->getPath()['styles']; ?>skins/deepblue.min.css"></a></li>
    </ul>
    </li>
    <!--/Theme Selector Area-->
    <li class="dropdown-footer">
    <a ng-click="logout()">
    Sign out
    </a>
    </li>
    </ul>
    <!--/Login Area Dropdown-->
    </li>
    <!-- /Account Area -->
    <!--Note: notice that setting div must start right after account area list.
    no space must be between these elements-->
    <!-- Settings -->
    </ul>
    <div class="setting">
    <a id="btn-setting" title="Setting" href="#">
    <i class="icon fa fa-cog"></i>
    </a>
    </div>
    <div class="setting-container">
    <label>
    <input type="checkbox" id="checkbox_fixednavbar">
    <span class="text">Fixed Navbar</span>
    </label>
    <label>
    <input type="checkbox" id="checkbox_fixedsidebar">
    <span class="text">Fixed SideBar</span>
    </label>
    <label>
    <input type="checkbox" id="checkbox_fixedbreadcrumbs">
    <span class="text">Fixed BreadCrumbs</span>
    </label>
    <label>
    <input type="checkbox" id="checkbox_fixedheader">
    <span class="text">Fixed Header</span>
    </label>
    </div>
    <!-- Settings -->
    </div>
    </div>
    <!-- /Account Area and Settings -->
    </div>
    </div>
    </div>
    
    
    
    
   
    
    
    
      
