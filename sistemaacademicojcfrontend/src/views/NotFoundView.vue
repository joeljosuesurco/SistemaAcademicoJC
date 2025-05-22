<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-slate-800 px-4">
    <div class="text-center">
      <h1 class="text-6xl font-bold text-red-600 mb-4">404</h1>
      <p class="text-2xl font-semibold text-gray-800 dark:text-white mb-2">Página no encontrada</p>
      <p class="text-gray-600 dark:text-gray-400 mb-6">
        Lo sentimos, la página que buscas no existe o ha sido movida.
      </p>
      <router-link
        to="/login"
        class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-2xl transition"
      >
        Volver
      </router-link>
    </div>
  </div>
</template>
