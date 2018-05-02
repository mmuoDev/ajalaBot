<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree">
            {{--<li class="active treeview">--}}
                {{--<a href="#">--}}
                    {{--<i class="fa fa-dashboard"></i> <span>Dashboard</span>--}}
                    {{--<span class="pull-right-container">--}}
              {{--<i class="fa fa-angle-left pull-right"></i>--}}
            {{--</span>--}}
                {{--</a>--}}
                {{--<ul class="treeview-menu">--}}
                    {{--<li class="active"><a href="index.html"><i class="fa fa-circle-o"></i> Dashboard v1</a></li>--}}
                    {{--<li><a href="index2.html"><i class="fa fa-circle-o"></i> Dashboard v2</a></li>--}}
                {{--</ul>--}}
            {{--</li>--}}
            <li>
                <a href="{{url('/home')}}">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
            </span>
                </a>
            </li>
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-th"></i>
                    <span>Trips/Tours</span>
                    <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{url('travels')}}"> All</a></li>
                    <li><a href="{{url('travels/create')}}">Add</a></li>
                    {{--<li><a href="pages/charts/flot.html">Channels</a></li>--}}
                </ul>
            </li>
            <li>
                <a href="{{url('transactions/summary')}}">
                    <i class="fa fa-edit"></i> <span>Wishlist</span>
                    </span>
                </a>
            </li>
            <li>
                <a href="{{url('transactions/summary')}}">
                    <i class="fa fa-folder"></i> <span>Bookings</span>
                    </span>
                </a>
            </li>
            <li>
                <a href="{{url('logout')}}">
                    <i class="fa fa-lock"></i> <span>Logout</span>
                    </span>
                </a>
            </li>
            {{--<li>--}}
                {{--<a href="{{url('/wallet/fund')}}">--}}
                    {{--<i class="fa fa-edit"></i> <span>Fund Wallet</span>--}}
                    {{--</span>--}}
                {{--</a>--}}
            {{--</li>--}}
            {{--<li class="treeview">--}}
                {{--<a href="#">--}}
                    {{--<i class="fa fa-table"></i>--}}
                    {{--<span>Settings</span>--}}
                    {{--<span class="pull-right-container">--}}
              {{--<i class="fa fa-angle-left pull-right"></i>--}}
            {{--</span>--}}
                {{--</a>--}}
                {{--<ul class="treeview-menu">--}}
                    {{--<li><a href="pages/charts/chartjs.html"> All Users</a></li>--}}
                    {{--<li><a href="pages/charts/chartjs.html"> All Agents</a></li>--}}
                    {{--<li><a href="pages/charts/morris.html">Add User</a></li>--}}
                    {{--<li><a href="pages/charts/flot.html">Add Agent</a></li>--}}
                {{--</ul>--}}
            {{--</li>--}}
            {{--<li>--}}
                {{--<a href="pages/calendar.html">--}}
                    {{--<i class="fa fa-edit"></i> <span>Edit Password</span>--}}
                    {{--</span>--}}
                {{--</a>--}}
            {{--</li>--}}
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>