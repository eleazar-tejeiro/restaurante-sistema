<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
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
                            <?php if (isset($component)) { $__componentOriginalff9615640ecc9fe720b9f7641382872b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalff9615640ecc9fe720b9f7641382872b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.banner','data' => ['width' => '480','height' => '480']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('banner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '480','height' => '480']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalff9615640ecc9fe720b9f7641382872b)): ?>
<?php $attributes = $__attributesOriginalff9615640ecc9fe720b9f7641382872b; ?>
<?php unset($__attributesOriginalff9615640ecc9fe720b9f7641382872b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalff9615640ecc9fe720b9f7641382872b)): ?>
<?php $component = $__componentOriginalff9615640ecc9fe720b9f7641382872b; ?>
<?php unset($__componentOriginalff9615640ecc9fe720b9f7641382872b); ?>
<?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    
        
    </body>
</html><?php /**PATH /home/programfive/Documentos/restaurant/resources/views/welcome.blade.php ENDPATH**/ ?>