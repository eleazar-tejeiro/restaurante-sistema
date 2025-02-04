<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
   
    </head>
    <body class="antialiased">
        <header class="bg-white shadow-sm">
            <nav class="container mx-auto mt-6 px-6 py-3">
                <div class="flex justify-between items-center">
                    <image src='images/Salteñas.png' class='w-20 h-20 object-contain' />
                    <a href="/admin" class="bg-orange-600 text-white p-2 rounded-md  hover:bg-orange-600 transition duration-300">Iniciar Sessiòn</a>
                </div>
            </nav>
        </header>
    
        <main class=''>
            <section class="py-10 px-32  ">
                <div class=" mx-auto px-6">
                    <div class="flex flex-col md:flex-row items-center">
                        <div class="w-[1200px] mb-10 md:mb-0">
                            <h1 class="text-[300px] md:text-7xl font-bold mb-4 text-gray-900">Saborea el éxito con nuestra <span class='text-orange-500'>plataforma</span></h1>
                            <p class="text-xl my-12 text-gray-600">Descubre una suite integrada de herramientas diseñadas para aumentar tu productividad y hacer crecer tu negocio con la máxima eficiencia.</p>
                            <a href="/admin" class="bg-orange-600 text-white px-6 py-3 rounded-md text-lg hover:bg-orange-600 transition duration-300">Empieza ahora</a>
                        </div>
                        <div class="md:w-1/2 ml-16">
                            <x-banner width="480" height="480"  />
                        </div>
                    </div>
                </div>
            </section>

        </main>
    
        {{-- <footer class="bg-orange-500 text-white py-10">
            <div class="container mx-auto px-6 text-center">
                <p>&copy; 2024 TechSolution. Todos los derechos reservados.</p>
            </div>
        </footer> --}}
    </body>
</html>