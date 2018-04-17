<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <base href="@data{global_site_url}">
    <link rel="icon" href="../../../../favicon.ico">

    <title>@data{global_site_name}</title>

    <!-- Bootstrap core CSS -->
    <link href="plugins/bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="https://fonts.googleapis.com/css?family=Tangerine" rel="stylesheet">
    <link href="assets/css/theme.css" rel="stylesheet">
  </head>

  <body>

    <header>
      <!-- Fixed navbar -->
      <nav class="navbar navbar-expand-md navbar-light sticky-top bg-light">
        <a class="navbar-brand" href="/">@data{global_site_name}</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item @data{navbar_home_active}">
              <a class="nav-link" href="/">Home</a>
            </li>
            <li class="nav-item @data{navbar_doc_active}">
              <a class="nav-link" href="docs">Documentation</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="http://www.alifuatnumanoglu.com/contact">Contact</a>
            </li>
            <li class="nav-item active">
              <a class="nav-link more-menu" href="http://www.alifuatnumanoglu.com">See More</a>
            </li>
          </ul>
          <form class="form-inline mt-2 mt-md-0" action="search/go" method="GET" >
            <input class="form-control mr-sm-2" name="search" type="text" placeholder="Search documentation" aria-label="Search">
          </form>
          @temp_file{header_logout_part}
        </div>
      </nav>
    </header>

    <!-- Begin page content -->
    <main role="main" class="container">
