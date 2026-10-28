<!-- resources/views/layouts/app.blade.php -->
@auth
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema minimercado')</title>
    
    <!-- Enlace al archivo CSS -->
    <!-- Enlace a FontAwesome y CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ventas.css') }}">
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand navbar-dark bg-dark border-bottom border-secondary shadow-sm py-2">
        <div class="container-fluid px-3 px-md-4">
            <div class="d-flex align-items-center gap-2 ms-auto">
                <div class="d-flex align-items-center gap-2 bg-secondary bg-opacity-25 px-3 py-1 rounded-pill border border-secondary border-opacity-50">
                    <i class="fas fa-user-circle text-info fs-5"></i>
                    <div class="d-flex flex-row align-items-center gap-2">
                        <span class="fw-bold text-white small">{{ auth()->user()->name }}</span>
                        @if(auth()->user()->rol)
                            <span class="badge bg-primary text-white small px-2 py-1 rounded-pill text-capitalize" style="font-size: 0.7rem;">
                                {{ auth()->user()->rol }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container-fluid mt-3 main-content">
        @yield('ladoizq')
    </div>

      <!-- Contenido principal -->
      <div class="container-fluid mt-3 main-content">
        @yield('content')
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@else
    <script>window.location = "{{ route('login') }}";</script>
@endauth
