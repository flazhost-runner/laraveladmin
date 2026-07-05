@include('layouts.fe.default.head')
<body class="bg-white dark:bg-neutral-950">
<div class="page-wraper">
  @include('layouts.fe.default.header')
  @yield('content')
  @include('layouts.fe.default.footer')

  <!-- Motion animations (diekstrak ke file) -->
  <script src="{{ asset('fe/default/js/motion.js') }}"></script>
</div><!-- /page-wraper -->
</body>
</html>
