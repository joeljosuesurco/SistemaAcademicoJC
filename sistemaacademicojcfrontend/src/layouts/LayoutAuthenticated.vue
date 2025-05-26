<script setup>
import { mdiForwardburger, mdiBackburger, mdiMenu } from '@mdi/js'
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { getMenuAside } from '@/menuAside.js'
import menuNavBar from '@/menuNavBar.js'
import { useDarkModeStore } from '@/stores/darkMode.js'
import BaseIcon from '@/components/BaseIcon.vue'
import NavBar from '@/components/NavBar.vue'
import NavBarItemPlain from '@/components/NavBarItemPlain.vue'
import AsideMenu from '@/components/AsideMenu.vue'
import FooterBar from '@/components/FooterBar.vue'
import { useAuth } from '@/composables/useAuth'
import { useVistaDashboard } from '@/composables/useVistaDashboard'
import { useMainStore } from '@/stores/main'

// Padding del layout según sidebar
const layoutAsidePadding = 'xl:pl-60'

const mainStore = useMainStore()
const userRole = computed(() => mainStore.userRole)

// Mapeo de acceso por rol
const accesoPorRol = {
  administrativo: [
    'Menú Principal',
    'Estudiantes',
    'Profesores',
    'Materias',
    'Cursos',
    'Notas',
    'Seguimiento de Rendimiento',
    'Padres de Familia',
    'Comunicados',
    'Control de Usuarios',
    'Reportes',
  ],
  profesor: [
    'Menú Principal',
    'Seguimiento de Rendimiento',
    'Comunicados',
    'Mis Cursos',
    'Mis Materias',
    'Notas',
    'Seguimiento de Estudiantes',
  ],

  estudiante: ['Menú Principal', 'Mi Información', 'Comunicados', 'Notas', 'Seguimiento Diario'],
  padre: [
    'Menú Principal',
    'Padres de Familia',
    'Comunicados',
    'Mis Hijos',
    'Notas',
    'Seguimiento',
  ],
}

// Menu filtrado dinámico
const filteredMenu = computed(() => {
  const allowed = accesoPorRol[userRole.value] || []
  return getMenuAside().filter((item) => allowed.includes(item.label))
})

const { setVistaActual } = useVistaDashboard()
const { logout } = useAuth()
const darkModeStore = useDarkModeStore()

const router = useRouter()
const isAsideMobileExpanded = ref(false)
const isAsideLgActive = ref(false)

onMounted(async () => {
  await mainStore.fetchPerfil()
  await mainStore.fetchComunicadosVisibles()
})

router.beforeEach(() => {
  isAsideMobileExpanded.value = false
  isAsideLgActive.value = false
})

const menuClick = (event, item) => {
  if (item.isToggleLightDark) {
    darkModeStore.set()
  }
  if (item.to === '/dashboard') {
    setVistaActual('dashboard')
  }
  if (item.isLogout) {
    logout()
  }
}
</script>

<template>
  <div :class="{ 'overflow-hidden lg:overflow-visible': isAsideMobileExpanded }">
    <div
      :class="[layoutAsidePadding, { 'ml-60 lg:ml-0': isAsideMobileExpanded }]"
      class="pt-14 w-screen transition-position lg:w-auto bg-gray-50 dark:bg-slate-800 dark:text-slate-100"
    >
      <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <NavBar
          :menu="menuNavBar"
          :class="[layoutAsidePadding, { 'ml-60 lg:ml-0': isAsideMobileExpanded }]"
          @menu-click="menuClick"
        >
          <!-- toggles -->
          <NavBarItemPlain
            display="flex lg:hidden"
            @click.prevent="isAsideMobileExpanded = !isAsideMobileExpanded"
          >
            <BaseIcon :path="isAsideMobileExpanded ? mdiBackburger : mdiForwardburger" size="24" />
          </NavBarItemPlain>
          <NavBarItemPlain
            display="hidden lg:flex xl:hidden"
            @click.prevent="isAsideLgActive = true"
          >
            <BaseIcon :path="mdiMenu" size="24" />
          </NavBarItemPlain>
        </NavBar>

        <!-- Sidebar con filtro por rol -->
        <AsideMenu
          :is-aside-mobile-expanded="isAsideMobileExpanded"
          :is-aside-lg-active="isAsideLgActive"
          :menu="filteredMenu"
          @menu-click="menuClick"
          @aside-lg-close-click="isAsideLgActive = false"
        />

        <!-- Contenido principal -->
        <main class="flex-grow">
          <slot />
        </main>

        <!-- Footer -->
        <FooterBar />
      </div>
    </div>
  </div>
</template>
