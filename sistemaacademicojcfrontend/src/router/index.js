import { createRouter, createWebHashHistory } from 'vue-router'
import { useMainStore } from '@/stores/main'

// tus vistas (o deja como estaban importadas antes)
import Home from '@/views/HomeView.vue'
import AdministrarEstudianteView from '@/views/Estudiantes/AdministrarEstudianteView.vue'
import CambioCursoEstudianteView from '@/views/Estudiantes/CambioCursoEstudianteView.vue'
import ListaEstudianteView from '@/views/Estudiantes/ListaEstudianteView.vue'
import ListaCursoView from '@/views/Cursos/ListaCursoView.vue'
import CrearCursoView from '@/views/Cursos/CrearCursoView.vue'
import ListarMateriaView from '@/views/Materias/ListarMateriaView.vue'
import ListarProfesorView from '@/views/Profesores/ListaProfesorView.vue'
import AsignarMateriaProfesorView from '@/views/Asignaciones/AsignarMateriaProfesorView.vue'
import RegistrarProfesorView from '@/views/Profesores/RegistrarProfesorView.vue'
import EditarProfesorView from '@/views/Profesores/EditarProfesorView.vue'
import RegistrarPadreView from '@/views/Padres/RegistrarPadreView.vue'
import ListarPadresView from '@/views/Padres/ListarPadresView.vue'
import AdministrarPadreView from '@/views/Padres/AdministarPadreView.vue'
import CrearSeguimientoView from '@/views/Seguimiento/CrearSeguimientoView.vue'
import ListarUsuarioView from '@/views/Usuarios/ListarUsuarioView.vue'
import ListaNotasView from '@/views/Notas/ListaNotasView.vue'
import RegistrarNotaView from '@/views/Notas/RegistrarNotaView.vue'
import SeguimientoProfesorView from '@/views/Profesores/SeguimientoProfesorView.vue'
import RegistrarNotaCursoView from '@/views/Notas/RegistrarNotaCursoView.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/LoginView.vue'),
    meta: { title: 'Login' },
  },
  { path: '/dashboard', name: 'dashboard', component: Home, meta: { title: 'Dashboard' } },
  {
    path: '/forms',
    name: 'forms',
    component: () => import('@/views/FormsView.vue'),
    meta: { title: 'Forms' },
  },

  // Estudiantes
  {
    path: '/estudiantes/inscribir',
    name: 'InscribirEstudiante',
    component: () => import('@/views/Estudiantes/InscribirEstudianteView.vue'),
    meta: { requiresAuth: true, title: 'Inscribir Estudiante' },
  },
  {
    path: '/estudiantes/administrar',
    name: 'AdministrarEstudiante',
    component: AdministrarEstudianteView,
    meta: { requiresAuth: true, title: 'Administrar Estudiante' },
  },
  {
    path: '/cambio-curso',
    name: 'CambioCurso',
    component: CambioCursoEstudianteView,
    meta: { requiresAuth: true, title: 'Cambio de Curso' },
  },
  {
    path: '/estudiantes/listado',
    name: 'ListadoEstudiante',
    component: ListaEstudianteView,
    meta: { requiresAuth: true, title: 'Listado de Estudiantes' },
  },

  // Cursos
  {
    path: '/cursos',
    name: 'ListadoCursos',
    component: ListaCursoView,
    meta: { requiresAuth: true, title: 'Listado de Cursos' },
  },
  {
    path: '/cursos/crear',
    name: 'CrearCurso',
    component: CrearCursoView,
    meta: { requiresAuth: true, title: 'Crear Curso' },
  },
  {
    path: '/cursos/administrar',
    name: 'AdministrarCurso',
    component: () => import('@/views/Cursos/AdministrarCursoView.vue'),
    meta: { requiresAuth: true, title: 'Administrar Curso' },
  },
  // Materias
  {
    path: '/materias',
    name: 'ListadoMaterias',
    component: ListarMateriaView,
    meta: { requiresAuth: true, title: 'Listado de Materias' },
  },

  // Profesores
  {
    path: '/profesores',
    name: 'ListadoProfesores',
    component: ListarProfesorView,
    meta: { requiresAuth: true, title: 'Listado de Profesores' },
  },
  {
    path: '/profesores/registrar',
    name: 'RegistrarProfesor',
    component: RegistrarProfesorView,
    meta: { requiresAuth: true, title: 'Registrar Profesor' },
  },
  {
    path: '/profesores/editar',
    name: 'EditarProfesores',
    component: EditarProfesorView,
    meta: { requiresAuth: true, title: 'Administrar Profesor' },
  },
  {
    path: '/asignaciones/crear',
    name: 'AsignarMateriaProfesor',
    component: AsignarMateriaProfesorView,
    meta: { requiresAuth: true, title: 'Asignar Materia a Profesor' },
  },

  // Padres
  {
    path: '/padres',
    name: 'ListadoPadres',
    component: ListarPadresView,
    meta: { requiresAuth: true, title: 'Padres de Familia' },
  },
  {
    path: '/padres/registrar',
    name: 'RegistrarPadre',
    component: RegistrarPadreView,
    meta: { requiresAuth: true, title: 'Registrar Padre' },
  },
  {
    path: '/padres/editar',
    name: 'AdministarPadre',
    component: AdministrarPadreView,
    meta: { requiresAuth: true, title: 'Editar Padre de Familia' },
  },
  {
    path: '/padres/asignar-hijos',
    name: 'AsignarHijosPadre',
    component: () => import('@/views/Padres/AsignarHijosPadreView.vue'),
    meta: { requiresAuth: true, title: 'Asignar Hijos a Padre' },
  },

  // Comunicados
  {
    path: '/comunicados/crear',
    name: 'CrearNotificacion',
    component: () => import('@/views/Comunicados/CrearNotificacionView.vue'),
    meta: { requiresAuth: true, title: 'Crear Comunicado' },
  },
  {
    path: '/comunicados',
    name: 'ListaComunicados',
    component: () => import('@/views/Comunicados/ListaComunicadosView.vue'),
    meta: { requiresAuth: true, title: 'Comunicados' },
  },
  {
    path: '/comunicado-reciente',
    name: 'ComunicadoReciente',
    component: () => import('@/views/Comunicados/ComunicadoRecienteView.vue'),
    meta: { requiresAuth: true, title: 'Comunicado Reciente' },
  },

  // Seguimiento
  {
    path: '/seguimiento',
    name: 'seguimiento-diario',
    component: () => import('@/views/Seguimiento/ListarSeguimientoView.vue'),
    meta: { requiresAuth: true, title: 'Ver Seguimiento Diario' },
  },
  {
    path: '/seguimiento/crear',
    name: 'CrearSeguimiento',
    component: CrearSeguimientoView,
    meta: { requiresAuth: true, title: 'Crear Seguimiento' },
  },

  // Usuarios
  {
    path: '/usuarios',
    name: 'usuarios',
    component: ListarUsuarioView,
    meta: { requiresAuth: true, title: 'Usuarios' },
  },

  // Notas
  {
    path: '/notas',
    name: 'notas',
    component: ListaNotasView,
    meta: { requiresAuth: true, title: 'Notas' },
  },
  {
    path: '/notas/registrar',
    name: 'registrar-nota',
    component: RegistrarNotaView,
    meta: { requiresAuth: true, title: 'Registrar Nota' },
  },

  // Fallback
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/NotFoundView.vue'),
  },
  //profe aut
  {
    path: '/profesor/cursos',
    name: 'CursosProfesor',
    component: () => import('@/views/Profesores/CursosProfesorView.vue'),
    meta: { requiresAuth: true, roles: ['profesor'] },
  },
  {
    path: '/profesor/materias',
    name: 'MateriasProfesor',
    component: () => import('@/views/Profesores/MateriasProfesorView.vue'),
    meta: { requiresAuth: true, roles: ['profesor'] },
  },
  {
    path: '/profesor/notas',
    name: 'NotasProfesor',
    component: () => import('@/views/Profesores/NotasProfesorView.vue'),
    meta: { requiresAuth: true, roles: ['profesor'] },
  },
  {
    path: '/profesor/registrar-notas',
    name: 'RegistrarNotaProfesor',
    component: () => import('@/views/Profesores/RegistrarNotaProfesorView.vue'),
    meta: { requiresAuth: true, roles: ['profesor'] },
  },
  {
    path: '/profesor/seguimiento',
    name: 'SeguimientoProfesor',
    component: SeguimientoProfesorView,
    meta: { requiresAuth: true, role: 'profesor' },
  },
  {
    path: '/profesor/seguimiento/crear',
    name: 'CrearSeguimientoProfesor',
    component: () => import('@/views/Profesores/CrearSeguimientoProfesorView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/mi-perfil',
    name: 'perfil-usuario',
    component: () => import('@/views/Usuarios/PerfilUsuarioView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/padre/hijos',
    name: 'HijosPadre',
    component: () => import('@/views/Padres/HijosPadreView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/padre/notas',
    name: 'NotasHijosPadre',
    component: () => import('@/views/Padres/NotasHijosPadreView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/padre/seguimiento',
    name: 'SeguimientoHijosPadre',
    component: () => import('@/views/Padres/SeguimientoHijosPadreView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/estudiante/inicio',
    name: 'InicioEstudiante',
    component: () => import('@/views/Estudiantes/InicioEstudianteView.vue'),
    meta: {
      requiresAuth: true,
      role: 'estudiante',
    },
  },
  {
    path: '/estudiante/notas',
    name: 'NotasEstudiante',
    component: () => import('@/views/Estudiantes/NotasEstudianteView.vue'),
    meta: {
      requiresAuth: true,
      role: 'estudiante',
    },
  },
  {
    path: '/estudiante/seguimiento',
    name: 'SeguimientoDiario',
    component: () => import('@/views/Estudiantes/SeguimientoDiarioView.vue'),
    meta: {
      requiresAuth: true,
      role: 'estudiante',
    },
  },
  {
    path: '/admin/registrar-notas',
    name: 'RegistrarNotasCurso',
    component: RegistrarNotaCursoView,
    meta: {
      requiresAuth: true,
      role: 'admin',
    },
  },
]

const accesoPathsPorRol = {
  administrativo: routes
    .map((r) => r.path)
    .filter((p) => p !== '/login' && p !== '/:pathMatch(.*)*'),
  profesor: [
    '/dashboard',
    '/mi-perfil',
    '/notas',
    '/notas/registrar',
    '/seguimiento',
    '/seguimiento/crear',
    '/comunicados',
    '/comunicado-reciente',
    '/profesor/cursos',
    '/profesor/materias',
    '/profesor/notas',
    '/profesor/registrar-notas',
    '/profesor/seguimiento',
    '/profesor/seguimiento/crear',
  ],
  estudiante: [
    '/dashboard',
    '/mi-perfil',
    '/notas',
    '/comunicados',
    '/comunicado-reciente',
    '/estudiante/inicio',
    '/estudiante/notas',
    '/estudiante/seguimiento',
  ],
  padre: [
    '/dashboard',
    '/mi-perfil',
    '/padres',
    '/padres/registrar',
    '/padres/editar',
    '/padres/asignar-hijos',
    '/comunicados',
    '/comunicado-reciente',
    '/padre/hijos',
    '/padre/notas',
    '/padre/seguimiento',
  ],
}

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { top: 0 }
  },
})

router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('token')
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)

  // 1) Redirigir al login si la ruta requiere auth y no hay token
  if (requiresAuth && !token) {
    return next('/login')
  }
  // 2) Si va a login y ya hay token, al dashboard
  if (to.path === '/login' && token) {
    return next('/dashboard')
  }

  // 3) Control de acceso por rol (solo rutas protegidas)
  if (token && requiresAuth) {
    const mainStore = useMainStore()
    // Asegurar que el perfil esté cargado
    if (!mainStore.userRole) {
      await mainStore.fetchPerfil()
    }
    const role = mainStore.userRole
    const allowed = accesoPathsPorRol[role] || []
    if (!allowed.includes(to.path)) {
      return next('/dashboard')
    }
  }

  // 4) Finalmente permitir
  return next()
})

export default router
