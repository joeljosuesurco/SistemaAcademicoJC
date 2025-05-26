package com.jsq.sistemaacademicojct.model;

import com.google.gson.annotations.SerializedName;

import java.util.List;

public class NotaHijoResponse {
    public boolean success;
    public Datos data;

    public static class Datos {
        @SerializedName("estudiante_id")
        public int estudianteId;
        @SerializedName("curso_id")
        public int cursoId;
        @SerializedName("gestion_id")
        public int gestionId;
        public String periodo;
        @SerializedName("numero_periodo")
        public int numeroPeriodo;
        public List<Nota> notas;
    }

    public static class Nota {
        @SerializedName("id_nota")
        public int idNota;
        public int numero_periodo;
        public String periodo;
        public Integer nota_final;
        public String observacion;
        public Materia materia;
        public List<Dimension> dimensiones;
    }

    public static class Materia {
        @SerializedName("id_materia")
        public int id;
        @SerializedName("campo_materia")
        public String campo;
        @SerializedName("area_materia")
        public String area;
        @SerializedName("sigla_materia")
        public String sigla;
    }

    public static class Dimension {
        @SerializedName("nombre_dimension")
        public String nombre;
        @SerializedName("valor_obtenido")
        public int valor;
    }
}
