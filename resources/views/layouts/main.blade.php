<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ url('style/main.css') }}">

    <title>{{ $title }}</title>
  </head>
  <body>
    <div class="page-wrapper chiller-theme toggled">
      <a id="show-sidebar" class="btn btn-sm btn-dark" href="#">
        <i class="fas fa-bars"></i>
      </a>
      <nav id="sidebar" class="sidebar-wrapper">
        <div class="sidebar-content">
          <div class="sidebar-brand">
            <a href="/home">KITA GERAK ADMIN</a>
            <div id="close-sidebar">
              <i class="fas fa-times"></i>
            </div>
          </div>
          <div class="sidebar-header">
            <div class="user-pic">
              <img class="img-responsive img-rounded" src="{{ url('/images/blank') }}"
                alt="User picture">
            </div>
            <div class="user-info">
              <span class="user-name">
                <strong>{{ auth()->user()->name }}</strong>
              </span>
              <span class="user-role">Administrator</span>
              <span class="user-status">
                <i class="fa fa-circle"></i>
                <span>Online</span>
              </span>
            </div>
          </div>
          <!-- sidebar-header  -->
          <div class="sidebar-menu">
            <ul>
              <li class="header-menu">
                <span>General</span>
              </li>
              <li class="sidebar-dropdown">
                <a href="/home">
                  <i class="fa fa-tachometer-alt"></i>
                  <span>Dashboard</span>
                  <span class="badge badge-pill badge-warning">New</span>
                </a>
              </li>
              <li class="sidebar-dropdown">
                <a href="#">
                  <i class="fa fa-shopping-cart"></i>
                  <span>Venues</span>
                  {{-- <span class="badge badge-pill badge-danger">3</span> --}}
                </a>
                <div class="sidebar-submenu">
                  <ul>
                    <li>
                      <a href="{{ url('/venues') }}">All</a>
                    </li>
                    <li>
                      <a href="{{ url('/venues?status=active') }}">Aktif</a>
                    </li>
                    <li>
                      <a href="{{ url('/venues?status=pending') }}">Pending</a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="sidebar-dropdown">
                <a href="#">
                  <i class="far fa-gem"></i>
                  <span>Courts</span>
                </a>
                <div class="sidebar-submenu">
                  <ul>
                    <li>
                      <a href="{{ url('/courts') }}">All</a>
                    </li>
                    <li>
                      <a href="{{ url('/courts?status=active') }}">Aktif</a>
                    </li>
                    <li>
                      <a href="{{ url('/courts?status=pending') }}">Pending</a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="sidebar-dropdown">
                <a href="/courtTypes">
                  <i class="fa fa-book"></i>
                  <span>Court Type</span>
                </a>
              </li>
              <li class="sidebar-dropdown">
                <a href="/halamanrahasiaregisterhanyauntukadmin2">
                  <i class="fa fa-chart-line"></i>
                  <span>Register New Admin</span>
                </a>
              </li>
              
              {{-- <li class="header-menu">
                <span>Extra</span>
              </li>
              <li>
                <a href="#">
                  <i class="fa fa-book"></i>
                  <span>Documentation</span>
                  <span class="badge badge-pill badge-primary">Beta</span>
                </a>
              </li>
              <li>
                <a href="#">
                  <i class="fa fa-calendar"></i>
                  <span>Calendar</span>
                </a>
              </li>
              <li>
                <a href="#">
                  <i class="fa fa-folder"></i>
                  <span>Examples</span>
                </a>
              </li> --}}
            </ul>
          </div>
          <!-- sidebar-menu  -->
        </div>
        <!-- sidebar-content  -->
        <div class="sidebar-footer">
          {{-- <a href="#">
            <i class="fa fa-bell"></i>
            <span class="badge badge-pill badge-warning notification">3</span>
          </a>
          <a href="#">
            <i class="fa fa-envelope"></i>
            <span class="badge badge-pill badge-success notification">7</span>
          </a> --}}
          <a href="#">
            <i class="fa fa-cog"></i>
            {{-- <span class="badge-sonar"></span> --}}
          </a>
          <a href="#">
            <form action="/logout" method="POST">
              @csrf
              {{-- <i class="fa fa-power-off"></i> --}}
              <button style="background-color: transparent; color: grey; border: 0px;" type="submit"><i class="fa fa-sign-out"></i></button>
            </form>
          </a>
        </div>
      </nav>
      <!-- sidebar-wrapper  -->
      <main class="page-content">
        {{-- <div class="container-fluid">
          <h2>Pro Sidebar with Bootstrap 5</h2>
          <hr>
          <div class="row">
            <div class="form-group col-md-12">
              <p>This is a responsive sidebar template with dropdown menu based on bootstrap 5 framework.</p>
              <p> You can find the complete code on <a href="https://github.com/azouaoui-med/pro-sidebar-template" target="_blank">
                  Github</a>, it contains more themes and background image option</p>
            </div>
          </div>
          </div>
        </div> --}}
        <div class="container-fluid">
          @yield('content')
        </div>
    
      </main>
      <!-- page-content" -->
    </div>
    <!-- page-wrapper -->

    <!-- Optional JavaScript; choose one of the two! -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    -->
    <script>
      $(".sidebar-dropdown > a").click(function() {
        $(".sidebar-submenu").slideUp(200);
        if (
          $(this)
            .parent()
            .hasClass("active")
        ) {
          $(".sidebar-dropdown").removeClass("active");
          $(this)
            .parent()
            .removeClass("active");
        } else {
          $(".sidebar-dropdown").removeClass("active");
          $(this)
            .next(".sidebar-submenu")
            .slideDown(200);
          $(this)
            .parent()
            .addClass("active");
        }
      });

      $("#close-sidebar").click(function() {
        $(".page-wrapper").removeClass("toggled");
      });
      $("#show-sidebar").click(function() {
        $(".page-wrapper").addClass("toggled");
    });
    </script>
    @yield('script')
  </body>
</html>