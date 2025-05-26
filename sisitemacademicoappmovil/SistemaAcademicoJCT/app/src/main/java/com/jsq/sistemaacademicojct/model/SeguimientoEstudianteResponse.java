package com.jsq.sistemaacademicojct.model;

import java.util.List;

public class SeguimientoEstudianteResponse {
    public boolean success;
    public List<Seguimiento> data;

    public static class Seguimiento {
        public int id_seguimiento;
        public String fecha_reg_seg;
        public String asistencia;
        public String participacion;
        public String disciplina;
        public String puntualidad;
        public String respeto;
        public String tolerancia;
        public String estado_animo;
        public String observaciones_seguimiento;

        public Materia materia;
        public Curso curso;
        public Gestion gestion;

        public static class Materia {
            public int id_materia;
            public String campo_materia;
            public String area_materia;
            public String sigla_materia;
        }

        public static class Curso {
            public String grado_curso;
            public String paralelo_curso;
        }

        public static class Gestion {
            public String nombre_gestion;
        }
    }
}
