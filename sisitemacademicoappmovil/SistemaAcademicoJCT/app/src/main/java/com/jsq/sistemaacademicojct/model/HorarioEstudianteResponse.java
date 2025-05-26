package com.jsq.sistemaacademicojct.model;

public class HorarioEstudianteResponse {
    public int id_horario;
    public String dia;
    public String hora_inicio;
    public String hora_fin;
    public int materias_id_materia;
    public int cursos_id_curso;
    public int gestiones_id_gestion;
    public Materia materia;

    public static class Materia {
        public int id_materia;
        public String campo_materia;
        public String area_materia;
        public String sigla_materia;
        public int nivel_educativo_id;
    }
}
