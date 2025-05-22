<script setup>
import { onMounted } from 'vue'
import { useMainStore } from '@/stores/main'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import SectionMain from '@/components/SectionMain.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import CardBox from '@/components/CardBox.vue'
import BaseIcon from '@/components/BaseIcon.vue'
import EstudiantesLista from '@/components/EstudiantesLista.vue'
import ProfesoresLista from '@/components/ProfesoresLista.vue'
import MateriasLista from '@/components/MateriasLista.vue'
import CursosLista from '@/components/CursosLista.vue'
import NotasLista from '@/components/NotasLista.vue'
import SeguimientoLista from '@/components/SeguimientoLista.vue'
import PadresLista from '@/components/PadresLista.vue'
import ComunicadosLista from '@/components/ComunicadosLista.vue'

import { useVistaDashboard } from '@/composables/useVistaDashboard'
import {
  mdiChartTimelineVariant,
  mdiSchool,
  mdiAccountGroup,
  mdiBookOpenPageVariant,
  mdiClipboardText,
  mdiChartLine,
  mdiAccountTie,
  mdiBullhorn,
  mdiBookshelf,
} from '@mdi/js'

const mainStore = useMainStore()

onMounted(async () => {
  if (!mainStore.userId) {
    await mainStore.fetchPerfil()
  }

  await mainStore.fetchComunicadosVisibles()
})

const { getVistaActual, setVistaActual } = useVistaDashboard()
const vistaActual = getVistaActual()

const sections = [
  { name: 'Estudiantes', color: 'text-orange-600', icon: mdiSchool, key: 'estudiantes' },
  { name: 'Profesores', color: 'text-amber-600', icon: mdiAccountGroup, key: 'profesores' },
  { name: 'Materias', color: 'text-yellow-600', icon: mdiBookOpenPageVariant, key: 'materias' },
  { name: 'Cursos', color: 'text-lime-600', icon: mdiBookshelf, key: 'cursos' },
  { name: 'Notas', color: 'text-red-600', icon: mdiClipboardText, key: 'notas' },
  {
    name: 'Seguimiento de Rendimiento',
    color: 'text-pink-600',
    icon: mdiChartLine,
    key: 'rendimiento',
  },
  { name: 'Padres de Familia', color: 'text-rose-600', icon: mdiAccountTie, key: 'padres' },
  { name: 'Comunicados', color: 'text-indigo-600', icon: mdiBullhorn, key: 'comunicados' },
]
</script>

<template>
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiChartTimelineVariant" title="Menu Principal" main />

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
        <CardBox class="text-center">
          <p class="text-lg font-semibold mb-2">
            👋 Bienvenido,
            <span class="text-blue-600">
              {{ mainStore.userName !== '' ? mainStore.userName : 'Cargando nombre...' }}
            </span>
          </p>
          <p class="text-sm text-gray-600 dark:text-gray-300">
            Rol: <strong>{{ mainStore.userRole || '...' }}</strong>
          </p>
        </CardBox>
      </div>

      <!-- Mostrar las tarjetas solo si el rol es 'administrativo' -->
      <div
        v-if="vistaActual === 'dashboard' && mainStore.userRole === 'administrativo'"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
      >
        <CardBox
          v-for="section in sections"
          :key="section.name"
          class="cursor-pointer text-center p-6 transition hover:shadow-lg scale-105"
          @click="setVistaActual(section.key)"
        >
          <BaseIcon :path="section.icon" size="32" class="mx-auto mb-2" :class="section.color" />
          <p class="text-lg font-semibold" :class="section.color">{{ section.name }}</p>
        </CardBox>
      </div>

      <!-- Mostrar las secciones internas solo si el rol es 'administrativo' -->
      <EstudiantesLista
        v-if="vistaActual === 'estudiantes' && mainStore.userRole === 'administrativo'"
      />
      <ProfesoresLista
        v-if="vistaActual === 'profesores' && mainStore.userRole === 'administrativo'"
      />
      <MateriasLista v-if="vistaActual === 'materias' && mainStore.userRole === 'administrativo'" />
      <CursosLista v-if="vistaActual === 'cursos' && mainStore.userRole === 'administrativo'" />
      <NotasLista v-if="vistaActual === 'notas' && mainStore.userRole === 'administrativo'" />
      <SeguimientoLista
        v-if="vistaActual === 'rendimiento' && mainStore.userRole === 'administrativo'"
      />
      <PadresLista v-if="vistaActual === 'padres' && mainStore.userRole === 'administrativo'" />
      <ComunicadosLista
        v-if="vistaActual === 'comunicados' && mainStore.userRole === 'administrativo'"
      />
    </SectionMain>
  </LayoutAuthenticated>
</template>
