<template>
  <div class="min-h-screen bg-white text-gray-800">
    <!-- Encabezado superior -->
    <header class="bg-gray-100 py-2 px-4 flex justify-center items-center border-b border-gray-300">
      <h1 class="text-sm font-semibold uppercase tracking-wide text-center">
        UNIDAD EDUCATIVA TÉCNICO-HUMANÍSTICO "JUAN CAPRILES" – TURNO TARDE
      </h1>
    </header>

    <!-- Título principal -->
    <div class="text-center py-6">
      <h2 class="text-2xl md:text-3xl font-bold uppercase">
        UNIDAD EDUCATIVA TÉCNICO-HUMANÍSTICO "JUAN CAPRILES" – TURNO TARDE
      </h2>
    </div>

    <!-- Navegación tipo pestañas -->
    <nav class="flex justify-center mb-6">
      <div class="flex flex-wrap bg-gray-200 rounded overflow-hidden">
        <button class="px-6 py-2 bg-blue-500 text-white font-semibold">Principal</button>
        <RouterLink to="/login" class="px-6 py-2 hover:bg-blue-100 text-sm font-medium">
          Acceso Administrador / Profesor
        </RouterLink>
        <RouterLink to="/login" class="px-6 py-2 hover:bg-blue-100 text-sm font-medium">
          Acceso Estudiante / Padre de Familia
        </RouterLink>
      </div>
    </nav>

    <!-- Contenido principal -->
    <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row gap-6 items-start">
      <!-- Imagen -->
      <div class="w-full md:w-1/2">
        <img
          src="/bienvenida-imagen.png"
          alt="Imagen institucional"
          class="rounded shadow-md w-full max-h-[350px] object-cover"
        />
      </div>

      <!-- Texto de bienvenida -->
      <div class="w-full md:w-1/2 text-justify text-sm leading-relaxed">
        <p class="mb-4">
          Bienvenidos al Sistema Académico de la Unidad Educativa Técnico-Humanístico “Juan
          Capriles” – Turno Tarde. Este espacio está diseñado para brindar acceso rápido, seguro y
          transparente a toda la comunidad educativa.
        </p>
        <p class="mb-4">
          Desde aquí podrás consultar noticias institucionales, acceder como docente, estudiante o
          padre de familia, y participar activamente en la gestión educativa. Nos esforzamos por
          ofrecer un entorno accesible, inclusivo y adaptado a las necesidades del siglo XXI.
        </p>
        <p>
          Agradecemos su visita y les invitamos a explorar las funcionalidades de esta plataforma
          con responsabilidad y compromiso.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
