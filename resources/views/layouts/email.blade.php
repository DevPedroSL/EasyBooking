<head>
    <title>@yield('title', 'EasyBooking')</title> 
    <style>
        /* Usamos el yield con el color por defecto directamente en el CSS */
        .header { 
            background-color: @yield('header_color', '#4F46E5'); 
            color: white; 
            padding: 20px; 
            text-align: center; 
        }
        /* ... resto de estilos ... */
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>@yield('header_title')</h1>
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            <p> Saludos,<br>El equipo de EasyBooking</p>

            <p>Este es un mensaje automático, por favor no respondas a este email</p>
        </div>
    </div>
</body>