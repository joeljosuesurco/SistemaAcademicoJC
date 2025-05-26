import {
  mdiMonitor,
  mdiSchool,
  mdiAccountGroup,
  mdiBookOpenPageVariant,
  mdiClipboardText,
  mdiChartLine,
  mdiAccountTie,
  mdiBullhorn,
  mdiAccountCog,
  mdiBookshelf,
} from '@mdi/js'

import { useMainStore } from '@/stores/main'

export function getMenuAside() {
  const mainStore = useMainStore()
  const userRole = mainStore.userRole

  const menu = [
    {
      to: '/dashboard',
      icon: mdiMonitor,
      label: 'Menú Principal',
    },
  ]

  if (userRole === 'administrativo') {
    menu.push(
      {
        label: 'Estudiantes',
        icon: mdiSchool,
        menu: [
          { label: 'Listado Estudiantes', to: '/estudiantes/listado' },
          { label: 'Inscribir Estudiante', to: '/estudiantes/inscribir' },
          { label: 'Administrar Estudiante', to: '/estudiantes/administrar' },
          { label: 'Cambio de Curso', to: '/cambio-curso' },
        ],
      },
      {
        label: 'Profesores',
        icon: mdiAccountGroup,
        menu: [
          { label: 'Listado de Profesores', to: '/profesores' },
          { label: 'Inscribir Profesor', to: '/profesores/registrar' },
          { label: 'Administrar Profesor', to: '/profesores/editar' },
          { label: 'Asignación', to: '/asignaciones/crear' },
        ],
      },
      {
        label: 'Materias',
        icon: mdiBookOpenPageVariant,
        menu: [
          { label: 'Listado de Materias', to: '/materias' },
          { label: 'Registrar Materia', to: '/materias/nueva' }, // 👈 nuevo ítem
          {
            label: 'Editar materia',
            to: '/materias/editar/:id',
          },
        ],
      },
      {
        label: 'Cursos',
        icon: mdiBookshelf,
        menu: [
          { label: 'Listado de Cursos', to: '/cursos' },
          { label: 'Crear Curso', to: '/cursos/crear' },
          { label: 'Administrar Curso', to: '/cursos/administrar' },
        ],
      },
      {
        label: 'Notas',
        icon: mdiClipboardText,
        menu: [
          { label: 'Notas', to: '/notas' },
          { label: 'Registrar Nota Alumno', to: '/notas/registrar' },
          { label: 'Registrar Nota Curso ', to: '/admin/registrar-notas' },
        ],
      },
      {
        label: 'Seguimiento de Rendimiento',
        icon: mdiChartLine,
        menu: [
          { label: 'Ver Seguimiento Diario', to: '/seguimiento' },
          { label: 'Registrar Seguimiento Diario', to: '/seguimiento/crear' },
        ],
      },
      {
        label: 'Padres de Familia',
        icon: mdiAccountTie,
        menu: [
          { label: 'Listado Padres', to: '/padres' },
          { label: 'Inscribir Padre', to: '/padres/registrar' },
          { label: 'Administrar Padres', to: '/padres/editar' },
          { label: 'Asignar Hijos', to: '/padres/asignar-hijos' },
        ],
      },
      {
        label: 'Comunicados',
        icon: mdiBullhorn,
        menu: [
          { label: 'Enviar Comunicado', to: '/comunicados/crear' },
          { label: 'Lista Comunicados', to: '/comunicados' },
        ],
      },
      {
        label: 'Reportes',
        icon: mdiAccountTie,
        to: '/reportes/seguimiento',
      },
      {
        label: 'Control de Usuarios',
        icon: mdiAccountCog,
        menu: [{ label: 'Usuarios', to: '/usuarios' }],
      },
    )
  }

  if (userRole === 'profesor') {
    menu.push(
      {
        to: '/profesor/cursos',
        icon: mdiBookOpenPageVariant,
        label: 'Mis Cursos',
      },
      {
        to: '/profesor/materias',
        icon: mdiClipboardText,
        label: 'Mis Materias',
      },
      {
        label: 'Notas',
        icon: mdiClipboardText,
        menu: [
          { label: 'Notas Cursos', to: '/profesor/notas' },
          { label: 'Registrar Notas', to: '/profesor/registrar-notas' },
        ],
      },
      {
        label: 'Seguimiento de Estudiantes',
        icon: mdiClipboardText,
        menu: [
          { label: 'Ver Seguimientos', to: '/profesor/seguimiento' },
          { label: 'Registrar Seguimiento', to: '/profesor/seguimiento/crear' },
        ],
      },
      {
        label: 'Comunicados',
        icon: mdiBullhorn,
        menu: [{ label: 'Lista Comunicados', to: '/comunicados' }],
      },
    )
  }

  if (userRole === 'padre') {
    menu.push(
      {
        label: 'Mis Hijos',
        icon: mdiAccountGroup,
        to: '/padre/hijos',
      },
      {
        label: 'Notas',
        icon: mdiClipboardText,
        menu: [{ label: 'Ver Notas de Hijos', to: '/padre/notas' }],
      },
      {
        label: 'Seguimiento',
        icon: mdiChartLine,
        menu: [{ label: 'Ver Seguimiento de Hijos', to: '/padre/seguimiento' }],
      },
      {
        label: 'Comunicados',
        icon: mdiBullhorn,
        menu: [{ label: 'Lista Comunicados', to: '/comunicados' }],
      },
    )
  }

  if (userRole === 'estudiante') {
    menu.push(
      {
        label: 'Mi Información',
        icon: mdiAccountGroup,
        to: '/estudiante/inicio',
      },
      {
        label: 'Notas',
        icon: mdiClipboardText,
        to: '/estudiante/notas',
      },
      {
        label: 'Seguimiento Diario',
        icon: mdiChartLine,
        to: '/estudiante/seguimiento',
      },
      {
        label: 'Comunicados',
        icon: mdiBullhorn,
        menu: [{ label: 'Lista Comunicados', to: '/comunicados' }],
      },
    )
  }

  return menu
}
