<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h2 class="text-2xl font-bold mb-6">Notas de mis Estudiantes</h2>

      <!-- Paso 1: Selección de curso -->
      <div v-if="!cursoSeleccionado">
        <h3 class="text-lg font-semibold mb-2">Seleccione un curso</h3>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="curso in cursosDisponibles"
            :key="curso.id_curso"
            class="p-4 border rounded shadow hover:bg-slate-50 cursor-pointer"
            @click="seleccionarCurso(curso)"
          >
            <p class="font-semibold">{{ curso.nombre }}</p>
            <p class="text-sm text-gray-600">Nivel: {{ curso.nivel }}</p>
            <p class="text-sm text-gray-600">Gestión: {{ curso.gestion }}</p>
          </div>
        </div>
      </div>

      <!-- Paso 2: Selección de materia -->
      <div v-else-if="!materiaSeleccionada">
        <div class="mb-4">
          <BaseButton
            label="Volver a cursos"
            color="green"
            small
            @click="cursoSeleccionado = null"
          />
        </div>
        <h3 class="text-xl font-semibold mb-4">
          Seleccione una materia del curso {{ cursoSeleccionado.nombre }}
        </h3>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="materia in materiasDelCurso"
            :key="materia.id_materia"
            class="p-4 border rounded shadow hover:bg-blue-50 cursor-pointer"
            @click="seleccionarMateria(materia)"
          >
            <p class="font-semibold">{{ materia.area }} ({{ materia.sigla }})</p>
            <p class="text-sm text-gray-600">Campo: {{ materia.campo }}</p>
          </div>
        </div>
      </div>

      <!-- Paso 3: Mostrar notas -->
      <div v-else>
        <div class="mb-4 flex justify-between items-center">
          <div>
            <BaseButton
              label="Volver a materias"
              color="green"
              small
              @click="materiaSeleccionada = null"
            />
          </div>
          <div>
            <label class="mr-2 font-medium">Periodo:</label>
            <select v-model="periodoSeleccionado" class="px-3 py-2 border rounded shadow-sm">
              <option value="1">Trimestre 1</option>
              <option value="2">Trimestre 2</option>
              <option value="3">Trimestre 3</option>
            </select>
          </div>
        </div>

        <CardBox class="mt-4">
          <h3 class="text-xl font-semibold mb-4">
            Estudiantes de {{ cursoSeleccionado.nombre }} – {{ materiaSeleccionada.area }} ({{
              materiaSeleccionada.sigla
            }})
          </h3>

          <div class="overflow-x-auto">
            <table class="w-full text-center bg-white rounded shadow text-sm">
              <thead>
                <tr>
                  <th class="bg-white align-middle" rowspan="3">Estudiante</th>
                  <th class="bg-blue-100 text-blue-800 font-semibold" colspan="4">
                    Evaluación Maestro(a)
                  </th>
                  <th class="bg-purple-100 text-purple-800 font-semibold" colspan="2">
                    Autoevaluación
                  </th>
                  <th class="bg-blue-50 font-semibold align-middle" rowspan="3">Promedio</th>
                </tr>
                <tr>
                  <th class="bg-blue-200 text-blue-800 font-medium" colspan="4">Dimensiones</th>
                  <th class="bg-purple-200 text-purple-800 font-medium" colspan="2">Dimensiones</th>
                </tr>
                <tr class="text-blue-800">
                  <th>Ser<br /><span class="text-xs font-normal">(5 pts.)</span></th>
                  <th>Saber<br /><span class="text-xs font-normal">(45 pts.)</span></th>
                  <th>Hacer<br /><span class="text-xs font-normal">(40 pts.)</span></th>
                  <th>Decidir<br /><span class="text-xs font-normal">(5 pts.)</span></th>
                  <th class="text-purple-700">
                    Ser<br /><span class="text-xs font-normal">(5 pts.)</span>
                  </th>
                  <th class="text-purple-700">
                    Decidir<br /><span class="text-xs font-normal">(5 pts.)</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="nota in notasFiltradas" :key="nota.id_nota" class="hover:bg-gray-50">
                  <td class="p-2 font-medium">{{ nota.estudiante }}</td>
                  <td class="p-2">
                    {{ nota.ser_raw }}<span class="text-xs text-gray-500"> ({{ nota.ser }})</span>
                  </td>
                  <td class="p-2">
                    {{ nota.saber_raw
                    }}<span class="text-xs text-gray-500"> ({{ nota.saber }})</span>
                  </td>
                  <td class="p-2">
                    {{ nota.hacer_raw
                    }}<span class="text-xs text-gray-500"> ({{ nota.hacer }})</span>
                  </td>
                  <td class="p-2">
                    {{ nota.decidir_raw
                    }}<span class="text-xs text-gray-500"> ({{ nota.decidir }})</span>
                  </td>
                  <td class="p-2 text-purple-800">
                    {{ nota.ser_auto_raw
                    }}<span class="text-xs text-gray-500"> ({{ nota.ser_auto }})</span>
                  </td>
                  <td class="p-2 text-purple-800">
                    {{ nota.decidir_auto_raw
                    }}<span class="text-xs text-gray-500"> ({{ nota.decidir_auto }})</span>
                  </td>
                  <td class="p-2 font-bold text-blue-700">{{ nota.promedio }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="!notasFiltradas.length" class="text-gray-500 mt-4">
            No hay notas registradas para este periodo.
          </div>
        </CardBox>
        <div class="mt-4 flex justify-end">
          <BaseButton
            label="Descargar Reporte PDF"
            color="success"
            small
            @click="descargarReporteNotasPDF"
          />
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'
import CardBox from '@/components/CardBox.vue'

const rawNotas = ref([])
const cursosDisponibles = ref([])
const cursoSeleccionado = ref(null)
const materiaSeleccionada = ref(null)
const periodoSeleccionado = ref(1)

onMounted(async () => {
  try {
    const res = await api.get('/profesor-auth/notas')
    rawNotas.value = res.data.data
    const cursosSet = new Map()
    rawNotas.value.forEach((n) => {
      const key = `${n.curso.id_curso}-${n.gestion.id_gestion}`
      if (!cursosSet.has(key)) {
        cursosSet.set(key, {
          id_curso: n.curso.id_curso,
          nombre: `${n.curso.grado_curso} ${n.curso.paralelo_curso}`,
          nivel: n.curso.nivel_educativo.nombre,
          gestion: n.gestion.gestion,
          id_gestion: n.gestion.id_gestion,
        })
      }
    })
    cursosDisponibles.value = Array.from(cursosSet.values())
  } catch (err) {
    console.error('Error al cargar notas:', err)
  }
})

const seleccionarCurso = (curso) => {
  cursoSeleccionado.value = curso
  materiaSeleccionada.value = null
}

const materiasDelCurso = computed(() => {
  if (!cursoSeleccionado.value) return []
  const materiasSet = new Map()
  rawNotas.value.forEach((n) => {
    if (
      n.curso.id_curso === cursoSeleccionado.value.id_curso &&
      n.gestion.id_gestion === cursoSeleccionado.value.id_gestion
    ) {
      if (!materiasSet.has(n.materia.id_materia)) {
        materiasSet.set(n.materia.id_materia, {
          id_materia: n.materia.id_materia,
          sigla: n.materia.sigla_materia,
          area: n.materia.area_materia,
          campo: n.materia.campo_materia,
        })
      }
    }
  })
  return Array.from(materiasSet.values())
})

const seleccionarMateria = (materia) => {
  materiaSeleccionada.value = materia
}

const notasFiltradas = computed(() => {
  if (!cursoSeleccionado.value || !materiaSeleccionada.value) return []

  return rawNotas.value
    .filter(
      (n) =>
        n.curso.id_curso === cursoSeleccionado.value.id_curso &&
        n.gestion.id_gestion === cursoSeleccionado.value.id_gestion &&
        n.materia.id_materia === materiaSeleccionada.value.id_materia &&
        String(n.numero_periodo) === String(periodoSeleccionado.value),
    )
    .map((n) => {
      const dims = new Map(n.dimensiones.map((d) => [d.nombre_dimension, d]))
      const getValor = (dim, pct) => {
        const raw = dims.get(dim)?.valor_obtenido || 0
        return {
          raw,
          calculado: Math.round((raw * pct) / 100),
        }
      }
      const persona = n.estudiante.persona_rol?.persona || {}
      const nombre =
        `${persona.apellidos_pat || ''} ${persona.apellidos_mat || ''} ${persona.nombres_persona || ''}`.trim()
      const valores = {
        ser: getValor('ser_docente', 5),
        saber: getValor('saber_docente', 45),
        hacer: getValor('hacer_docente', 40),
        decidir: getValor('decidir_docente', 5),
        ser_auto: getValor('ser_autoeval', 5),
        decidir_auto: getValor('decidir_autoeval', 5),
      }
      const promedio = Object.values(valores).reduce((sum, v) => sum + v.calculado, 0)
      return {
        id_nota: n.id_nota,
        estudiante: nombre,
        ser_raw: valores.ser.raw,
        saber_raw: valores.saber.raw,
        hacer_raw: valores.hacer.raw,
        decidir_raw: valores.decidir.raw,
        ser_auto_raw: valores.ser_auto.raw,
        decidir_auto_raw: valores.decidir_auto.raw,
        ser: valores.ser.calculado,
        saber: valores.saber.calculado,
        hacer: valores.hacer.calculado,
        decidir: valores.decidir.calculado,
        ser_auto: valores.ser_auto.calculado,
        decidir_auto: valores.decidir_auto.calculado,
        promedio: Math.min(promedio, 100),
      }
    })
})
const descargarReporteNotasPDF = async () => {
  if (!cursoSeleccionado.value || !materiaSeleccionada.value || !notasFiltradas.value.length) {
    alert('No hay datos suficientes para generar el reporte.')
    return
  }

  const token = localStorage.getItem('token')
  const url = `${import.meta.env.VITE_API_BASE_URL}/reportes/notas-profesor`

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
        Accept: 'application/pdf',
      },
      body: JSON.stringify({
        curso: cursoSeleccionado.value,
        materia: materiaSeleccionada.value,
        periodo: periodoSeleccionado.value,
        notas: notasFiltradas.value,
      }),
    })

    const contentType = res.headers.get('content-type')
    if (!res.ok || !contentType.includes('application/pdf')) {
      const text = await res.text()
      console.error('Respuesta inesperada:', text)
      alert('No se pudo generar el PDF. Ver consola.')
      return
    }

    const blob = await res.blob()
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `Reporte_Notas_${cursoSeleccionado.value.nombre}_${materiaSeleccionada.value.sigla}.pdf`
    link.click()
  } catch (err) {
    console.error('Error al descargar reporte:', err)
    alert('Ocurrió un error al generar el reporte.')
  }
}
</script>
