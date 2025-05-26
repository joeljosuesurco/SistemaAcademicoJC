package com.jsq.sistemaacademicojct.model;

import java.util.List;

public class NotasEstudianteResponse {
    private boolean success;
    private Data data;

    public boolean isSuccess() { return success; }
    public Data getData() { return data; }

    public static class Data {
        public String curso;
        public String nivel;
        public String periodo;
        public String numero_periodo;
        public List<Nota> notas;
    }

    public static class Nota {
        public int id_nota;
        public String periodo;
        public int numero_periodo;
        public int nota_final;
        public String observacion;
        public int estudiantes_id_estudiante;
        public int materias_id_materia;
        public int cursos_id_curso;
        public int gestiones_id_gestion;
        public Materia materia;
        public List<Dimension> dimensiones;
    }

    public static class Materia {
        public int id_materia;
        public String campo_materia;
        public String area_materia;
        public String sigla_materia;
        public int nivel_educativo_id;
    }

    public static class Dimension {
        public int id_dimension_nota;
        public String nombre_dimension;
        public int porcentaje;
        public int valor_obtenido;
        public int notas_id_nota;
    }
}
