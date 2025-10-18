<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laika - Clínica Veterinaria</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        .carousel-image {
            transition: opacity 700ms ease-in-out;
        }
        
        .carousel-dot {
            transition: all 300ms ease-in-out;
        }
        
        .hover-scale {
            transition: transform 500ms ease-in-out;
        }
        
        .hover-scale:hover {
            transform: scale(1.05);
        }
        
        .card-hover {
            transition: all 300ms ease-in-out;
        }
        
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-purple-50/30">
    
    <!-- Header -->
    <header class="bg-gradient-to-r from-purple-600 to-purple-700 text-white mb-8">
        <div class="container mx-auto px-4 py-12 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                <i data-lucide="heart-pulse" class="text-purple-600" style="width: 40px; height: 40px;"></i>
            </div>
            <h1 class="text-4xl font-bold mb-2">Laika</h1>
            <p class="text-lg opacity-90">
                Clínica veterinaria · Manzanillo, Colima
            </p>
        </div>
    </header>

    <!-- Carousel -->
    <section class="container mx-auto px-4 mb-12">
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="relative h-96 md:h-[480px]">
                <!-- Carousel Images -->
                <div class="carousel-slide absolute inset-0 carousel-image opacity-100">
                    <img src="https://images.unsplash.com/photo-1654895716780-b4664497420d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx2ZXRlcmluYXJ5JTIwY2xpbmljfGVufDF8fHx8MTc2MDY5MTk4M3ww&ixlib=rb-4.1.0&q=80&w=1080" 
                         alt="Clínica Veterinaria Laika" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                </div>

                <div class="carousel-slide absolute inset-0 carousel-image opacity-0">
                    <img src="https://images.unsplash.com/photo-1572987372598-fcd543795afb?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxwZXQlMjBjb25zdWx0YXRpb24lMjBkb2d8ZW58MXx8fHwxNzYwNzUzNTI2fDA&ixlib=rb-4.1.0&q=80&w=1080" 
                         alt="Atención profesional" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                </div>

                <div class="carousel-slide absolute inset-0 carousel-image opacity-0">
                    <img src="https://images.unsplash.com/photo-1730677769210-7b5a39d0635e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxoYXBweSUyMGRvZyUyMHZldGVyaW5hcmlhbnxlbnwxfHx8fDE3NjA2NzkxODl8MA&ixlib=rb-4.1.0&q=80&w=1080" 
                         alt="Mascotas felices" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                </div>

                <!-- Previous Button -->
                <button onclick="previousSlide()" 
                        class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-purple-700 rounded-full w-11 h-11 shadow-md flex items-center justify-center transition-colors">
                    <i data-lucide="chevron-left" style="width: 20px; height: 20px;"></i>
                </button>

                <!-- Next Button -->
                <button onclick="nextSlide()" 
                        class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-purple-700 rounded-full w-11 h-11 shadow-md flex items-center justify-center transition-colors">
                    <i data-lucide="chevron-right" style="width: 20px; height: 20px;"></i>
                </button>

                <!-- Carousel Dots -->
                <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2">
                    <button onclick="goToSlide(0)" class="carousel-dot h-2 rounded-full bg-white w-8"></button>
                    <button onclick="goToSlide(1)" class="carousel-dot h-2 rounded-full bg-white/60 hover:bg-white/80 w-2"></button>
                    <button onclick="goToSlide(2)" class="carousel-dot h-2 rounded-full bg-white/60 hover:bg-white/80 w-2"></button>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-12">
        <div class="grid md:grid-cols-2 gap-8 mb-12">
            
            <!-- ¿Quiénes somos? -->
            <div class="overflow-hidden border border-purple-100 shadow-lg hover:shadow-xl transition-shadow duration-300 rounded-lg bg-white">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-100 text-purple-700 rounded-xl">
                            <i data-lucide="info" style="width: 24px; height: 24px;"></i>
                        </div>
                        <h2 class="text-2xl font-bold">¿Quiénes somos?</h2>
                    </div>
                    <div class="mb-4 rounded-xl overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1654895716780-b4664497420d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx2ZXRlcmluYXJ5JTIwY2xpbmljfGVufDF8fHx8MTc2MDY5MTk4M3ww&ixlib=rb-4.1.0&q=80&w=1080" 
                             alt="Laika clínica veterinaria"
                             class="w-full h-52 object-cover hover-scale">
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Laika es una clínica veterinaria en Manzanillo, Colima. 
                        Hacemos fácil el cuidado de tu mascota con atención profesional y 
                        herramientas como nuestra app de citas y un dispensador 
                        de comida automatizado.
                    </p>
                </div>
            </div>

            <!-- Dispensador automatizado -->
            <div class="overflow-hidden border border-purple-100 shadow-lg hover:shadow-xl transition-shadow duration-300 rounded-lg bg-white">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-100 text-purple-700 rounded-xl">
                            <i data-lucide="bot" style="width: 24px; height: 24px;"></i>
                        </div>
                        <h2 class="text-2xl font-bold">Dispensador automatizado</h2>
                    </div>
                    <div class="mb-4 rounded-xl overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1710322827318-ad371a1821c6?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxhdXRvbWF0aWMlMjBwZXQlMjBmZWVkZXJ8ZW58MXx8fHwxNzYwNzUzNTI3fDA&ixlib=rb-4.1.0&q=80&w=1080" 
                             alt="Dispensador automatizado"
                             class="w-full h-52 object-cover hover-scale">
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Sistema inteligente para dispensar alimento en cantidades
                        exactas en horarios programados. Mantén a tu mascota bien alimentada
                        incluso cuando no estés en casa.
                    </p>
                </div>
            </div>
        </div>

        <!-- Servicios -->
        <section class="mb-16">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold mb-3">Nuestros Servicios</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Ofrecemos atención veterinaria integral con tecnología de punta y profesionales dedicados
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                
                <!-- Servicio: Consultas -->
                <div class="overflow-hidden border border-purple-100 shadow-lg rounded-lg bg-white card-hover group">
                    <div class="relative h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1572987372598-fcd543795afb?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxwZXQlMjBjb25zdWx0YXRpb24lMjBkb2d8ZW58MXx8fHwxNzYwNzUzNTI2fDA&ixlib=rb-4.1.0&q=80&w=1080" 
                             alt="Consultas"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4">
                            <div class="flex items-center gap-2 text-white">
                                <div class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-md rounded-lg">
                                    <i data-lucide="check-circle-2" style="width: 24px; height: 24px;"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Consultas</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">
                            Atención médica profesional para tu mascota
                        </p>
                    </div>
                </div>

                <!-- Servicio: Vacunación -->
                <div class="overflow-hidden border border-purple-100 shadow-lg rounded-lg bg-white card-hover group">
                    <div class="relative h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1541887796712-054f4b0f8e5d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxkb2clMjB2YWNjaW5hdGlvbnxlbnwxfHx8fDE3NjA2ODQ1NTN8MA&ixlib=rb-4.1.0&q=80&w=1080" 
                             alt="Vacunación"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4">
                            <div class="flex items-center gap-2 text-white">
                                <div class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-md rounded-lg">
                                    <i data-lucide="check-circle-2" style="width: 24px; height: 24px;"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Vacunación</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">
                            Protección completa para la salud de tu compañero
                        </p>
                    </div>
                </div>

                <!-- Servicio: Cirugías menores -->
                <div class="overflow-hidden border border-purple-100 shadow-lg rounded-lg bg-white card-hover group">
                    <div class="relative h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1759164955427-14ca448a839d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx2ZXRlcmluYXJ5JTIwc3VyZ2VyeXxlbnwxfHx8fDE3NjA3NDc3Mjl8MA&ixlib=rb-4.1.0&q=80&w=1080" 
                             alt="Cirugías menores"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4">
                            <div class="flex items-center gap-2 text-white">
                                <div class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-md rounded-lg">
                                    <i data-lucide="check-circle-2" style="width: 24px; height: 24px;"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Cirugías menores</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">
                            Procedimientos especializados con equipo moderno
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-purple-50 border-t border-purple-100">
        <div class="container mx-auto px-4 py-12">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                
                <!-- Horarios -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <i data-lucide="clock" class="text-purple-600" style="width: 20px; height: 20px;"></i>
                        <h3 class="text-xl font-bold text-purple-900">Horarios</h3>
                    </div>
                    <ul class="space-y-2 text-gray-700">
                        <li>Lun – Vie: 9:00–14:00, 16:00–20:00</li>
                        <li>Sábado: 9:00–14:00</li>
                        <li class="text-purple-700 font-semibold">Domingo: Cerrado</li>
                    </ul>
                </div>

                <!-- Contacto -->
                <div>
                    <h3 class="text-xl font-bold text-purple-900 mb-4">Contacto</h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="tel:+523120000000" 
                               class="flex items-center gap-2 text-gray-700 hover:text-purple-700 transition-colors">
                                <i data-lucide="phone" style="width: 16px; height: 16px;"></i>
                                +52 312 000 0000
                            </a>
                        </li>
                        <li>
                            <a href="mailto:laika@gmail.com" 
                               class="flex items-center gap-2 text-gray-700 hover:text-purple-700 transition-colors">
                                <i data-lucide="mail" style="width: 16px; height: 16px;"></i>
                                laika@gmail.com
                            </a>
                        </li>
                        <li class="flex items-start gap-2 text-gray-700">
                            <i data-lucide="map-pin" style="width: 16px; height: 16px; margin-top: 4px;" class="flex-shrink-0"></i>
                            <span>Manzanillo, Colima<br>Calle tal tal tal</span>
                        </li>
                    </ul>
                </div>

                <!-- Enlaces legales -->
                <div>
                    <h3 class="text-xl font-bold text-purple-900 mb-4">Legal</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="#" class="text-gray-700 hover:text-purple-700 transition-colors">
                                Política de privacidad
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-700 hover:text-purple-700 transition-colors">
                                Términos y condiciones
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-purple-200 pt-8 text-center">
                <p class="text-gray-600">
                    &copy; 2025 Laika. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript para el carrusel -->
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        const totalSlides = slides.length;

        function showSlide(index) {
            // Asegurar que el índice esté dentro del rango
            currentSlide = (index + totalSlides) % totalSlides;
            
            // Ocultar todas las slides
            slides.forEach((slide, i) => {
                slide.style.opacity = i === currentSlide ? '1' : '0';
            });
            
            // Actualizar dots
            dots.forEach((dot, i) => {
                if (i === currentSlide) {
                    dot.classList.remove('w-2', 'bg-white/60');
                    dot.classList.add('w-8', 'bg-white');
                } else {
                    dot.classList.remove('w-8', 'bg-white');
                    dot.classList.add('w-2', 'bg-white/60');
                }
            });
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function previousSlide() {
            showSlide(currentSlide - 1);
        }

        function goToSlide(index) {
            showSlide(index);
        }

        // Auto-advance carousel every 4 seconds
        setInterval(nextSlide, 4000);

        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>