package com.jsq.sistemaacademicojct.model;

import com.google.gson.annotations.SerializedName;
import java.io.Serializable;
import java.util.List;

public class HijoResponse {
    public boolean success;
    public List<Hijo> data;

    public static class Hijo implements Serializable {
        @SerializedName("id_estudiante")
        public int idEstudiante;

        public String rude;

        @SerializedName("nombre_completo")
        public String nombreCompleto;

        @SerializedName("apellidos_pat")
        public String apellidosPat;

        @SerializedName("apellidos_mat")
        public String apellidosMat;

        public String nombres;

        @SerializedName("fecha_nacimiento")
        public String fechaNacimiento;

        public String sexo;
        public String nacionalidad;
        public String direccion;
        public String celular;
        public String foto;
        public String ci;

        @SerializedName("cert_nac")
        public String certNac;

        public String libreta;

        public Curso curso;

        public static class Curso implements Serializable {
            @SerializedName("grado_curso")
            public String grado;

            @SerializedName("paralelo_curso")
            public String paralelo;

            public String nivel;

            @SerializedName("id_curso")
            public int idCurso;
        }
    }
}
